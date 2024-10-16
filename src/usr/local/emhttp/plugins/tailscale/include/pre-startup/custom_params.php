<?php

$tailscale_config = $tailscale_config ?? TailscaleHelpers::getPluginConfig();

$custom_params = "";

$wgport = intval($tailscale_config['WG_PORT']);
if ($wgport > 0 && $wgport < 65535) {
    $custom_params .= "-port {$wgport} ";
}

file_put_contents('/usr/local/emhttp/plugins/tailscale/custom-params.sh', 'TAILSCALE_CUSTOM_PARAMS="' . $custom_params . '"');
