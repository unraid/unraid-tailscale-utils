#!/usr/bin/php -q
<?php

namespace Tailscale;

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

require_once "{$docroot}/plugins/tailscale/include/common.php";
$tailscaleConfig = $tailscaleConfig ?? new Config();

Utils::run_task('Tailscale\System::notifyOnKeyExpiration');
Utils::run_task('Tailscale\System::refreshWebGuiCert');
