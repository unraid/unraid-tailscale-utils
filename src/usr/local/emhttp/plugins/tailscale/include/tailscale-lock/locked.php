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
<?= $tr->tr('lock.unsigned'); ?>.
</p>

<p><?= $tr->tr('lock.unsigned_instructions'); ?></p>

<?php
    if ( ! isset($tailscaleInfo)) {
        echo("Tailscale info not defined");
        return;
    }
?>

<pre><?= $tailscaleInfo->getTailscaleLockNodekey(); ?></pre>