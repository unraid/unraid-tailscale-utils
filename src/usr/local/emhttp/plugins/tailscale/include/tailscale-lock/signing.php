<?php
$tr = $tr ?? new Tailscale\Translator();
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
    if ( ! isset($tailscaleInfo)) {
        echo("Tailscale info not defined");
        return;
    }
?>

<form markdown="1" method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#command" value="/usr/local/emhttp/plugins/tailscale/approve-nodes.php" />
<table class="unraid t2 tablesorter" id="t2">
    <thead>
        <tr>
            <th class="filter-false">&nbsp;</th>
            <th>Name</th>
            <th class="filter-false">Key</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ($tailscaleInfo->getTailscaleLockPending() as $lockHost => $lockKey) {
            echo "<tr><td><input type='checkbox' name='#arg[]' value='{$lockKey}' /></td><td>{$lockHost}</td><td>{$lockKey}</td></tr>";
        }
?>
    </tbody>
</table>

<input type="submit" name="#apply" value="<?= $tr->tr('Sign'); ?>">
</form>
<script>
    function showLocks() {
    $('#t2').tablesorter({
      widthFixed : true,
      sortList: [[0,0]],
      sortAppend: [[0,0]],
      widgets: ['stickyHeaders','filter','zebra'],
      widgetOptions: {
        // on black and white, offset is height of #menu
        // on azure and gray, offset is height of #header
        stickyHeaders_offset: ($('#menu').height() < 50) ? $('#menu').height() : $('#header').height(),
        filter_columnFilters: true,
        filter_liveSearch: true,

        zebra: ["normal-row","alt-row"]
      }
    });
}
showLocks();
</script>