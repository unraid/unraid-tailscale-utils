<?php

namespace Tailscale;

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}

if ( ! defined(__NAMESPACE__ . "\PLUGIN_ROOT")) {
    throw new \RuntimeException("PLUGIN_ROOT not defined");
}

$signingNode = false;

$tailscaleInfo = $tailscaleInfo ?? new Info($tr);

switch (true) {
    case $tailscaleInfo->getTailscaleLockSigning():
        require PLUGIN_ROOT . "/include/tailscale-lock/signing.php";
        break;
    case $tailscaleInfo->getTailscaleLockSigned():
        require PLUGIN_ROOT . "/include/tailscale-lock/signed.php";
        break;
    case $tailscaleInfo->getTailscaleLockEnabled():
        require PLUGIN_ROOT . "/include/tailscale-lock/locked.php";
        break;
    default:
        require PLUGIN_ROOT . "/include/tailscale-lock/disabled.php";
        break;
}
