<?php
$tr = $tr ?? new Tailscale\Translator();
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
