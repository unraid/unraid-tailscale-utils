<h3><?= _tr("tailscale_lock"); ?></h3>

<p>
    <?= _tr('lock.signed_node'); ?>
</p>

<p>
<?= _tr('lock.make_signing'); ?>
</p>

<?php
    if(!isset($tailscale_output)) {
        echo("Tailscale output not defined");
        return;
    }
?>

<pre><?= $tailscale_output['lock_pubkey']; ?></pre>
