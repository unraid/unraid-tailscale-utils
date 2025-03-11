#!/usr/bin/php -q
<?php

namespace Tailscale;

require_once "include/common.php";

$localAPI = new LocalAPI();

foreach (array_slice($argv, 1) as $key => $value) {
    Utils::logmsg("Tailnet lock: signing {$value}");
    $localAPI->postTkaSign($value);
}
