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
<link type="text/css" rel="stylesheet" href="/plugins/tailscale/style.css">
<?php
if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}

$tailscaleInfo = $tailscaleInfo ?? new Info($tr);
?>
<iframe src="/plugins/tailscale/interface.php" style="width:100%; height:600px; border: none;"></iframe>