<?php

function getTailscaleNetbiosWarning($var)
{
    if (($var['USE_NETBIOS'] == "yes") && ($var['shareSMBEnabled'] != "no")) {
        return "<span class='warn' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . _tr("warnings.netbios") . "</span>";
    }
    return "";
}
