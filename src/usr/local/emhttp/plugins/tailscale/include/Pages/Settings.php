<?php

namespace Tailscale;

$tailscaleConfig = $tailscaleConfig ?? new Config();
$tr              = $tr              ?? new Translator();

if (( ! isset($var)) || ( ! isset($display))) {
    echo("Missing required WebGUI variables");
    return;
}
?>
<style>
    .fileTree {
        background:
            <?= strstr('white,azure', $display['theme']) ? '#f2f2f2' : '#1c1c1c'; ?>
        ;
        width: 300px;
        max-height: 150px;
        overflow-y: scroll;
        overflow-x: hidden;
        position: absolute;
        z-index: 100;
        display: none
    }
    .advanced{display:none}
</style>
<link type="text/css" rel="stylesheet" href="<?= Utils::auto_v('/webGui/styles/jquery.filetree.css');?>">
<link type="text/css" rel="stylesheet" href="<?= Utils::auto_v('/webGui/styles/jquery.switchbutton.css');?>">
<span class="status vhshift"><input type="checkbox" class="advancedview"></span>
<form method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#file"
    value="tailscale/tailscale.cfg" />
<input type="hidden" name="#cleanup" value="" />
<input type="hidden" name="#command" value="/usr/local/emhttp/plugins/tailscale/restart.sh" />

<h3><?= $tr->tr("settings.system_settings"); ?></h3>

<div class="advanced">
    <dl>
        <dt><?= $tr->tr("settings.enable_tailscale"); ?></dt>
        <dd>
            <select name='ENABLE_TAILSCALE' size='1'>
                <?= Utils::make_option($tailscaleConfig->Enable, '1', $tr->tr("yes"));?>
                <?= Utils::make_option( ! $tailscaleConfig->Enable, '0', $tr->tr("no"));?>
            </select>
        </dd>
    </dl>

    <dl>
        <dt><?= $tr->tr("settings.unraid_listen"); ?></dt>
        <dd>
            <select name='INCLUDE_INTERFACE' size='1'>
                <?= Utils::make_option($tailscaleConfig->IncludeInterface, '1', $tr->tr("yes"));?>
                <?= Utils::make_option( ! $tailscaleConfig->IncludeInterface, '0', $tr->tr("no"));?>
            </select>
        </dd>
    </dl>
    <blockquote class='inline_help'><?= $tr->tr("settings.context.unraid_listen"); ?></blockquote>

    <dl>
        <dt><?= $tr->tr("settings.ip_forward"); ?></dt>
        <dd>
            <select name='SYSCTL_IP_FORWARD' size='1'>
                <?= Utils::make_option($tailscaleConfig->IPForward, '1', $tr->tr("yes"));?>
                <?= Utils::make_option( ! $tailscaleConfig->IPForward, '0', $tr->tr("no"));?>
            </select>
        </dd>
    </dl>
    <blockquote class='inline_help'><?= $tr->tr("settings.context.ip_forward"); ?></blockquote>
</div>

<dl>
    <dt><?= $tr->tr("settings.taildrop"); ?></dt>
    <dd>
        <input type="text" id="taildropdir" name="TAILDROP_DIR" autocomplete="off" spellcheck="false"
        data-pickfolders="true" data-pickfilter="HIDE_FILES_FILTER" data-pickroot="/mnt" pattern="^[^\\]*/$"
        value="<?= htmlspecialchars($tailscaleConfig->TaildropDir);?>">
    </dd>
</dl>
<blockquote class='inline_help'><?= $tr->tr("settings.context.taildrop"); ?></blockquote>

<dl>
    <dt><?= $tr->tr("settings.usage"); ?></dt>
    <dd>
        <select name='USAGE' size='1'>
            <?= Utils::make_option($tailscaleConfig->Usage, '1', $tr->tr("yes"));?>
            <?= Utils::make_option( ! $tailscaleConfig->Usage, '0', $tr->tr("no"));?>
        </select>
    </dd>
</dl>
<blockquote class='inline_help'><?= $tr->tr("settings.context.usage"); ?></blockquote>

