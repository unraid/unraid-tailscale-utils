<?php

// Make certain that the WebGUI is listening on the Tailscale interface
if ($tailscale_config["INCLUDE_INTERFACE"] == 1) {
    $ident_config = parse_ini_file("/boot/config/ident.cfg");

    $connection = @fsockopen($tailscale_ipv4, $ident_config['PORT']);

    if (is_resource($connection)) {
        logmsg("WebGUI listening on {$tailscale_ipv4}:{$ident_config['PORT']}");
    } else {
        logmsg("WebGUI not listening on {$tailscale_ipv4}:{$ident_config['PORT']}, terminating and restarting");
        exec("/etc/init.d/rc.nginx term");
        sleep(5);
        exec("/etc/init.d/rc.nginx start");
    }
}