<?php

namespace Tailscale;

class Helpers
{
    public static function make_option(string $select, string $value, string $text, string $extra = ""): string
    {
        return "<option value='{$value}'" . ($value == $select ? " selected" : "") . (strlen($extra) ? " {$extra}" : "") . ">{$text}</option>";
    }

    public static function auto_v(string $file): string
    {
        global $docroot;
        $path = $docroot . $file;
        clearstatcache(true, $path);
        $time    = file_exists($path) ? filemtime($path) : 'autov_fileDoesntExist';
        $newFile = "{$file}?v=" . $time;

        return $newFile;
    }

    /**
     * @return array<string>
     */
    public static function run_command(string $command, bool $alwaysShow = false, bool $show = true): array
    {
        $output = array();
        $retval = null;
        if ($show) {
            self::logmsg($command);
        }
        exec("{$command} 2>&1", $output, $retval);

        if (($retval != 0) || $alwaysShow) {
            self::logmsg("Command returned {$retval}" . PHP_EOL . implode(PHP_EOL, $output));
        }

        return $output;
    }

    public static function logmsg(string $message): void
    {
        $timestamp = date('Y/m/d H:i:s');
        $filename  = basename($_SERVER['PHP_SELF']);
        file_put_contents("/var/log/tailscale-utils.log", "{$timestamp} {$filename}: {$message}" . PHP_EOL, FILE_APPEND);
    }

    public static function ip4_in_network(string $ip, string $network): bool
    {
        if (strpos($network, '/') === false) {
            return false;
        }

        list($subnet, $mask) = explode('/', $network, 2);
        $ip_bin_string       = sprintf("%032b", ip2long($ip));
        $net_bin_string      = sprintf("%032b", ip2long($subnet));

        return (substr_compare($ip_bin_string, $net_bin_string, 0, intval($mask)) === 0);
    }

    public static function refreshWebGuiCert(bool $restartIfChanged = true): void
    {
        $status = Info::getStatus();

        $certDomains = $status->CertDomains;

        if (count($certDomains ?? array()) === 0) {
            self::logmsg("Cannot generate certificate for WebGUI -- HTTPS not enabled for Tailnet.");
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

        self::logmsg("Certificate bundle hash: {$pemHash}");

        self::run_command("tailscale cert --cert-file={$certFile} --key-file={$keyFile} --min-validity=720h {$dnsName}");

        if (
            file_exists($certFile) && file_exists($keyFile) && filesize($certFile) > 0 && filesize($keyFile) > 0
        ) {
            file_put_contents($pemFile, file_get_contents($certFile));
            file_put_contents($pemFile, file_get_contents($keyFile), FILE_APPEND);

            if ((sha1_file($pemFile) != $pemHash) && $restartIfChanged) {
                self::logmsg("WebGUI certificate has changed, restarting nginx");
                self::run_command("/etc/rc.d/rc.nginx reload");
            }
        } else {
            self::logmsg("Something went wrong when creating WebGUI certificate, skipping nginx update.");
        }
    }

    public static function applyGRO(): void
    {
        /** @var array<int, array<string>> $ip_route */
        $ip_route = (array) json_decode(implode(self::run_command('ip -j route get 8.8.8.8')), true);

        // Check if a device was returned
        if ( ! isset($ip_route[0]['dev'])) {
            self::logmsg("Default interface could not be detected.");
            return;
        }

        $dev = $ip_route[0]['dev'];

        /** @var array<string, array<string>> $ethtool */
        $ethtool = ((array) json_decode(implode(self::run_command("ethtool --json -k {$dev}")), true))[0];

        if (isset($ethtool['rx-udp-gro-forwarding']) && ! $ethtool['rx-udp-gro-forwarding']['active']) {
            self::run_command("ethtool -K {$dev} rx-udp-gro-forwarding on");
        }

        if (isset($ethtool['rx-gro-list']) && $ethtool['rx-gro-list']['active']) {
            self::run_command("ethtool -K {$dev} rx-gro-list off");
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPluginConfig(): array
    {
        $config_file   = '/boot/config/plugins/tailscale/tailscale.cfg';
        $defaults_file = '/usr/local/emhttp/plugins/tailscale/settings.json';

        // Load configuration file
        if (file_exists($config_file)) {
            $tailscale_config = parse_ini_file($config_file) ?: array();
        } else {
            $tailscale_config = array();
        }

        // Load default settings and assign values
        /** @var array<string, array<string>> $settings_config */
        $settings_config = (array) json_decode(file_get_contents($defaults_file) ?: "{}", true);
        foreach ($settings_config as $key => $value) {
            if ( ! isset($tailscale_config[$key])) {
                $tailscale_config[$key] = $value['default'];
            }
        }

        return $tailscale_config;
    }
}
