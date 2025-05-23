<?php

namespace Tailscale;

enum NotificationType: string
{
    case NORMAL  = 'normal';
    case WARNING = 'warning';
    case ALERT   = 'alert';
}

class System
{
    public const RESTART_COMMAND = "/usr/local/emhttp/webGui/scripts/reload_services";
    public const NOTIFY_COMMAND  = "/usr/local/emhttp/webGui/scripts/notify";

    public static function fixLocalSubnetRoutes(): void
    {
        $ips = parse_ini_file("/boot/config/network.cfg") ?: array();
        if (array_key_exists(('IPADDR'), $ips)) {
            $route_table = Utils::run_command("ip route list table 52", false, false);

            $ipaddr = is_array($ips['IPADDR']) ? $ips['IPADDR'] : array($ips['IPADDR']);

            foreach ($ipaddr as $ip) {
                foreach ($route_table as $route) {
                    $net = explode(' ', $route)[0];
                    if (Utils::ip4_in_network($ip, $net)) {
                        Utils::logmsg("Detected local IP {$ip} in Tailscale route {$net}, removing");
                        Utils::run_command("ip route del '{$net}' dev tailscale1 table 52");
                    }
                }
            }
        }
    }

    public static function checkWebgui(Config $config, string $tailscale_ipv4): void
    {
        // Make certain that the WebGUI is listening on the Tailscale interface
        if ($config->IncludeInterface) {
            $ident_config = parse_ini_file("/boot/config/ident.cfg") ?: array();

            $connection = @fsockopen($tailscale_ipv4, $ident_config['PORT']);

            if (is_resource($connection)) {
                Utils::logmsg("WebGUI listening on {$tailscale_ipv4}:{$ident_config['PORT']}", false, true);
            } else {
                Utils::logmsg("WebGUI not listening on {$tailscale_ipv4}:{$ident_config['PORT']}, terminating and restarting");
                Utils::run_command("/etc/rc.d/rc.nginx term");
                sleep(5);
                Utils::run_command("/etc/rc.d/rc.nginx start");
            }
        }
    }

    public static function checkServeConfig(): void
    {
        $ident_config = parse_ini_file("/boot/config/ident.cfg") ?: array();

        $httpPort = isset($ident_config['PORT']) && is_scalar($ident_config['PORT'])
                     ? intval($ident_config['PORT']) : 80;
        $httpsPort = isset($ident_config['PORTSSL']) && is_scalar($ident_config['PORTSSL'])
                     ? intval($ident_config['PORTSSL']) : 443;

        $localAPI    = new LocalAPI();
        $serveConfig = $localAPI->getServeConfig();

        $tcpConfig = $serveConfig->TCP ?? array();

        foreach ($tcpConfig as $key => $val) {
            $configPort = intval($key);

            if ($configPort == $httpPort || $configPort == $httpsPort) {
                Utils::logmsg("Serve TCP Port {$configPort} conflicts with WebGUI, removing");
                self::sendNotification(
                    "Tailscale Serve Port Conflict",
                    "Tailscale Serve Port Conflict",
                    "Port {$configPort} conflicts with WebGUI port. The Tailscale serve config has been reset to remove the conflict.",
                    NotificationType::ALERT
                );
                $localAPI->resetServeConfig();
                Utils::run_command(self::RESTART_COMMAND);

                return;
            } else {
                Utils::logmsg("Checked for WebGUI conflict with serve TCP Port {$configPort}", false, true);
            }
        }
    }

    public static function restartSystemServices(Config $config): void
    {
        if ($config->IncludeInterface) {
            self::refreshWebGuiCert(false);

            Utils::run_command(self::RESTART_COMMAND);
        }
    }

    public static function enableIPForwarding(Config $config): void
    {
        if ($config->Enable) {
            Utils::logmsg("Enabling IP forwarding");
            $sysctl = "net.ipv4.ip_forward = 1" . PHP_EOL . "net.ipv6.conf.all.forwarding = 1";
            file_put_contents('/etc/sysctl.d/99-tailscale.conf', $sysctl);
            Utils::run_command("sysctl -p /etc/sysctl.d/99-tailscale.conf", true);
        }
    }

    public static function applyGRO(): void
    {
        /** @var array<int, array<string>> $ip_route */
        $ip_route = (array) json_decode(implode(Utils::run_command('ip -j route get 8.8.8.8')), true);

        // Check if a device was returned
        if ( ! isset($ip_route[0]['dev'])) {
            Utils::logmsg("Default interface could not be detected.");
            return;
        }

        $dev = $ip_route[0]['dev'];

        /** @var array<string, array<string>> $ethtool */
        $ethtool = ((array) json_decode(implode(Utils::run_command("ethtool --json -k {$dev}")), true))[0];

        if (isset($ethtool['rx-udp-gro-forwarding']) && ! $ethtool['rx-udp-gro-forwarding']['active']) {
            Utils::run_command("ethtool -K {$dev} rx-udp-gro-forwarding on");
        }

        if (isset($ethtool['rx-gro-list']) && $ethtool['rx-gro-list']['active']) {
            Utils::run_command("ethtool -K {$dev} rx-gro-list off");
        }
    }

