<?php

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

if (( ! isset($var)) || ( ! isset($display))) {
    throw new Exception("Missing required WebGUI variables");
}

require_once "{$docroot}/plugins/tailscale/include/common.php";
$tr = $tr ?? new Tailscale\Translator();

$tailscaleInfo = new Tailscale\Info($tr);

$tailscale_output = array();

$tailscale_dashboard = $tailscaleInfo->getDashboardInfo();

$tailscale_output['key_expiry_warning'] = $tailscaleInfo->getKeyExpirationWarning();
$tailscale_output['status_info']        = $tailscaleInfo->getStatusInfo();
$tailscale_output['connection_info']    = $tailscaleInfo->getConnectionInfo();

$tailscale_output['attach_file_tree'] = ($var['fsState'] == 'Started') ? "$('#taildropdir').fileTreeAttach();" : "";
$tailscale_output['background_color'] = strstr('white,azure', $display['theme']) ? '#f2f2f2' : '#1c1c1c';

$tailscale_output['lock_enabled'] = $tailscaleInfo->getTailscaleLockEnabled();
$tailscale_output['lock_signed']  = $tailscaleInfo->getTailscaleLockSigned();
$tailscale_output['lock_signing'] = $tailscaleInfo->getTailscaleLockSigning();
$tailscale_output['lock_pending'] = $tailscaleInfo->getTailscaleLockPending();
$tailscale_output['lock_pubkey']  = $tailscaleInfo->getTailscaleLockPubkey();
$tailscale_output['lock_nodekey'] = $tailscaleInfo->getTailscaleLockNodekey();
$tailscale_output['lock_warning'] = $tailscaleInfo->getTailscaleLockWarning();

$tailscale_output['netbios_warning'] = $tailscaleInfo->getTailscaleNetbiosWarning($var);
