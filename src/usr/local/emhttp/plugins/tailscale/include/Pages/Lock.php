<?php

namespace Tailscale;

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}

$signingNode = false;

$tailscaleInfo = $tailscaleInfo ?? new Info($tr);

switch (true) {
    case $tailscaleInfo->getTailscaleLockSigning():
        require "{$docroot}/plugins/tailscale/include/tailscale-lock/signing.php";
        break;
    case $tailscaleInfo->getTailscaleLockSigned():
        require "{$docroot}/plugins/tailscale/include/tailscale-lock/signed.php";
        break;
    case $tailscaleInfo->getTailscaleLockEnabled():
        require "{$docroot}/plugins/tailscale/include/tailscale-lock/locked.php";
        break;
    default:
        require "{$docroot}/plugins/tailscale/include/tailscale-lock/disabled.php";
        break;
}
