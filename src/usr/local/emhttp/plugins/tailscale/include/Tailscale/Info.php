<?php

namespace Tailscale;

class Info
{
    private string $useNetbios;
    private string $smbEnabled;
    private Translator $tr;
    private \stdClass $status;
    private \stdClass $prefs;
    private \stdClass $lock;

    public function __construct(Translator $tr)
    {
        $share_config = parse_ini_file("/boot/config/share.cfg") ?: array();
        $ident_config = parse_ini_file("/boot/config/ident.cfg") ?: array();

        $this->tr         = $tr;
        $this->smbEnabled = $share_config['shareSMBEnabled'] ?? "";
        $this->useNetbios = $ident_config['USE_NETBIOS']     ?? "";
        $this->status     = self::getStatus();
        $this->prefs      = self::getPrefs();
        $this->lock       = self::getLock();
    }

    public static function getStatus(): \stdClass
    {
        exec("tailscale status --json", $out_status);
        return (object) json_decode(implode($out_status));
    }

    public static function getPrefs(): \stdClass
    {
        exec("tailscale debug prefs", $out_prefs);
        return (object) json_decode(implode($out_prefs));
    }

    public static function getLock(): \stdClass
    {
        exec("tailscale lock status -json=true", $out_status);
        return (object) json_decode(implode($out_status));
    }

    private function tr(string $message): string
    {
        return $this->tr->tr($message);
    }

