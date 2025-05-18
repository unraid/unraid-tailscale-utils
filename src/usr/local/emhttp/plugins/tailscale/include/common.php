<?php

namespace Tailscale;

define(__NAMESPACE__ . "\PLUGIN_ROOT", dirname(dirname(__FILE__)));

foreach (glob(PLUGIN_ROOT . "/include/" . __NAMESPACE__ . "/*.php") ?: array() as $file) {
    try {
        require $file;
    } catch (\Throwable $e) {
        Utils::logmsg("Caught exception in {$file} : " . $e->getMessage());
    }
}

Utils::setPHPDebug();
