<?php

$tailscale_config = $tailscale_config ?? Tailscale\Helpers::getPluginConfig();

$endpoint = "https://plugin-usage.edacerton.win/";

function download_url(string $url): string
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);
    curl_setopt($ch, CURLOPT_ENCODING, "");
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_REFERER, "");
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $out = curl_exec($ch) ?: false;
    curl_close($ch);
    return strval($out);
}

/**
 * @param array<mixed> $content
 */
function send_usage(string $url, array $content): int
{
    $body  = json_encode($content);
    $token = download_url($url . '?connect');

    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);

    $headers = [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $token
    ];

    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $body);
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_USERAGENT, 'plugin-metrics/1.0.0');

    curl_exec($c);
    if ( ! curl_errno($c)) {
        $info = curl_getinfo($c);
        return $info['http_code'];
    }
    return -1;
}

if ($tailscale_config['USAGE']) {
    if ( ! isset($var)) {
        $var = parse_ini_file('/usr/local/emhttp/state/var.ini');
    }

    $version = parse_ini_file('/var/local/emhttp/plugins/tailscale/tailscale.ini') ?: array();

    $prefs = Tailscale\Info::getPrefs();

    if (isset($prefs->LoggedOut) ? ($prefs->LoggedOut ? true : false) : true) {
        Tailscale\Helpers::logmsg("Skipping usage data collection; not logged in.");
        return;
    }

    $exit          = false;
    $subnet        = false;
    $customControl = false;

    foreach (($prefs->AdvertiseRoutes ?? array()) as $net) {
        switch ($net) {
            case "0.0.0.0/0":
            case "::/0":
                $exit = true;
                break;
            default:
                $subnet = true;
                break;
        }
    }

    if ($prefs->ControlURL != "https://controlplane.tailscale.com") {
        $customControl = true;
    }

    $content = array(
        'clientId'       => hash("crc32b", $var['flashGUID']),
        'plugin'         => 'tailscale',
        'plugin_version' => $version['VERSION'],
        'plugin_branch'  => $version['BRANCH'],
        'unraid_version' => $var['version'],
        'bool1'          => boolval($tailscale_config['ACCEPT_DNS']),
        'bool2'          => boolval($tailscale_config['ACCEPT_ROUTES']),
        'bool3'          => boolval($tailscale_config['INCLUDE_INTERFACE']),
        'bool4'          => $subnet,
        'bool5'          => $exit,
        'num1'           => $customControl ? 0 : 1
    );

    $attempts = 0;
    $delay    = rand(0, 300);
    do {
        Tailscale\Helpers::logmsg("Waiting for {$delay} seconds before sending usage data.");
        sleep($delay);
        $delay += 300;
        $attempts++;

        $result = send_usage($endpoint, $content);
        Tailscale\Helpers::logmsg("Usage data sent.");
    } while (($result != '200') && ($attempts < 3));

    if ($result != '200') {
        Tailscale\Helpers::logmsg("Error occurred while transmitting usage data.");
    }
} else {
    Tailscale\Helpers::logmsg("Usage collection disabled.");
}
