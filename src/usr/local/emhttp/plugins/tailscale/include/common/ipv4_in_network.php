<?php

function ip4_in_network(string $ip, string $network) : bool
{
    if (strpos($network, '/') === false) {
        return false;
    }

    list($subnet, $mask) = explode('/', $network, 2);
    $ip_bin_string       = sprintf("%032b", ip2long($ip));
    $net_bin_string      = sprintf("%032b", ip2long($subnet));

    return (substr_compare($ip_bin_string, $net_bin_string, 0, intval($mask)) === 0);
}
