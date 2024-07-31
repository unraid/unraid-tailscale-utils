<?php

// Wait for two minutes, then check to see if nginx is listening on the Unraid address
// If it isn't, this probably means that nginx didn't reload properly and needs to be forced.
if (isset($tailscale_ipv4)) {
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