    public static function notifyOnKeyExpiration(): void
    {
        $localAPI = new LocalAPI();
        $status   = $localAPI->getStatus();

        if (isset($status->Self->KeyExpiry)) {
            $expiryTime = new \DateTime($status->Self->KeyExpiry);
            $expiryTime->setTimezone(new \DateTimeZone(date_default_timezone_get()));
            $interval = $expiryTime->diff(new \DateTime('now'));

            $expiryPrint   = $expiryTime->format(\DateTimeInterface::RFC7231);
            $intervalPrint = $interval->format('%a');

            $message = "The Tailscale key will expire in {$intervalPrint} days on {$expiryPrint}.";
            Utils::logmsg($message);

            switch (true) {
                case $interval->days <= 7:
                    $priority = NotificationType::ALERT;
                    break;
                case $interval->days <= 30:
                    $priority = NotificationType::WARNING;
                    break;
                default:
                    return;
            }

            $event = "Tailscale Key Expiration - {$priority->value} - {$expiryTime->format('Ymd')}";
            Utils::logmsg("Sending notification for key expiration: {$event}");
            self::sendNotification($event, "Tailscale key is expiring", $message, $priority);
        } else {
            Utils::logmsg("Tailscale key expiration is not set.");
        }
    }

    public static function sendNotification(string $event, string $subject, string $message, NotificationType $priority): void
    {
        $command = self::NOTIFY_COMMAND . " -l '/Settings/Tailscale' -e " . escapeshellarg($event) . " -s " . escapeshellarg($subject) . " -d " . escapeshellarg("{$message}") . " -i \"{$priority->value}\" -x 2>/dev/null";
        exec($command);
    }

    public static function refreshWebGuiCert(bool $restartIfChanged = true): void
    {
        $localAPI = new LocalAPI();
        $status   = $localAPI->getStatus();

        $certDomains = $status->CertDomains;

        if (count($certDomains ?? array()) === 0) {
            Utils::logmsg("Cannot generate certificate for WebGUI -- HTTPS not enabled for Tailnet.");
            return;
        }

        $dnsName = $certDomains[0];

        $certFile = "/boot/config/plugins/tailscale/state/certs/{$dnsName}.crt";
        $keyFile  = "/boot/config/plugins/tailscale/state/certs/{$dnsName}.key";
        $pemFile  = "/boot/config/ssl/certs/ts_bundle.pem";

        clearstatcache();

        $pemHash = '';
        if (file_exists($pemFile)) {
            $pemHash = sha1_file($pemFile);
        }

        Utils::logmsg("Certificate bundle hash: {$pemHash}");

        Utils::run_command("tailscale cert --cert-file={$certFile} --key-file={$keyFile} --min-validity=720h {$dnsName}");

        if (
            file_exists($certFile) && file_exists($keyFile) && filesize($certFile) > 0 && filesize($keyFile) > 0
        ) {
            file_put_contents($pemFile, file_get_contents($certFile));
            file_put_contents($pemFile, file_get_contents($keyFile), FILE_APPEND);

            if ((sha1_file($pemFile) != $pemHash) && $restartIfChanged) {
                Utils::logmsg("WebGUI certificate has changed, restarting nginx");
                Utils::run_command("/etc/rc.d/rc.nginx reload");
            }
        } else {
            Utils::logmsg("Something went wrong when creating WebGUI certificate, skipping nginx update.");
        }
    }

    public static function setExtraInterface(Config $config): void
    {
        if (file_exists(self::RESTART_COMMAND)) {
            $include_array      = array();
            $exclude_interfaces = "";
            $write_file         = true;
            $network_extra_file = '/boot/config/network-extra.cfg';
            $ifname             = 'tailscale1';

            if (file_exists($network_extra_file)) {
                $netExtra = parse_ini_file($network_extra_file);
                if ($netExtra['include_interfaces'] ?? false) {
                    $include_array = explode(' ', $netExtra['include_interfaces']);
                }
                if ($netExtra['exclude_interfaces'] ?? false) {
                    $exclude_interfaces = $netExtra['exclude_interfaces'];
                }
                $write_file = false;
            }

            $in_array = in_array($ifname, $include_array);

            if ($in_array != $config->IncludeInterface) {
                if ($config->IncludeInterface) {
                    $include_array[] = $ifname;
                    Utils::logmsg("{$ifname} added to include_interfaces");
                } else {
                    $include_array = array_diff($include_array, [$ifname]);
                    Utils::logmsg("{$ifname} removed from include_interfaces");
                }
                $write_file = true;
            }

            if ($write_file) {
                $include_interfaces = implode(' ', $include_array);

                $file = <<<END
                    include_interfaces="{$include_interfaces}"
                    exclude_interfaces="{$exclude_interfaces}"

                    END;

                file_put_contents($network_extra_file, $file);
                Utils::logmsg("Updated network-extra.cfg");
            }
        }
    }

    private static function disableTailscaleFeature(LocalAPI $localAPI, bool $allow, string $flag): void
    {
        if ($allow) {
            Utils::logmsg("Ignoring {$flag}");
        } else {
            $localAPI->patchPref($flag, false);
        }
    }

    public static function applyTailscaleConfig(Config $config): void
    {
        $localAPI = new LocalAPI();

        self::disableTailscaleFeature($localAPI, $config->AllowRoutes, 'RouteAll');
        self::disableTailscaleFeature($localAPI, $config->AllowDNS, 'CorpDNS');

        $localAPI->patchPref('NoStatefulFiltering', true);
    }

    public static function createTailscaledParamsFile(Config $config): void
    {
        $custom_params = "";

        if ($config->WgPort > 0 && $config->WgPort < 65535) {
            $custom_params .= "-port {$config->WgPort} ";
        }

        file_put_contents('/usr/local/emhttp/plugins/tailscale/custom-params.sh', 'TAILSCALE_CUSTOM_PARAMS="' . $custom_params . '"');
    }
}
