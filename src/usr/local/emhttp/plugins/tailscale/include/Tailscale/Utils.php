<?php

namespace Tailscale;

class Utils
{
    /**
    * @param array<mixed> $args
    */
    public static function run_task(string $functionName, array $args = array()): void
    {
        try {
            $reflectionMethod = new \ReflectionMethod($functionName);
            $reflectionMethod->invokeArgs(null, $args);
        } catch (\Throwable $e) {
            Utils::logmsg("Caught exception in {$functionName} : " . $e->getMessage());
        }
    }

    /**
    * @param array<mixed> $content
    */
    private static function send_usage(string $url, array $content): int
    {
        $body  = json_encode($content);
        $token = self::download_url($url . '?connect');

        $c = curl_init();
        curl_setopt($c, CURLOPT_URL, $url);

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token
        ];

        curl_setopt($c, CURLOPT_POST, true);
        curl_setopt($c, CURLOPT_POSTFIELDS, $body);
        curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($c, CURLOPT_USERAGENT, 'plugin-metrics/1.0.0');

        curl_exec($c);
        if ( ! curl_errno($c)) {
            $info = curl_getinfo($c);
            return $info['http_code'];
        }
        return -1;
    }

    public static function setPHPDebug(): void
    {
        $version = parse_ini_file('/var/local/emhttp/plugins/tailscale/tailscale.ini');

        if ( ! $version) {
            Utils::logmsg("Could not retrieve system data, skipping debug check.");
            return;
        }

        if ((($version['BRANCH'] ?? "") == "trunk") && ! defined("TAILSCALE_TRUNK")) {
            error_reporting(E_ALL);
            define("TAILSCALE_TRUNK", true);
        }
    }

    public static function sendUsageData(Config $config): void
    {
        $endpoint = "https://plugin-usage.edacerton.win/";

        if ($config->Usage) {
            $var     = parse_ini_file('/usr/local/emhttp/state/var.ini');
            $version = parse_ini_file('/var/local/emhttp/plugins/tailscale/tailscale.ini');

            if ( ! $var || ! $version) {
                Utils::logmsg("Could not retrieve system data, skipping usage data.");
                return;
            }

            $prefs = Info::getPrefs();

            if (isset($prefs->LoggedOut) ? ($prefs->LoggedOut ? true : false) : true) {
                Utils::logmsg("Skipping usage data collection; not logged in.");
                return;
            }

            $exit          = false;
            $subnet        = false;
            $customControl = false;

            foreach (($prefs->AdvertiseRoutes ?? array()) as $net) {
                switch ($net) {
                    case "0.0.0.0/0":
                    case "::/0":
                        $exit = true;
                        break;
                    default:
                        $subnet = true;
                        break;
                }
            }

            if ($prefs->ControlURL != "https://controlplane.tailscale.com") {
                $customControl = true;
            }

            $content = array(
                'clientId'       => hash("crc32b", $var['flashGUID']),
                'plugin'         => 'tailscale',
                'plugin_version' => $version['VERSION'],
                'plugin_branch'  => $version['BRANCH'],
                'unraid_version' => $var['version'],
                'bool1'          => $config->AllowDNS,
                'bool2'          => $config->AllowRoutes,
                'bool3'          => $config->IncludeInterface,
                'bool4'          => $subnet,
                'bool5'          => $exit,
                'num1'           => $customControl ? 0 : 1
            );

            $attempts = 0;
            $delay    = rand(0, 300);
            do {
                Utils::logmsg("Waiting for {$delay} seconds before sending usage data.");
                sleep($delay);
                $delay += 300;
                $attempts++;

                $result = self::send_usage($endpoint, $content);
                Utils::logmsg("Usage data sent.");
            } while (($result != '200') && ($attempts < 3));

            if ($result != '200') {
                Utils::logmsg("Error occurred while transmitting usage data.");
            }
        } else {
            Utils::logmsg("Usage collection disabled.");
        }
    }

    public static function download_url(string $url): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 45);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_REFERER, "");
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'plugin-metrics/1.0.0');
        $out = curl_exec($ch) ?: false;
        curl_close($ch);
        return strval($out);
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

    public static function make_option(bool $selected, string $value, string $text, string $extra = ""): string
    {
        return "<option value='{$value}'" . ($selected ? " selected" : "") . (strlen($extra) ? " {$extra}" : "") . ">{$text}</option>";
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
            self::logmsg("exec: {$command}");
        }
        exec("{$command} 2>&1", $output, $retval);

        if (($retval != 0) || $alwaysShow) {
            self::logmsg("Command returned {$retval}" . PHP_EOL . implode(PHP_EOL, $output));
        }

        return $output;
    }

    public static function logmsg(string $message, bool $debug = false): void
    {
        if ($debug) {
            if (defined("TAILSCALE_TRUNK")) {
                $message = "DEBUG: " . $message;
            } else {
                return;
            }
        }
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
}
