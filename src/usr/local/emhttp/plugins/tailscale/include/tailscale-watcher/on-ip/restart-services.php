<?php

if ($tailscale_config["INCLUDE_INTERFACE"] == 1) {
    refreshWebGuiCert($false);

    logmsg("Restarting Unraid services");
    exec($restart_command);

    // Wait to allow services to restart before continuing
    sleep(15);
}
