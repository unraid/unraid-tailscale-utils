<?php

namespace Tailscale;

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "{$docroot}/plugins/tailscale/include/common.php";

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();
?>
<script>
    $(function() {
        showStatus('tailscaled');
    });
</script>
<?php
if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}

$tailscaleInfo = $tailscaleInfo ?? new Info($tr);
?>
<?= Utils::formatWarning($tailscaleInfo->getTailscaleLockWarning()); ?>
<?= Utils::formatWarning($tailscaleInfo->getTailscaleNetbiosWarning()); ?>
<?= Utils::formatWarning($tailscaleInfo->getKeyExpirationWarning()); ?>

<iframe src="/plugins/tailscale/interface.php" style="width:100%; height:600px; border: none;"></iframe>