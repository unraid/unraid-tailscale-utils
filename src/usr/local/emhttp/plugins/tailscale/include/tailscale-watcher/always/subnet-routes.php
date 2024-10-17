<?php

$ips = parse_ini_file("/boot/config/network.cfg") ?: array();
if (array_key_exists(('IPADDR'), $ips)) {
    $route_table = Tailscale\Helpers::run_command("ip route list table 52", false, false);

    $ipaddr = is_array($ips['IPADDR']) ? $ips['IPADDR'] : array($ips['IPADDR']);

    foreach ($ips['IPADDR'] as $ip) {
        foreach ($route_table as $route) {
            $net = explode(' ', $route)[0];
            if (Tailscale\Helpers::ip4_in_network($ip, $net)) {
                Tailscale\Helpers::logmsg("Detected local IP {$ip} in Tailscale route {$net}, removing");
                Tailscale\Helpers::run_command("ip route del '{$net}' dev tailscale1 table 52");
            }
        }
    }
}
