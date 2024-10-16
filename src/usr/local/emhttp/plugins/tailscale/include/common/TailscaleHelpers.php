<?php

class TailscaleHelpers
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
            TailscaleHelpers::logmsg($command);
        }
        exec("{$command} 2>&1", $output, $retval);

        if (($retval != 0) || $alwaysShow) {
            TailscaleHelpers::logmsg("Command returned {$retval}" . PHP_EOL . implode(PHP_EOL, $output));
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
}
