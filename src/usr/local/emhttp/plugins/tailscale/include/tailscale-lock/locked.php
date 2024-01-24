<h3><?= _tr("tailscale_lock") ?></h3>

<p>
<?= _tr('lock.unsigned') ?>.
</p>

<p><?= _tr('lock.unsigned_instructions') ?></p>

<pre><?= $tailscale_output['lock_nodekey']; ?></pre>