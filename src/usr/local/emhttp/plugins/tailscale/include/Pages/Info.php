<?php

namespace Tailscale;

use EDACerton\PluginUtils\Translator;

if ( ! defined(__NAMESPACE__ . '\PLUGIN_ROOT') || ! defined(__NAMESPACE__ . '\PLUGIN_NAME')) {
    throw new \RuntimeException("Common file not loaded.");
}

$tr = $tr ?? new Translator(PLUGIN_ROOT);

$tailscaleConfig = $tailscaleConfig ?? new Config();

if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}

$tailscaleInfo       = $tailscaleInfo ?? new Info($tr);
$tailscaleStatusInfo = $tailscaleInfo->getStatusInfo();
?>
<table class="unraid t1">
    <thead>
        <tr>
            <td><?= $tr->tr('status'); ?></td>
            <td>&nbsp;</td>
        </tr>
    </thead>
    <tbody>
        <?php
            $lockTranslate = $tr->tr("tailscale_lock");

echo Utils::printRow($tr->tr("info.version"), $tailscaleStatusInfo->TsVersion);
echo Utils::printRow($tr->tr("info.health"), $tailscaleStatusInfo->TsHealth);
echo Utils::printRow($tr->tr("info.login"), $tailscaleStatusInfo->LoggedIn);
echo Utils::printRow($tr->tr("info.netmap"), $tailscaleStatusInfo->InNetMap);
echo Utils::printRow($tr->tr("info.online"), $tailscaleStatusInfo->Online);
echo Utils::printRow($tr->tr("info.key_expire"), $tailscaleStatusInfo->KeyExpiration);
echo Utils::printRow($tr->tr("info.tags"), $tailscaleStatusInfo->Tags);
echo Utils::printRow("{$lockTranslate}: " . $tr->tr("enabled"), $tailscaleStatusInfo->LockEnabled);
echo Utils::printRow($tr->tr("info.connected_via"), $tailscaleInfo->connectedViaTS() ? $tr->tr("yes") : $tr->tr("no"));

if ($tailscaleStatusInfo->LockInfo != null) {
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.signed"), $tailscaleStatusInfo->LockInfo->LockSigned);
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.signing"), $tailscaleStatusInfo->LockInfo->LockSigning);
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.node_key"), $tailscaleStatusInfo->LockInfo->NodeKey);
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.public_key"), $tailscaleStatusInfo->LockInfo->PubKey);
}
?>
    </tbody>
</table>
