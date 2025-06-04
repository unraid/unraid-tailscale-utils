<?php

namespace Tailscale;

use EDACerton\PluginUtils\Translator;

if ( ! defined(__NAMESPACE__ . '\PLUGIN_ROOT') || ! defined(__NAMESPACE__ . '\PLUGIN_NAME')) {
    throw new \RuntimeException("Common file not loaded.");
}

$tr = $tr ?? new Translator(PLUGIN_ROOT);
?>
<h3><?= $tr->tr("tailscale_lock"); ?></h3>

<p>
    <?= $tr->tr('lock.signed_node'); ?>
</p>

<p>
<?= $tr->tr('lock.make_signing'); ?>
</p>

<?php
    if ( ! isset($tailscaleInfo)) {
        echo("Tailscale info not defined");
        return;
    }
?>

<pre><?= $tailscaleInfo->getTailscaleLockPubkey(); ?></pre>
