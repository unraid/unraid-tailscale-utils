<?php

namespace Tailscale;

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "{$docroot}/plugins/tailscale/include/common.php";

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

$tailscale_dashboard = "<tr><td>" . $tr->tr("tailscale_disabled") . "</td></tr>";

if ($tailscaleConfig->Enable) {
    $tailscaleInfo     = $tailscaleInfo ?? new Info($tr);
    $tailscaleDashInfo = $tailscaleInfo->getDashboardInfo();

    $tailscale_dashboard = Utils::printDash($tr->tr("info.online"), $tailscaleDashInfo->Online);
    $tailscale_dashboard .= Utils::printDash($tr->tr("info.hostname"), $tailscaleDashInfo->HostName);
    $tailscale_dashboard .= Utils::printDash($tr->tr("info.dns"), $tailscaleDashInfo->DNSName);
    $tailscale_dashboard .= Utils::printDash($tr->tr("info.ip"), implode("<br /><span class='w26'>&nbsp;</span>", $tailscaleDashInfo->TailscaleIPs));
}

echo <<<EOT
    <tbody title="Tailscale">
    <tr><td>
    <img style="margin-right: 8px; width: 32px; height: 32px" src="/plugins/tailscale/tailscale.png" /><div class='section'>Tailscale<br><span id='tailscale-temp'></span><br></div>
    <a href="/Settings/Tailscale" title="_(Settings)_"><i class="fa fa-fw fa-cog control"></i></a>
    </td></tr>
    {$tailscale_dashboard}
    </tbody>
    EOT;
