#!/usr/bin/php -q
<?php

require_once "include/common.php";

foreach (array_slice($argv, 1) as $key => $value) {
    Tailscale\Utils::logmsg("Tailnet lock: signing {$value}");
    exec("tailscale lock sign {$value}");
}
