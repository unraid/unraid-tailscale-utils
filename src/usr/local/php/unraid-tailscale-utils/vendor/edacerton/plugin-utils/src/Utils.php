<?php

namespace EDACerton\PluginUtils;

/*
    Copyright (C) 2025  Derek Kaser

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Utils
{
    private string $pluginName;

    public function __construct(string $pluginName)
    {
        $this->pluginName = $pluginName;
        if ( ! defined(__NAMESPACE__ . "\PLUGIN_NAME")) {
            define(__NAMESPACE__ . "\PLUGIN_NAME", $pluginName);
        }
    }

    /**
     * @return array<string>
     */
    public function run_command(string $command, bool $alwaysShow = false, bool $show = true): array
    {
        $output = array();
        $retval = null;
        if ($show) {
            $this->logmsg("exec: {$command}");
        }
        exec("{$command} 2>&1", $output, $retval);

        if (($retval != 0) || $alwaysShow) {
            $this->logmsg("Command returned {$retval}" . PHP_EOL . implode(PHP_EOL, $output));
        }

        return $output;
    }

    public function logmsg(string $message, bool $debug = false, bool $rateLimit = false): void
    {
        if ($rateLimit && (intval(date("i")) % 10 != 0)) {
            // Only log rate limited messages every 10 minutes
            return;
        }

        if ($debug) {
            if (defined("PLUGIN_DEBUG")) {
                $message = "DEBUG: " . $message;
            } else {
                return;
            }
        }

        $timestamp = date('Y/m/d H:i:s');
        $filename  = basename(is_string($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : "");
        file_put_contents("/var/log/" . $this->pluginName . ".log", "{$timestamp} {$filename}: {$message}" . PHP_EOL, FILE_APPEND);
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

    public static function make_option(bool|string $selected, string $value, string $text, string $extra = ""): string
    {
        if (is_string($selected)) {
            $selected = $selected === $value;
        }

        return "<option value='{$value}'" . ($selected ? " selected" : "") . (strlen($extra) ? " {$extra}" : "") . ">{$text}</option>";
    }

    /**
    * @param array<mixed> $args
    */
    public function run_task(string $functionName, array $args = array()): void
    {
        try {
            $reflectionMethod = new \ReflectionMethod($functionName);
            $reflectionMethod->invokeArgs(null, $args);
        } catch (\Throwable $e) {
            $this->logmsg("Caught exception in {$functionName} : " . $e->getMessage());
        }
    }

    /**
     * @return array<string, string>
     */
    public static function parse_plugin_cfg(string $plugin): array
    {
        $default = "/usr/local/emhttp/plugins/{$plugin}/default.cfg";
        $user    = "/boot/config/plugins/{$plugin}/{$plugin}.cfg";

        $cfg_default = parse_ini_file($default, false, INI_SCANNER_RAW) ?: array();
        $cfg_user    = parse_ini_file($user, false, INI_SCANNER_RAW) ?: array();
        return array_replace_recursive($cfg_default, $cfg_user);
    }
}
