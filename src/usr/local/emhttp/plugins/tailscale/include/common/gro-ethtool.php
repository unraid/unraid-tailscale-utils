<?php

function applyGRO(): void
{
    $ip_route = json_decode(implode(run_command('ip -j route get 8.8.8.8')), true);

    // Check if a device was returned
    if ( ! isset($ip_route[0]['dev'])) {
        logmsg("Default interface could not be detected.");
        return;
    }

    $dev = $ip_route[0]['dev'];

    $ethtool = json_decode(implode(run_command("ethtool --json -k {$dev}")), true)[0];

    if (isset($ethtool['rx-udp-gro-forwarding']) && ! $ethtool['rx-udp-gro-forwarding']['active']) {
        run_command("ethtool -K {$dev} rx-udp-gro-forwarding on");
    }

    if (isset($ethtool['rx-gro-list']) && $ethtool['rx-gro-list']['active']) {
        run_command("ethtool -K {$dev} rx-gro-list off");
    }
}
