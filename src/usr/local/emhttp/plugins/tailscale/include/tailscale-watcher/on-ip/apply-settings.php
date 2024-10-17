<?php

function apply_flag(string $setting, string $flag): void
{
    global $tailscale_config;

    switch ($tailscale_config[$setting]) {
        case "0":
            Tailscale\Helpers::run_command("/usr/local/sbin/tailscale set {$flag}=false");
            break;
        case "1":
            Tailscale\Helpers::run_command("/usr/local/sbin/tailscale set {$flag}=true");
            break;
        default:
            Tailscale\Helpers::logmsg("Ignoring {$flag}");
    }
}

apply_flag('ACCEPT_ROUTES', '--accept-routes');
apply_flag('ACCEPT_DNS', '--accept-dns');

Tailscale\Helpers::run_command("/usr/local/sbin/tailscale set --stateful-filtering=false");
