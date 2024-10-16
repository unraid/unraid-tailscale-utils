<?php

class TailscaleInfo
{
    private Translator $tr;
    private stdClass $status;
    private stdClass $prefs;
    private stdClass $lock;

    public function __construct(Translator $tr)
    {
        $this->tr     = $tr;
        $this->status = self::getStatus();
        $this->prefs  = self::getPrefs();
        $this->lock   = self::getLock();
    }

    public static function getStatus(): stdClass
    {
        exec("tailscale status --json", $out_status);
        return json_decode(implode($out_status));
    }

    public static function getPrefs(): stdClass
    {
        exec("tailscale debug prefs", $out_prefs);
        return json_decode(implode($out_prefs));
    }

    public static function getLock(): stdClass
    {
        exec("tailscale lock status -json=true", $out_status);
        return json_decode(implode($out_status));
    }

    public static function printRow(string $title, string $value): string
    {
        return "<tr><td>{$title}</td><td>{$value}</td></tr>" . PHP_EOL;
    }

    public static function printDash(string $title, string $value): string
    {
        return "<tr><td><span class='w26'>{$title}</span>{$value}</td></tr>" . PHP_EOL;
    }

    private function tr(string $message): string
    {
        return $this->tr->tr($message);
    }

    public function getStatusInfo(): string
    {
        $status = $this->status;
        $prefs  = $this->prefs;
        $lock   = $this->lock;

        $tsVersion     = isset($status->Version) ? $status->Version : $this->tr("unknown");
        $keyExpiration = isset($status->Self->KeyExpiry) ? $status->Self->KeyExpiry : $this->tr("disabled");
        $online        = isset($status->Self->Online) ? ($status->Self->Online ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");
        $inNetMap      = isset($status->Self->InNetworkMap) ? ($status->Self->InNetworkMap ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");
        $tags          = isset($status->Self->Tags) ? implode("<br />", $status->Self->Tags) : "";
        $loggedIn      = isset($prefs->LoggedOut) ? ($prefs->LoggedOut ? $this->tr("no") : $this->tr("yes")) : $this->tr("unknown");
        $tsHealth      = isset($status->Health) ? implode("<br />", $status->Health) : "";
        $lockEnabled   = $this->getTailscaleLockEnabled() ? $this->tr("yes") : $this->tr("no");

        $lockTranslate = $this->tr("tailscale_lock");

        $output = "";
        $output .= self::printRow($this->tr("info.version"), $tsVersion);
        $output .= self::printRow($this->tr("info.health"), $tsHealth);
        $output .= self::printRow($this->tr("info.login"), $loggedIn);
        $output .= self::printRow($this->tr("info.netmap"), $inNetMap);
        $output .= self::printRow($this->tr("info.online"), $online);
        $output .= self::printRow($this->tr("info.key_expire"), $keyExpiration);
        $output .= self::printRow($this->tr("info.tags"), $tags);
        $output .= self::printRow("{$lockTranslate}: " . $this->tr("enabled"), $lockEnabled);

        if ($this->getTailscaleLockEnabled()) {
            $lockSigned  = $this->getTailscaleLockSigned() ? $this->tr("yes") : $this->tr("no");
            $lockSigning = $this->getTailscaleLockSigning() ? $this->tr("yes") : $this->tr("no");
            $pubKey      = $this->getTailscaleLockPubkey();
            $nodeKey     = $this->getTailscaleLockNodekey();

            $output .= self::printRow("{$lockTranslate}: " . $this->tr("info.lock.signed"), $lockSigned);
            $output .= self::printRow("{$lockTranslate}: " . $this->tr("info.lock.signing"), $lockSigning);
            $output .= self::printRow("{$lockTranslate}: " . $this->tr("info.lock.node_key"), $nodeKey);
            $output .= self::printRow("{$lockTranslate}: " . $this->tr("info.lock.public_key"), $pubKey);
        }

        return $output;
    }

    public function getConnectionInfo(): string
    {
        $status = $this->status;
        $prefs  = $this->prefs;

        $hostName         = isset($status->Self->HostName) ? $status->Self->HostName : $this->tr("unknown");
        $dnsName          = isset($status->Self->DNSName) ? $status->Self->DNSName : $this->tr("unknown");
        $tailscaleIPs     = isset($status->TailscaleIPs) ? implode("<br />", $status->TailscaleIPs) : $this->tr("unknown");
        $magicDNSSuffix   = isset($status->MagicDNSSuffix) ? $status->MagicDNSSuffix : $this->tr("unknown");
        $advertisedRoutes = isset($prefs->AdvertiseRoutes) ? implode("<br />", $prefs->AdvertiseRoutes) : $this->tr("none");
        $acceptRoutes     = isset($prefs->RouteAll) ? ($prefs->RouteAll ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");
        $acceptDNS        = isset($prefs->CorpDNS) ? ($prefs->CorpDNS ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");

        $output = "";
        $output .= self::printRow($this->tr("info.hostname"), $hostName);
        $output .= self::printRow($this->tr("info.dns"), $dnsName);
        $output .= self::printRow($this->tr("info.ip"), $tailscaleIPs);
        $output .= self::printRow($this->tr("info.magicdns"), $magicDNSSuffix);
        $output .= self::printRow($this->tr("info.routes"), $advertisedRoutes);
        $output .= self::printRow($this->tr("info.accept_routes"), $acceptRoutes);
        $output .= self::printRow($this->tr("info.accept_dns"), $acceptDNS);

        return $output;
    }

    public function getDashboardInfo(): string
    {
        $status = $this->status;

        $hostName     = isset($status->Self->HostName) ? $status->Self->HostName : $this->tr("Unknown");
        $dnsName      = isset($status->Self->DNSName) ? $status->Self->DNSName : $this->tr("Unknown");
        $tailscaleIPs = isset($status->TailscaleIPs) ? implode("<br /><span class='w26'>&nbsp;</span>", $status->TailscaleIPs) : $this->tr("unknown");
        $online       = isset($status->Self->Online) ? ($status->Self->Online ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");

        $output = "";
        $output .= self::printDash($this->tr("info.online"), $online);
        $output .= self::printDash($this->tr("info.hostname"), $hostName);
        $output .= self::printDash($this->tr("info.dns"), $dnsName);
        $output .= self::printDash($this->tr("info.ip"), $tailscaleIPs);

        return $output;
    }

    public function getKeyExpirationWarning(): string
    {
        $status = $this->status;

        if (isset($status->Self->KeyExpiry)) {
            $expiryTime = new DateTime($status->Self->KeyExpiry);
            $expiryTime->setTimezone(new DateTimeZone(date_default_timezone_get()));

            $interval      = $expiryTime->diff(new DateTime('now'));
            $expiryPrint   = $expiryTime->format(DateTimeInterface::RFC7231);
            $intervalPrint = $interval->format('%a');

            switch (true) {
                case $interval->days <= 7:
                    $priority = 'error';
                    break;
                case $interval->days <= 30:
                    $priority = 'warn';
                    break;
                default:
                    $priority = 'system';
                    break;
            }

            return "<span class='{$priority}' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . sprintf($this->tr("warnings.key_expiration"), $intervalPrint, $expiryPrint) . "</span>";
        }
        return "";
    }

    public function getTailscaleLockEnabled(): bool
    {
        return $this->lock->Enabled;
    }

    public function getTailscaleLockSigned(): bool
    {
        if ( ! $this->getTailscaleLockEnabled()) {
            return false;
        }

        return $this->lock->NodeKeySigned;
    }

    public function getTailscaleLockNodekey(): string
    {
        if ( ! $this->getTailscaleLockEnabled()) {
            return "";
        }

        return $this->lock->NodeKey;
    }

    public function getTailscaleLockPubkey(): string
    {
        if ( ! $this->getTailscaleLockEnabled()) {
            return "";
        }

        return $this->lock->PublicKey;
    }

    public function getTailscaleLockSigning(): bool
    {
        if ( ! $this->getTailscaleLockSigned()) {
            return false;
        }

        $isTrusted = false;
        $myKey     = $this->getTailscaleLockPubkey();

        foreach ($this->lock->TrustedKeys as $item) {
            if ($item->Key == $myKey) {
                $isTrusted = true;
            }
        }

        return $isTrusted;
    }

    /**
     * @return array<string, string>
     */
    public function getTailscaleLockPending(): array
    {
        if ( ! $this->getTailscaleLockSigning()) {
            return array();
        }

        $pending = array();

        foreach ($this->lock->FilteredPeers as $item) {
            $pending[$item->Name] = $item->NodeKey;
        }

        return $pending;
    }

    public function getTailscaleLockWarning(): string
    {
        if ($this->getTailscaleLockEnabled() && ( ! $this->getTailscaleLockSigned())) {
            return "<span class='error' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . $this->tr("warnings.lock") . "</span>";
        }
        return "";
    }

    /**
     * @param array<mixed> $var
     */
    public function getTailscaleNetbiosWarning(array $var): string
    {
        if (($var['USE_NETBIOS'] == "yes") && ($var['shareSMBEnabled'] != "no")) {
            return "<span class='warn' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . $this->tr("warnings.netbios") . "</span>";
        }
        return "";
    }
}
