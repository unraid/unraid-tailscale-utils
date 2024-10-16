<h3><?= _tr("tailscale_lock"); ?></h3>

<p>
<?= _tr('lock.unsigned'); ?>.
</p>

<p><?= _tr('lock.unsigned_instructions'); ?></p>

<?php
    if(!isset($tailscale_output)) {
        echo("Tailscale output not defined");
        return;
    }
?>

<pre><?= $tailscale_output['lock_nodekey']; ?></pre>