    public function getStatusInfo(): StatusInfo
    {
        $status = $this->status;
        $prefs  = $this->prefs;
        $lock   = $this->lock;

        $statusInfo = new StatusInfo();

        $statusInfo->TsVersion     = isset($status->Version) ? $status->Version : $this->tr("unknown");
        $statusInfo->KeyExpiration = isset($status->Self->KeyExpiry) ? $status->Self->KeyExpiry : $this->tr("disabled");
        $statusInfo->Online        = isset($status->Self->Online) ? ($status->Self->Online ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");
        $statusInfo->InNetMap      = isset($status->Self->InNetworkMap) ? ($status->Self->InNetworkMap ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");
        $statusInfo->Tags          = isset($status->Self->Tags) ? implode("<br />", $status->Self->Tags) : "";
        $statusInfo->LoggedIn      = isset($prefs->LoggedOut) ? ($prefs->LoggedOut ? $this->tr("no") : $this->tr("yes")) : $this->tr("unknown");
        $statusInfo->TsHealth      = isset($status->Health) ? implode("<br />", $status->Health) : "";
        $statusInfo->LockEnabled   = $this->getTailscaleLockEnabled() ? $this->tr("yes") : $this->tr("no");

        if ($this->getTailscaleLockEnabled()) {
            $lockInfo = new LockInfo();

            $lockInfo->LockSigned  = $this->getTailscaleLockSigned() ? $this->tr("yes") : $this->tr("no");
            $lockInfo->LockSigning = $this->getTailscaleLockSigning() ? $this->tr("yes") : $this->tr("no");
            $lockInfo->PubKey      = $this->getTailscaleLockPubkey();
            $lockInfo->NodeKey     = $this->getTailscaleLockNodekey();

            $statusInfo->LockInfo = $lockInfo;
        }

        return $statusInfo;
    }

    public function getConnectionInfo(): ConnectionInfo
    {
        $status = $this->status;
        $prefs  = $this->prefs;

        $info = new ConnectionInfo();

        $info->HostName         = isset($status->Self->HostName) ? $status->Self->HostName : $this->tr("unknown");
        $info->DNSName          = isset($status->Self->DNSName) ? $status->Self->DNSName : $this->tr("unknown");
        $info->TailscaleIPs     = isset($status->TailscaleIPs) ? implode("<br />", $status->TailscaleIPs) : $this->tr("unknown");
        $info->MagicDNSSuffix   = isset($status->MagicDNSSuffix) ? $status->MagicDNSSuffix : $this->tr("unknown");
        $info->AdvertisedRoutes = isset($prefs->AdvertiseRoutes) ? implode("<br />", $prefs->AdvertiseRoutes) : $this->tr("none");
        $info->AcceptRoutes     = isset($prefs->RouteAll) ? ($prefs->RouteAll ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");
        $info->AcceptDNS        = isset($prefs->CorpDNS) ? ($prefs->CorpDNS ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");

        return $info;
    }

    public function getDashboardInfo(): DashboardInfo
    {
        $status = $this->status;

        $info = new DashboardInfo();

        $info->HostName     = isset($status->Self->HostName) ? $status->Self->HostName : $this->tr("Unknown");
        $info->DNSName      = isset($status->Self->DNSName) ? $status->Self->DNSName : $this->tr("Unknown");
        $info->TailscaleIPs = isset($status->TailscaleIPs) ? $status->TailscaleIPs : array();
        $info->Online       = isset($status->Self->Online) ? ($status->Self->Online ? $this->tr("yes") : $this->tr("no")) : $this->tr("unknown");

        return $info;
    }

    public function getKeyExpirationWarning(): ?Warning
    {
        $status = $this->status;

        if (isset($status->Self->KeyExpiry)) {
            $expiryTime = new \DateTime($status->Self->KeyExpiry);
            $expiryTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));

            $interval      = $expiryTime->diff(new \DateTime('now'));
            $expiryPrint   = $expiryTime->format(\DateTimeInterface::RFC7231);
            $intervalPrint = $interval->format('%a');

            $warning = new Warning(sprintf($this->tr("warnings.key_expiration"), $intervalPrint, $expiryPrint));

            switch (true) {
                case $interval->days <= 7:
                    $warning->Priority = 'error';
                    break;
                case $interval->days <= 30:
                    $warning->Priority = 'warn';
                    break;
                default:
                    $warning->Priority = 'system';
                    break;
            }

            return $warning;
        }
        return null;
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

    public function getTailscaleLockWarning(): ?Warning
    {
        if ($this->getTailscaleLockEnabled() && ( ! $this->getTailscaleLockSigned())) {
            return new Warning($this->tr("warnings.lock"), "error");
        }
        return null;
    }

    public function getTailscaleNetbiosWarning(): ?Warning
    {
        if (($this->useNetbios == "yes") && ($this->smbEnabled != "no")) {
            return new Warning($this->tr("warnings.netbios"), "warn");
        }
        return null;
    }

    /**
     * @return array<int, PeerStatus>
     */
    public function getPeerStatus(): array
    {
        $result = array();

        foreach ($this->status->Peer as $node => $status) {
            $peer = new PeerStatus();

            $peer->Name = trim($status->DNSName, ".");
            $peer->IP   = $status->TailscaleIPs;

            $peer->LoginName  = $this->status->User->{$status->UserID}->LoginName;
            $peer->SharedUser = isset($status->ShareeNode);

            if ($status->ExitNode) {
                $peer->ExitNodeActive = true;
            } elseif ($status->ExitNodeOption) {
                $peer->ExitNodeAvailable = true;
            }
            $peer->Mullvad = in_array("tag:mullvad-exit-node", $status->Tags ?? array());

            if ($status->TxBytes > 0 || $status->RxBytes > 0) {
                $peer->Traffic = true;
                $peer->TxBytes = $status->TxBytes;
                $peer->RxBytes = $status->RxBytes;
            }

            if ( ! $status->Online) {
                $peer->Online = false;
                $peer->Active = false;
            } elseif ( ! $status->Active) {
                $peer->Online = true;
                $peer->Active = false;
            } else {
                $peer->Online = true;
                $peer->Active = true;

                if (($status->Relay != "") && ($status->CurAddr == "")) {
                    $peer->Relayed = true;
                    $peer->Address = $status->Relay;
                } elseif ($status->CurAddr != "") {
                    $peer->Relayed = false;
                    $peer->Address = $status->CurAddr;
                }
            }

            $result[] = $peer;
        }

        return $result;
    }
}
