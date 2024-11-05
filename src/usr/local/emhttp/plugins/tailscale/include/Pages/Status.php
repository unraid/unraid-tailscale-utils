<?php

namespace Tailscale;

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

if ( ! $tailscaleConfig->Enable) {
    echo($tr->tr("tailscale_disabled"));
    return;
}
?>

<script src="/webGui/javascript/jquery.tablesorter.widgets.js"></script>

<script>

function controlsDisabled(val) {
    $('#refresh').prop('disabled', val);
    $('input.ping').prop('disabled', val);
    $('#peersearch').prop('disabled', val);
}
function showStatus() {
  controlsDisabled(true);
  $.post('/plugins/tailscale/include/data/Status.php',{action: 'get',mullvad: $("#mullvad").prop('checked'), shared: $("#shared").prop('checked')},function(data){
    clearTimeout(timers.refresh);
    $("#t1").trigger("destroy");
    $('#t1').html(data.html);
    $('#t1').tablesorter({
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
    $("#pingout").html("<strong>Ping response:</strong><br>" + res);
    showStatus();
}
showStatus();
</script>
<style type="text/css">
  /* rows hidden by filtering (needed for child rows) */
.tablesorter .filtered {
    display: none;
}
.tablesorter-filter.disabled {
  display: none;
}
</style>

<table id='t1' class="unraid t1 tablesorter"><tr><td><div class="spinner"></div></td></tr></table><br>
<table>
    <tr>
        <td style="vertical-align: top">
          <input type="button" id="refresh" value="Refresh" onclick="showStatus()">
          <button type="button" class="reset">Reset Filters</button>
          <input type="checkbox" id="mullvad" onChange="showStatus()">Display inactive Mullvad nodes
          <input type="checkbox" id="shared" onChange="showStatus()">Display inactive shared-in nodes
        </td>
        <td><div id="pingout" style="float: right;"></div></td>
    </tr>
</table>