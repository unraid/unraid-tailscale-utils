<?php

/**
 * @param array<mixed> $var
 */
function getTailscaleNetbiosWarning(array $var) : string
{
    if (($var['USE_NETBIOS'] == "yes") && ($var['shareSMBEnabled'] != "no")) {
        return "<span class='warn' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . _tr("warnings.netbios") . "</span>";
    }
    return "";
}
