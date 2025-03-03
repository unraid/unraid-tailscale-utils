<?php

namespace Tailscale;

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
require_once "{$docroot}/plugins/tailscale/include/common.php";

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}

$tailscaleInfo = $tailscaleInfo ?? new Info($tr);
?>

<script src="/webGui/javascript/jquery.tablesorter.widgets.js"></script>

<script>

function tailscaleControlsDisabled(val) {
    $('#configTable_refresh').prop('disabled', val);
}
function showTailscaleConfig() {
  tailscaleControlsDisabled(true);
  $.post('/plugins/tailscale/include/data/Config.php',{action: 'get'},function(data){
    clearTimeout(timers.refresh);
    $("#configTable").trigger("destroy");
    $('#configTable').html(data.html);
    $('div.spinner.fixed').hide('fast');
    tailscaleControlsDisabled(false);
  },"json");
}
async function setFeature(feature, enable) {
    $('div.spinner.fixed').show('fast');
    tailscaleControlsDisabled(true);
    var res = await $.post('/plugins/tailscale/include/data/Config.php',{action: 'set-feature', feature: feature, enable: enable});
    showTailscaleConfig();
}
async function tailscaleUp() {
  $('div.spinner.fixed').show('fast');
  tailscaleControlsDisabled(true);
  var res = await $.post('/plugins/tailscale/include/data/Config.php',{action: 'up'});
  window.open(res);
  $('div.spinner.fixed').hide('fast');
  tailscaleControlsDisabled(false);
}
showTailscaleConfig();
</script>

<!-- TODO: Get these warnings with the table -->
<?= Utils::formatWarning($tailscaleInfo->getTailscaleLockWarning()); ?>
<?= Utils::formatWarning($tailscaleInfo->getTailscaleNetbiosWarning()); ?>
<?= Utils::formatWarning($tailscaleInfo->getKeyExpirationWarning()); ?>

<table id='configTable' class="unraid statusTable tablesorter"><tr><td><div class="spinner"></div></td></tr></table><br>
<table>
    <tr>
        <td style="vertical-align: top">
          <input type="button" id="configTable_refresh" value="Refresh" onclick="showTailscaleConfig()">
        </td>
    </tr>
</table>