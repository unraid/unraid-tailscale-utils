<?php
$tr = $tr ?? new Translator();
?>
<h3><?= $tr->tr("tailscale_lock"); ?></h3>
<h4><?= $tr->tr("lock.sign"); ?></h4>
<p>
    <?= $tr->tr("lock.signing_node"); ?>
</p>
<p>
<?= $tr->tr("lock.signing_instructions"); ?>
</p>
<?php
    if ( ! isset($tailscale_output)) {
        echo("Tailscale output not defined");
        return;
    }
?>
<form markdown="1" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#command" value="/usr/local/emhttp/plugins/tailscale/approve-nodes.php" />
<table style="margin-top: 5px;">
<?php
foreach ($tailscale_output['lock_pending'] as $lockHost => $lockKey) {
    echo "<tr><td><input type='checkbox' name='#arg[]' value='{$lockKey}' /></td><td>{$lockHost}<br />{$lockKey}</td></tr>";
}
?>
</table>

<input type="submit" name="#apply" value="<?= $tr->tr('Sign'); ?>">
</form>