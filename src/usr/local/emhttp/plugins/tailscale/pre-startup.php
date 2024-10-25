#!/usr/bin/php -q
<?php

namespace Tailscale;

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "{$docroot}/plugins/tailscale/include/common.php";

$tailscaleConfig = $tailscaleConfig ?? new Config();

Utils::run_task('Tailscale\System::createTailscaledParamsFile', array($tailscaleConfig));
Utils::run_task('Tailscale\System::applyGRO');
Utils::run_task('Tailscale\System::setExtraInterface', array($tailscaleConfig));
Utils::run_task('Tailscale\System::enableIPForwarding', array($tailscaleConfig));
Utils::run_task('Tailscale\System::patchNginx');
Utils::run_task('Tailscale\System::patchSSH', array($tailscaleConfig));

if ($tailscaleConfig->Enable) {
    Utils::run_command('/etc/rc.d/rc.tailscale restart > /dev/null &');
} else {
    Utils::run_command('/etc/rc.d/rc.tailscale stop');
    Utils::run_command(System::RESTART_COMMAND);
}