<div class="advanced">
    <h3><?= $tr->tr("settings.services"); ?></h3>

    <dl>
        <dt><?= $tr->tr("settings.wireguard"); ?></dt>
        <dd>
            <input type="number" name="WG_PORT" class="narrow" min="0" max="65535" value="<?= $tailscaleConfig->WgPort; ?>" placeholder="0">
        </dd>
    </dl>
    <blockquote class='inline_help'><?= $tr->tr("settings.context.wireguard"); ?></blockquote>

    <h3><?= $tr->tr("settings.outbound_network"); ?></h3>
    <h4><?= $tr->tr("settings.context.outbound_network"); ?></h4>

    <dl>
        <dt><?= $tr->tr("settings.subnets"); ?></dt>
        <dd>
            <select name='ACCEPT_ROUTES' size='1'>
                <?= Utils::make_option($tailscaleConfig->AcceptRoutes == 0, '0', $tr->tr("no"));?>
                <?= Utils::make_option($tailscaleConfig->AcceptRoutes == 1, '1', $tr->tr("yes"));?>
                <?= Utils::make_option($tailscaleConfig->AcceptRoutes == 2, '2', $tr->tr("ignore"));?>
            </select>
        </dd>
    </dl>
    <blockquote class='inline_help'>
        <?= $tr->tr("settings.context.subnets"); ?>
        <?= $tr->tr("settings.context.ignore"); ?>
    </blockquote>

    <dl>
        <dt><?= $tr->tr("settings.dns"); ?></dt>
        <dd>
            <select name='ACCEPT_DNS' size='1'>
                <?= Utils::make_option($tailscaleConfig->AcceptDNS == 0, '0', $tr->tr("no"));?>
                <?= Utils::make_option($tailscaleConfig->AcceptDNS == 1, '1', $tr->tr("yes"));?>
                <?= Utils::make_option($tailscaleConfig->AcceptDNS == 2, '2', $tr->tr("ignore"));?>
            </select>
        </dd>
    </dl>
    <blockquote class='inline_help'>
        <?= $tr->tr("settings.context.dns"); ?>
        <?= $tr->tr("settings.context.ignore"); ?>
    </blockquote>
</div>

<h3><?= $tr->tr("settings.save"); ?></h3>

<dl>
    <dt><strong><?= $tr->tr("settings.context.save"); ?></strong></dt>
    <dd>
        <input type="submit" name="#apply" value="<?= $tr->tr('Apply'); ?>"><input type="button" id="DONE" value="<?= $tr->tr('Back'); ?>" onclick="done()">
    </dd>
</dl>
</form>

<h3><?= $tr->tr("settings.restart"); ?></h3>

<form method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#command" value="/usr/local/emhttp/plugins/tailscale/restart.sh" />
<dl>
    <dt><?= $tr->tr("settings.context.restart"); ?></dt>
    <dd>
        <input type="submit" value="<?= $tr->tr('Restart'); ?>">
    </dd>
</dl>
</form>

<?php if (file_exists('/usr/local/emhttp/plugins/plugin-diagnostics/download.php')) { ?>
<h3><?= $tr->tr("settings.diagnostics"); ?></h3>

<form method="GET" action="/plugins/plugin-diagnostics/download.php" target="_blank">
<input type="hidden" name="plugin" value="tailscale" />
<dl>
    <dt><?= $tr->tr("settings.context.diagnostics"); ?></dt>
    <dd>
        <input type="submit" value="<?= $tr->tr('Download'); ?> ">
    </dd>
</dl>
</form>

<?php } ?>

<div class="advanced">
<h3><?= $tr->tr("settings.erase"); ?></h3>

<form method="POST" action="/update.php" target="progressFrame">
<input type="hidden" name="#command" value="/usr/local/emhttp/plugins/tailscale/erase.sh" />
<dl>
    <dt><?= $tr->tr("settings.context.erase"); ?></dt>
    <dd>
        <input type="button" value="<?= $tr->tr('Erase'); ?>" onclick="requestErase(this)"><input id="tailscale_erase_confirm" type="submit" value="<?= $tr->tr('Confirm'); ?>" style="display: none;">
    </dd>
</dl>
</form>
</div>

<h3><?= $tr->tr("settings.donate"); ?></h3>
<dl>
    <dt><?= $tr->tr("settings.context.donate"); ?></dt>
    <dd>
        <input type="button" value="Paypal" onclick="window.open('https://paypal.me/edacerton', '_blank')"> <input type="button" value="GitHub" onclick="window.open('https://github.com/sponsors/dkaser', '_blank')">
    </dd>
</dl>

<script src="<?= Utils::auto_v('/webGui/javascript/jquery.filetree.js');?>" charset="utf-8"></script>
<script src="<?= Utils::auto_v('/webGui/javascript/jquery.switchbutton.js');?>"></script>
<script>
    function requestErase(e) {
        e.disabled = true;
        var confirmButton = document.getElementById('tailscale_erase_confirm');
        confirmButton.style.display = "inline";
    }
</script>
<script>
    $(function() {
        <?= ($var['fsState'] == 'Started') ? "$('#taildropdir').fileTreeAttach();" : ""; ?>

        if ($.cookie('tailscale_view_mode') == 'advanced') {
            $('.advanced').show();
            $('.basic').hide();
        }

        $('.advancedview').switchButton({
            labels_placement: "left",
            on_label: "<?= $tr->tr("settings.advanced"); ?>",
            off_label: "<?= $tr->tr("settings.basic"); ?>",
            checked: $.cookie('tailscale_view_mode') == 'advanced'
        });
        $('.advancedview').change(function(){
            $('.advanced').toggle('slow');
            $('.basic').toggle('slow');
            $.cookie('tailscale_view_mode', $('.advancedview').is(':checked') ? 'advanced' : 'basic', {expires:3650});
        });
    });
</script>