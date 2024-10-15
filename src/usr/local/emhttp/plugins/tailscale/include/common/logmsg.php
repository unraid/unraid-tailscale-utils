<?php

function logmsg(string $message) : void
{
    $timestamp = date('Y/m/d H:i:s');
    $filename  = basename($_SERVER['PHP_SELF']);
    file_put_contents("/var/log/tailscale-utils.log", "{$timestamp} {$filename}: {$message}" . PHP_EOL, FILE_APPEND);
}
