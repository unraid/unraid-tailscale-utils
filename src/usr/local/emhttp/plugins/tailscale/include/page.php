<?php

namespace Tailscale;

/**
* @param array<string, mixed> $params
*/
function getPage(string $filename, bool $niceError = true, array $params = array()): string|false
{
    try {
        return includePage(dirname(__FILE__) . "/Pages/{$filename}.php", $params);
    } catch (\Throwable $e) {
        if ($niceError) {
            file_put_contents("/var/log/tailscale-error.log", print_r($e, true) . PHP_EOL, FILE_APPEND);
            return includePage(dirname(__FILE__) . "/Pages/Error.php", array("e" => $e));
        } else {
            throw $e;
        }
    }
}

/**
* @param array<string, mixed> $params
*/
function includePage(string $filename, array $params = array()): string|false
{
    extract($params);

    if (is_file($filename)) {
        ob_start();
        include $filename;
        return ob_get_clean();
    }
    return false;
}
