#!/usr/bin/php -q
<?php

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "{$docroot}/plugins/tailscale/include/common.php";

$timer   = 15;
$need_ip = true;

$tsName = '';

Tailscale\Helpers::logmsg("Starting tailscale-watcher");

// @phpstan-ignore while.alwaysTrue
while (true) {
    unset($tailscale_ipv4);

    $interfaces = net_get_interfaces();

    if (isset($interfaces["tailscale1"]["unicast"])) {
        foreach ($interfaces["tailscale1"]["unicast"] as $interface) {
            if (isset($interface["address"])) {
                if ($interface["family"] == 2) {
                    $tailscale_ipv4 = $interface["address"];
                    $timer          = 60;
                }
            }
        }
    }

    if (isset($tailscale_ipv4)) {
        if ($need_ip) {
            Tailscale\Helpers::logmsg("Tailscale IP detected, applying configuration");
            $need_ip = false;

            $status = Tailscale\Info::getStatus();
            $tsName = $status->Self->DNSName;

            foreach (glob("{$docroot}/plugins/tailscale/include/tailscale-watcher/on-ip/*.php") ?: array() as $file) {
                try {
                    require $file;
                } catch (Throwable $e) {
                    Tailscale\Helpers::logmsg("Caught exception in {$file} : " . $e->getMessage());
                }
            }
        }

        foreach (glob("{$docroot}/plugins/tailscale/include/tailscale-watcher/always/*.php") ?: array() as $file) {
            try {
                require $file;
            } catch (Throwable $e) {
                Tailscale\Helpers::logmsg("Caught exception in {$file} : " . $e->getMessage());
            }
        }

        // Watch for changes to the DNS name (e.g., if someone changes the tailnet name or the Tailscale name of the server via the admin console)
        // If a change happens, refresh the Tailscale WebGUI certificate
        $status    = Tailscale\Info::getStatus();
        $newTsName = $status->Self->DNSName;

        if ($newTsName != $tsName) {
            Tailscale\Helpers::logmsg("Detected DNS name change");
            $tsName = $newTsName;

            foreach (glob("{$docroot}/plugins/tailscale/include/tailscale-watcher/on-name-change/*.php") ?: array() as $file) {
                try {
                    require $file;
                } catch (Throwable $e) {
                    Tailscale\Helpers::logmsg("Caught exception in {$file} : " . $e->getMessage());
                }
            }
        }
    } else {
        Tailscale\Helpers::logmsg("Waiting for Tailscale IP");
    }

    sleep($timer);
}
