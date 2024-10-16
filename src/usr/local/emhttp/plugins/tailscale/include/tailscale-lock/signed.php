<?php
$tr = $tr ?? new Translator();
?>
<h3><?= $tr->tr("tailscale_lock"); ?></h3>

<p>
    <?= $tr->tr('lock.signed_node'); ?>
</p>

<p>
<?= $tr->tr('lock.make_signing'); ?>
</p>

<?php
    if ( ! isset($tailscale_output)) {
        echo("Tailscale output not defined");
        return;
    }
?>

<pre><?= $tailscale_output['lock_pubkey']; ?></pre>
