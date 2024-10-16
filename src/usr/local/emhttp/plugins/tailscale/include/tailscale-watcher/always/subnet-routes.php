<?php

$ips = parse_ini_file("/boot/config/network.cfg") ?: array();
if (array_key_exists(('IPADDR'), $ips)) {
    $route_table = TailscaleHelpers::run_command("ip route list table 52", false, false);

    $ipaddr = is_array($ips['IPADDR']) ? $ips['IPADDR'] : array($ips['IPADDR']);

    foreach ($ips['IPADDR'] as $ip) {
        foreach ($route_table as $route) {
            $net = explode(' ', $route)[0];
            if (TailscaleHelpers::ip4_in_network($ip, $net)) {
                TailscaleHelpers::logmsg("Detected local IP {$ip} in Tailscale route {$net}, removing");
                TailscaleHelpers::run_command("ip route del '{$net}' dev tailscale1 table 52");
            }
        }
    }
}
