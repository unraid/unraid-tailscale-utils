<?php
$tr = $tr ?? new Tailscale\Translator();
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