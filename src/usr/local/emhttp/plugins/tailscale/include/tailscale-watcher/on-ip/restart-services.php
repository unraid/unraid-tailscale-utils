<?php

if ( ! isset($tailscale_config)) {
    echo('Tailscale config not defined.');
    return;
}
if ( ! isset($restart_command)) {
    echo('Restart command not defined.');
    return;
}

if ($tailscale_config["INCLUDE_INTERFACE"] == 1) {
    Tailscale\Helpers::refreshWebGuiCert(false);

    Tailscale\Helpers::logmsg("Restarting Unraid services");
    Tailscale\Helpers::run_command($restart_command);

    // Wait to allow services to restart before continuing
    sleep(15);
}
