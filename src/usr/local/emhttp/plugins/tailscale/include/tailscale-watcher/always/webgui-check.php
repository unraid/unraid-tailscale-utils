<?php

$tailscale_config = $tailscale_config ?? TailscaleHelpers::getPluginConfig();

if ( ! isset($tailscale_ipv4)) {
    TailscaleHelpers::logmsg('Tailscale IP not defined.');
    return;
}

// Make certain that the WebGUI is listening on the Tailscale interface
if ($tailscale_config["INCLUDE_INTERFACE"] == 1) {
    $ident_config = parse_ini_file("/boot/config/ident.cfg") ?: array();

    $connection = @fsockopen($tailscale_ipv4, $ident_config['PORT']);

    if (is_resource($connection)) {
        TailscaleHelpers::logmsg("WebGUI listening on {$tailscale_ipv4}:{$ident_config['PORT']}");
    } else {
        TailscaleHelpers::logmsg("WebGUI not listening on {$tailscale_ipv4}:{$ident_config['PORT']}, terminating and restarting");
        TailscaleHelpers::run_command("/etc/rc.d/rc.nginx term", true);
        sleep(5);
        TailscaleHelpers::run_command("/etc/rc.d/rc.nginx start", true);
    }
}
