<?php

namespace Tailscale;

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}

$tailscaleInfo = $tailscaleInfo ?? new Info($tr);
?>

<table id="t1" class="unraid t1">
    <thead>
        <tr>
            <td><?= $tr->tr('info.dns'); ?></td>
            <td><?= $tr->tr('info.ip'); ?></td>
            <td><?= $tr->tr('status_page.login_name'); ?></td>
            <td><?= $tr->tr('status'); ?></td>
            <td><?= $tr->tr('status_page.exit_node'); ?></td>
            <td><?= $tr->tr('status_page.connection_type'); ?></td>
            <td><?= $tr->tr('status_page.connection_addr'); ?></td>
            <td><?= $tr->tr('status_page.tx_bytes'); ?></td>
            <td><?= $tr->tr('status_page.rx_bytes'); ?></td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tailscaleInfo->getPeerStatus() as $peer) { ?>
            <tr>
                <td><?= $peer->SharedUser ? $tr->tr('status_page.shared') : $peer->Name; ?></td>
                <td><?= $peer->IP; ?></td>
                <td><?= $peer->LoginName; ?></td>
                <td><?= $peer->Online ? ($peer->Active ? $tr->tr('status_page.active') : $tr->tr('status_page.idle')) : $tr->tr('status_page.offline'); ?></td>
                <td><?= $peer->ExitNodeActive ? $tr->tr('status_page.exit_active') : ($peer->ExitNodeAvailable ? $tr->tr('status_page.exit_available') : ""); ?></td>
                <td><?= $peer->Active ? ($peer->Relayed ? $tr->tr('status_page.relay') : $tr->tr('status_page.direct')) : ""; ?></td>
                <td><?= $peer->Active ? $peer->Address : ""; ?></td>
                <td><?= $peer->Traffic ? $peer->TxBytes : ""; ?></td>
                <td><?= $peer->Traffic ? $peer->RxBytes : ""; ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>