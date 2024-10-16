<?php

function getTailscaleStatus(): object
{
    exec("tailscale status --json", $out_status);
    return json_decode(implode($out_status));
}

function getTailscalePrefs(): object
{
    exec("tailscale debug prefs", $out_prefs);
    return json_decode(implode($out_prefs));
}

function getTailscaleLock(): object
{
    exec("tailscale lock status -json=true", $out_status);
    return json_decode(implode($out_status));
}
