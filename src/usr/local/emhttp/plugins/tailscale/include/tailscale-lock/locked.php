<?php
$tr = $tr ?? new Translator();
?>
<h3><?= $tr->tr("tailscale_lock"); ?></h3>

<p>
<?= $tr->tr('lock.unsigned'); ?>.
</p>

<p><?= $tr->tr('lock.unsigned_instructions'); ?></p>

<?php
    if ( ! isset($tailscale_output)) {
        echo("Tailscale output not defined");
        return;
    }
?>

<pre><?= $tailscale_output['lock_nodekey']; ?></pre>