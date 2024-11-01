<?php

namespace Tailscale;

foreach (glob("/usr/local/emhttp/plugins/tailscale/include/Tailscale/*.php") ?: array() as $file) {
    try {
        require $file;
    } catch (\Throwable $e) {
        Utils::logmsg("Caught exception in {$file} : " . $e->getMessage());
    }
}

Utils::setPHPDebug();
