<?php

function getTailscaleLockEnabled(object $lock) : bool
{
    return $lock->Enabled;
}

function getTailscaleLockSigned(object $lock) : bool
{
    if ( ! getTailscaleLockEnabled($lock)) {
        return false;
    }

    return $lock->NodeKeySigned;
}

function getTailscaleLockNodekey(object $lock) : string
{
    if ( ! getTailscaleLockEnabled($lock)) {
        return "";
    }

    return $lock->NodeKey;
}

function getTailscaleLockPubkey(object $lock) : string
{
    if ( ! getTailscaleLockEnabled($lock)) {
        return "";
    }

    return $lock->PublicKey;
}

function getTailscaleLockSigning(object $lock) : bool
{
    if ( ! getTailscaleLockSigned($lock)) {
        return false;
    }

    $isTrusted = false;
    $myKey     = getTailscaleLockPubkey($lock);

    foreach ($lock->TrustedKeys as $item) {
        if ($item->Key == $myKey) {
            $isTrusted = true;
        }
    }

    return $isTrusted;
}

/**
 * @return array<string, string>
 */
function getTailscaleLockPending(object $lock) : array
{
    if ( ! getTailscaleLockSigning($lock)) {
        return array();
    }

    $pending = array();

    foreach ($lock->FilteredPeers as $item) {
        $pending[$item->Name] = $item->NodeKey;
    }

    return $pending;
}

function getTailscaleLockWarning(object $lock) : string
{
    if (getTailscaleLockEnabled($lock) && ( ! getTailscaleLockSigned($lock))) {
        return "<span class='error' style='text-align: center; font-size: 1.4em; font-weight: bold;'>" . _tr("warnings.lock") . "</span>";
    }
    return "";
}
