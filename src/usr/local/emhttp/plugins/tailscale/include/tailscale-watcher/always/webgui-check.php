<?php

// Make certain that the WebGUI is listening on the Tailscale interface
if ($tailscale_config["INCLUDE_INTERFACE"] == 1) {
    $ident_config = parse_ini_file("/boot/config/ident.cfg");

    $connection = @fsockopen($tailscale_ipv4, $ident_config['PORT']);

    if (is_resource($connection)) {
        logmsg("WebGUI listening on {$tailscale_ipv4}:{$ident_config['PORT']}");
    } else {
        logmsg("WebGUI not listening on {$tailscale_ipv4}:{$ident_config['PORT']}, terminating and restarting");
        run_command("/etc/rc.d/rc.nginx term", true);
        sleep(5);
        run_command("/etc/rc.d/rc.nginx start", true);
    }
}
