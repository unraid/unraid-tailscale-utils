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
}
function showStatus() {
  controlsDisabled(true);
  $.post('/plugins/tailscale/include/data/Status.php',{action: 'get'},function(data){
    clearTimeout(timers.refresh);
    $("#t1").trigger("destroy");
    $('#t1').html(data.html);
    $('#t1').tablesorter({
      sortList: [[0,0]],
      sortAppend: [[0,0]],
      widgets: ['stickyHeaders','filter','zebra'],
      widgetOptions: {
        // on black and white, offset is height of #menu
        // on azure and gray, offset is height of #header
        stickyHeaders_offset: ($('#menu').height() < 50) ? $('#menu').height() : $('#header').height(),
        filter_columnFilters: false,
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

<table id='t1' class="unraid t1 tablesorter"><tr><td><div class="spinner"></div></td></tr></table><br>
<table>
    <tr>
        <td style="vertical-align: top"><input type="button" id="refresh" value="Refresh" onclick="showStatus()"></td>
        <td><div id="pingout" style="float: right;"></div></td>
    </tr>
</table>