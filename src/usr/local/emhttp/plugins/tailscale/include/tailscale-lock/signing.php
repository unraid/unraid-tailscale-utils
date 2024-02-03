<h3><?= _tr("tailscale_lock"); ?></h3>
<h4><?= _tr("lock.sign"); ?></h4>
<p>
    <?= _tr("lock.signing_node"); ?>
</p>
<p>
<?= _tr("lock.signing_instructions"); ?>
</p>
<form markdown="1" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#command" value="/usr/local/emhttp/plugins/tailscale/approve-nodes.php" />
<table style="margin-top: 5px;">
<?php
foreach ($tailscale_output['lock_pending'] as $lockHost => $lockKey) {
    echo "<tr><td><input type='checkbox' name='#arg[]' value='{$lockKey}' /></td><td>{$lockHost}<br />{$lockKey}</td></tr>";
}
?>
</table>

<input type="submit" name="#apply" value="<?= _tr('Sign'); ?>">
</form>