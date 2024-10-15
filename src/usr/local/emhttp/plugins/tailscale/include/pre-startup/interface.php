<?php

$docroot = $docroot ?? $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

if ($configure_extra_interfaces ?? false) {
    require "{$docroot}/plugins/tailscale/include/set-tailscale-interface.php";
}
