#!/usr/bin/php -q
<?php

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "{$docroot}/plugins/tailscale/include/common.php";

$tailscale_config = $tailscale_config ?? getTailscaleConfig();

if ( ! isset($restart_command)) {
    throw new Exception('Restart command not defined.');
}

// Log current settings
foreach ($tailscale_config as $key => $value) {
    TailscaleHelpers::logmsg("Setting: {$key}: {$value}");
}

foreach (glob("{$docroot}/plugins/tailscale/include/pre-startup/*.php") ?: array() as $file) {
    TailscaleHelpers::logmsg("Executing {$file}");
    try {
        require_once $file;
    } catch (Throwable $e) {
        TailscaleHelpers::logmsg("Caught exception in {$file} : " . $e->getMessage());
    }
}

if ($tailscale_config['ENABLE_TAILSCALE'] == "1") {
    TailscaleHelpers::run_command('/etc/rc.d/rc.tailscale restart');
} else {
    TailscaleHelpers::run_command('/etc/rc.d/rc.tailscale stop');
    TailscaleHelpers::run_command($restart_command);
}
