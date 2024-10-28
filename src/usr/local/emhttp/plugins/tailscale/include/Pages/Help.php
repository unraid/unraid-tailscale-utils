<?php

namespace Tailscale;

$tr = $tr ?? new Translator();
function help_block(string $section, Translator $tr, string $header = "h3"): string
{
    $title = $tr->tr("settings.{$section}");
    $body  = $tr->tr("help.{$section}");
    return <<<END
        <{$header}>{$title}</{$header}>
        <p>{$body}</p>
        END;
}
?>

<h2><?= $tr->tr("help.initial"); ?></h2>
<p><?= $tr->tr("help.key_expiration"); ?></p>

<h2><?= $tr->tr("settings.system_settings"); ?></h2>
<?= help_block('unraid_listen', $tr); ?>
<?= help_block('ip_forward', $tr); ?>
<?= help_block('taildrop', $tr); ?>

<h3><?= $tr->tr("settings.usage"); ?></h3>
<?= $tr->tr("settings.context.usage"); ?>

<?= help_block('outbound_network', $tr, "h2"); ?>
<?= help_block('subnets', $tr); ?>
<?= help_block('dns', $tr); ?>

<h2><?= $tr->tr("help.support"); ?></h2>

<?= $tr->tr("help.support_forums"); ?>

<p><a href="https://forums.unraid.net/topic/136889-plugin-tailscale">https://forums.unraid.net/topic/136889-plugin-tailscale/</a></p>

<p><strong><?= $tr->tr("help.support_advanced"); ?></strong></p>