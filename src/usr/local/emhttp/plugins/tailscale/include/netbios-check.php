<?php

/**
 * @param array<mixed> $var
 */
function getTailscaleNetbiosWarning(array $var, Translator $tr): string
{
    if (($var['USE_NETBIOS'] == "yes") && ($var['shareSMBEnabled'] != "no")) {
        return "<span class='warn' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . $tr->tr("warnings.netbios") . "</span>";
    }
    return "";
}
