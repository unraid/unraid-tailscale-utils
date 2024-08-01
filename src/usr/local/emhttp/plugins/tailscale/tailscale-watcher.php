#!/usr/bin/php -q
<?php

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "{$docroot}/plugins/tailscale/include/common.php";

$timer   = 15;
$need_ip = true;

logmsg("Starting tailscale-watcher");

while (true) {
    sleep($timer);

    unset($tailscale_ipv4);

    $interfaces  = net_get_interfaces();

    if (isset($interfaces["tailscale1"]["unicast"])) {
        foreach ($interfaces["tailscale1"]["unicast"] as $interface) {
            if (isset($interface["address"])) {
                if ($interface["family"] == 2) {
                    $tailscale_ipv4 = $interface["address"];
                    $timer = 60;
                }
            }
        }
    }

    if (isset($tailscale_ipv4)) {
        if ($need_ip) {
            logmsg("Tailscale IP detected, applying configuration");
            $need_ip = false;

            foreach (glob("{$docroot}/plugins/tailscale/include/tailscale-watcher/*.php") as $file) {
                try {
                    require $file;
                } catch (Exception $e) {
                    logmsg("Caught exception in {$file} : " . $e->getMessage());
                }
            }
        }

        foreach (glob("{$docroot}/plugins/tailscale/include/tailscale-watcher/always/*.php") as $file) {
            try {
                require $file;
            } catch (Exception $e) {
                logmsg("Caught exception in {$file} : " . $e->getMessage());
            }
        }
    } else {
        logmsg("Waiting for Tailscale IP");
    }
}
