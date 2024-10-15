<?php

if(!isset($tailscale_config)) {
    throw new Exception('Tailscale config not defined.');
}
if(!isset($restart_command)) {
    throw new Exception('Restart command not defined.');
}

if ($tailscale_config["INCLUDE_INTERFACE"] == 1) {
    refreshWebGuiCert(false);

    logmsg("Restarting Unraid services");
    exec($restart_command);

    // Wait to allow services to restart before continuing
    sleep(15);
}
