<?php

foreach (glob("/usr/local/emhttp/plugins/tailscale/include/common/*.php") as $file) {
    try {
        require $file;
    } catch (Throwable $e) {
        logmsg("Caught exception in {$file} : " . $e->getMessage());
    }
}

$plugin = "tailscale";

$restart_command    = '/usr/local/emhttp/webGui/scripts/reload_services';

$tailscale_config = getPluginConfig();

$configure_extra_interfaces = file_exists($restart_command);
