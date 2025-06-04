<?php

namespace Tailscale;

use EDACerton\PluginUtils\Translator;

if ( ! defined(__NAMESPACE__ . '\PLUGIN_ROOT') || ! defined(__NAMESPACE__ . '\PLUGIN_NAME')) {
    throw new \RuntimeException("Common file not loaded.");
}

$tr = $tr ?? new Translator(PLUGIN_ROOT);

$tailscaleConfig = $tailscaleConfig ?? new Config();

if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}
?>

<script src="/webGui/javascript/jquery.tablesorter.widgets.js"></script>

<script>

function controlsDisabled(val) {
    $('#statusTable_refresh').prop('disabled', val);
    $('input.ping').prop('disabled', val);
}
function showStatus() {
  controlsDisabled(true);
  $.post('/plugins/tailscale/include/data/Status.php',{action: 'get',mullvad: $("#statusTable_mullvad").prop('checked'), shared: $("#statusTable_shared").prop('checked')},function(data){
    clearTimeout(timers.refresh);
    $("#statusTable").trigger("destroy");
    $('#statusTable').html(data.html);
    $('#statusTable').tablesorter({
      widthFixed : true,
      sortList: [[0,0]],
      sortAppend: [[0,0]],
      widgets: ['stickyHeaders','filter','zebra'],
      widgetOptions: {
        // on black and white, offset is height of #menu
        // on azure and gray, offset is height of #header
        stickyHeaders_offset: ($('#menu').height() < 50) ? $('#menu').height() : $('#header').height(),
        filter_columnFilters: true,
        filter_reset: '.reset',
        filter_liveSearch: true,

        zebra: ["normal-row","alt-row"]
      }
    });
    $('div.spinner.fixed').hide('fast');
    controlsDisabled(false);
  },"json");
}
async function pingHost(host) {
    $('div.spinner.fixed').show('fast');
    controlsDisabled(true);
    var res = await $.post('/plugins/tailscale/include/data/Status.php',{action: 'ping', host: host});
    $("#status_pingout").html("<strong>Ping response:</strong><br>" + res);
    showStatus();
}
showStatus();
</script>

<table id='statusTable' class="unraid statusTable tablesorter"><tr><td><div class="spinner"></div></td></tr></table><br>
<table>
    <tr>
        <td style="vertical-align: top">
          <input type="button" id="statusTable_refresh" value="Refresh" onclick="showStatus()">
          <button type="button" class="reset">Reset Filters</button>
          <input type="checkbox" id="statusTable_mullvad" onChange="showStatus()">Display inactive Mullvad nodes
          <input type="checkbox" id="statusTable_shared" onChange="showStatus()">Display inactive shared-in nodes
        </td>
        <td><div id="status_pingout" style="float: right;"></div></td>
    </tr>
</table>