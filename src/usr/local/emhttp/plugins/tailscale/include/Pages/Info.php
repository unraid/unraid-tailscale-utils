<?php

namespace Tailscale;

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

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

if ($tailscaleStatusInfo->LockInfo != null) {
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.signed"), $tailscaleStatusInfo->LockInfo->LockSigned);
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.signing"), $tailscaleStatusInfo->LockInfo->LockSigning);
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.node_key"), $tailscaleStatusInfo->LockInfo->NodeKey);
    echo Utils::printRow("{$lockTranslate}: " . $tr->tr("info.lock.public_key"), $tailscaleStatusInfo->LockInfo->PubKey);
}
?>
    </tbody>
</table>
