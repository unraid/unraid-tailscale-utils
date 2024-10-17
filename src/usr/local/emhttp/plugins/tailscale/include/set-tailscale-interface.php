<?php

$include_array      = array();
$exclude_interfaces = "";
$write_file         = true;
$network_extra_file = '/boot/config/network-extra.cfg';
$ifname             = 'tailscale1';

$tailscale_config = $tailscale_config ?? Tailscale\Helpers::getPluginConfig();

if (file_exists($network_extra_file)) {
    $netExtra = parse_ini_file($network_extra_file);
    if ($netExtra['include_interfaces'] ?? false) {
        $include_array = explode(' ', $netExtra['include_interfaces']);
    }
    if ($netExtra['exclude_interfaces'] ?? false) {
        $exclude_interfaces = $netExtra['exclude_interfaces'];
    }
    $write_file = false;
}

$in_array = in_array($ifname, $include_array);

if ($in_array != $tailscale_config["INCLUDE_INTERFACE"]) {
    if ($tailscale_config["INCLUDE_INTERFACE"]) {
        $include_array[] = $ifname;
        Tailscale\Helpers::logmsg("{$ifname} added to include_interfaces");
    } else {
        $include_array = array_diff($include_array, [$ifname]);
        Tailscale\Helpers::logmsg("{$ifname} removed from include_interfaces");
    }
    $write_file = true;
}

if ($write_file) {
    $include_interfaces = implode(' ', $include_array);

    $file = <<<END
        include_interfaces="{$include_interfaces}"
        exclude_interfaces="{$exclude_interfaces}"

        END;

    file_put_contents($network_extra_file, $file);
    Tailscale\Helpers::logmsg("Updated network-extra.cfg");
}
