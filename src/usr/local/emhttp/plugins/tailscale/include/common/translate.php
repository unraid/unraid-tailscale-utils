<?php

function _tr($message)
{
    global $tailscale_lang;

    return $tailscale_lang[strtolower($message)];
}
$locale = $_SESSION['locale'] ?? ($login_locale ?? parse_ini_file('/boot/config/plugins/dynamix/dynamix.cfg', true)['display']['locale']);

$tailscale_locale = json_decode(file_get_contents("/usr/local/emhttp/plugins/tailscale/locales/en_US.json"), true);

if (file_exists("/usr/local/emhttp/plugins/tailscale/locales/{$locale}.json")) {
    $current_locale   = json_decode(file_get_contents("/usr/local/emhttp/plugins/tailscale/locales/{$locale}.json"), true);
    $tailscale_locale = array_replace_recursive($tailscale_locale, $current_locale);
}

$ritit          = new RecursiveIteratorIterator(new RecursiveArrayIterator($tailscale_locale));
$tailscale_lang = array();
foreach ($ritit as $leafValue) {
    $keys = array();
    foreach (range(0, $ritit->getDepth()) as $depth) {
        $keys[] = $ritit->getSubIterator($depth)->key();
    }
    $tailscale_lang[ strtolower(join('.', $keys)) ] = $leafValue;
}
