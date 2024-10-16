<?php

function printRow(string $title, string $value): string
{
    return "<tr><td>{$title}</td><td>{$value}</td></tr>" . PHP_EOL;
}

function printDash(string $title, string $value): string
{
    return "<tr><td><span class='w26'>{$title}</span>{$value}</td></tr>" . PHP_EOL;
}

function getStatusInfo(object $status, object $prefs, object $lock): string
{
    $tsVersion     = isset($status->Version) ? $status->Version : _tr("unknown");
    $keyExpiration = isset($status->Self->KeyExpiry) ? $status->Self->KeyExpiry : _tr("disabled");
    $online        = isset($status->Self->Online) ? ($status->Self->Online ? _tr("yes") : _tr("no")) : _tr("unknown");
    $inNetMap      = isset($status->Self->InNetworkMap) ? ($status->Self->InNetworkMap ? _tr("yes") : _tr("no")) : _tr("unknown");
    $tags          = isset($status->Self->Tags) ? implode("<br />", $status->Self->Tags) : "";
    $loggedIn      = isset($prefs->LoggedOut) ? ($prefs->LoggedOut ? _tr("no") : _tr("yes")) : _tr("unknown");
    $tsHealth      = isset($status->Health) ? implode("<br />", $status->Health) : "";
    $lockEnabled   = getTailscaleLockEnabled($lock) ? _tr("yes") : _tr("no");

    $lockTranslate = _tr("tailscale_lock");

    $output = "";
    $output .= printRow(_tr("info.version"), $tsVersion);
    $output .= printRow(_tr("info.health"), $tsHealth);
    $output .= printRow(_tr("info.login"), $loggedIn);
    $output .= printRow(_tr("info.netmap"), $inNetMap);
    $output .= printRow(_tr("info.online"), $online);
    $output .= printRow(_tr("info.key_expire"), $keyExpiration);
    $output .= printRow(_tr("info.tags"), $tags);
    $output .= printRow("{$lockTranslate}: " . _tr("enabled"), $lockEnabled);

    if (getTailscaleLockEnabled($lock)) {
        $lockSigned  = getTailscaleLockSigned($lock) ? _tr("yes") : _tr("no");
        $lockSigning = getTailscaleLockSigning($lock) ? _tr("yes") : _tr("no");
        $pubKey      = getTailscaleLockPubkey($lock);
        $nodeKey     = getTailscaleLockNodekey($lock);

        $output .= printRow("{$lockTranslate}: " . _tr("info.lock.signed"), $lockSigned);
        $output .= printRow("{$lockTranslate}: " . _tr("info.lock.signing"), $lockSigning);
        $output .= printRow("{$lockTranslate}: " . _tr("info.lock.node_key"), $nodeKey);
        $output .= printRow("{$lockTranslate}: " . _tr("info.lock.public_key"), $pubKey);
    }

    return $output;
}

function getConnectionInfo(object $status, object $prefs): string
{
    $hostName         = isset($status->Self->HostName) ? $status->Self->HostName : _tr("unknown");
    $dnsName          = isset($status->Self->DNSName) ? $status->Self->DNSName : _tr("unknown");
    $tailscaleIPs     = isset($status->TailscaleIPs) ? implode("<br />", $status->TailscaleIPs) : _tr("unknown");
    $magicDNSSuffix   = isset($status->MagicDNSSuffix) ? $status->MagicDNSSuffix : _tr("unknown");
    $advertisedRoutes = isset($prefs->AdvertiseRoutes) ? implode("<br />", $prefs->AdvertiseRoutes) : _tr("none");
    $acceptRoutes     = isset($prefs->RouteAll) ? ($prefs->RouteAll ? _tr("yes") : _tr("no")) : _tr("unknown");
    $acceptDNS        = isset($prefs->CorpDNS) ? ($prefs->CorpDNS ? _tr("yes") : _tr("no")) : _tr("unknown");

    $output = "";
    $output .= printRow(_tr("info.hostname"), $hostName);
    $output .= printRow(_tr("info.dns"), $dnsName);
    $output .= printRow(_tr("info.ip"), $tailscaleIPs);
    $output .= printRow(_tr("info.magicdns"), $magicDNSSuffix);
    $output .= printRow(_tr("info.routes"), $advertisedRoutes);
    $output .= printRow(_tr("info.accept_routes"), $acceptRoutes);
    $output .= printRow(_tr("info.accept_dns"), $acceptDNS);

    return $output;
}

function getDashboardInfo(object $status): string
{
    $hostName     = isset($status->Self->HostName) ? $status->Self->HostName : _tr("Unknown");
    $dnsName      = isset($status->Self->DNSName) ? $status->Self->DNSName : _tr("Unknown");
    $tailscaleIPs = isset($status->TailscaleIPs) ? implode("<br /><span class='w26'>&nbsp;</span>", $status->TailscaleIPs) : _tr("unknown");
    $online       = isset($status->Self->Online) ? ($status->Self->Online ? _tr("yes") : _tr("no")) : _tr("unknown");

    $output = "";
    $output .= printDash(_tr("info.online"), $online);
    $output .= printDash(_tr("info.hostname"), $hostName);
    $output .= printDash(_tr("info.dns"), $dnsName);
    $output .= printDash(_tr("info.ip"), $tailscaleIPs);

    return $output;
}
