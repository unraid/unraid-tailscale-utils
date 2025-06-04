<?php

namespace Tailscale;

class Utils extends \EDACerton\PluginUtils\Utils
{
    public function setPHPDebug(): void
    {
        $version = parse_ini_file('/var/local/emhttp/plugins/tailscale/tailscale.ini');

        if ( ! $version) {
            $this->logmsg("Could not retrieve system data, skipping debug check.");
            return;
        }

        if ((($version['BRANCH'] ?? "") == "trunk") && ! defined("PLUGIN_DEBUG")) {
            error_reporting(E_ALL);
            define("PLUGIN_DEBUG", true);
        }
    }

    public static function printRow(string $title, string $value): string
    {
        return "<tr><td>{$title}</td><td>{$value}</td></tr>" . PHP_EOL;
    }

    public static function printDash(string $title, string $value): string
    {
        return "<tr><td><span class='w26'>{$title}</span>{$value}</td></tr>" . PHP_EOL;
    }

    public static function formatWarning(?Warning $warning): string
    {
        if ($warning == null) {
            return "";
        }

        return "<span class='{$warning->Priority}' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . $warning->Message . "</span>";
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

    public static function logwrap(string $message, bool $debug = false, bool $rateLimit = false): void
    {
        if ( ! defined(__NAMESPACE__ . "\PLUGIN_NAME")) {
            throw new \RuntimeException("PLUGIN_NAME is not defined.");
        }
        $utils = new Utils(PLUGIN_NAME);
        $utils->logmsg($message, $debug, $rateLimit);
    }

    /**
     * @return array<string>
     */
    public static function runwrap(string $command, bool $alwaysShow = false, bool $show = true): array
    {
        if ( ! defined(__NAMESPACE__ . "\PLUGIN_NAME")) {
            throw new \RuntimeException("PLUGIN_NAME is not defined.");
        }
        $utils = new Utils(PLUGIN_NAME);
        return $utils->run_command($command, $alwaysShow, $show);
    }

    public static function validateCidr(string $cidr): bool
    {
        $parts = explode('/', $cidr);
        if (count($parts) != 2) {
            return false;
        }

        $ip      = $parts[0];
        $netmask = $parts[1];

        if ( ! preg_match("/^\d+$/", $netmask)) {
            return false;
        }

        $netmask = intval($parts[1]);

        if ($netmask < 0) {
            return false;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return $netmask <= 32;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return $netmask <= 128;
        }

        return false;
    }

    /**
     * @return array<string>
     */
    public static function getExitRoutes(): array
    {
        return ["0.0.0.0/0", "::/0"];
    }
}
