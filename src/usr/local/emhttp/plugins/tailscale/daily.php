#!/usr/bin/php -q
<?php

namespace Tailscale;

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

require_once "{$docroot}/plugins/tailscale/include/common.php";
if ( ! defined(__NAMESPACE__ . '\PLUGIN_ROOT') || ! defined(__NAMESPACE__ . '\PLUGIN_NAME')) {
    throw new \RuntimeException("Common file not loaded.");
}
$utils = new Utils(PLUGIN_NAME);

$tailscaleConfig = $tailscaleConfig ?? new Config();

$utils->run_task('Tailscale\System::notifyOnKeyExpiration');
$utils->run_task('Tailscale\System::refreshWebGuiCert');
