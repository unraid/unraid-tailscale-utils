<?php

$endpoint = "https://plugin-usage.edacerton.win/";

function send_usage($url) {
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_exec($c);
    if (!curl_errno($c)) {
        $info = curl_getinfo($c);
        return $info['http_code'];
    }
    return -1;
}

if ($tailscale_config['USAGE']) {
    if ( ! isset($var)) {
        $var = parse_ini_file('/usr/local/emhttp/state/var.ini');
    }

    $version = parse_ini_file('/var/local/emhttp/plugins/tailscale/tailscale.ini');
    $query   = array(
        'clientId' => hash("crc32b", $var['flashGUID']),
        'plugin'   => 'tailscale',
        'version'  => $version['VERSION'],
        'branch'    => $version['BRANCH'],
        'unraid'   => $var['version']
    );

    $queryString = http_build_query($query);

    logmsg("Sending usage data: {$queryString}");
    $attempts = 0;
    do {
        sleep(rand(0, 600));

        $attempts++;
        $result = send_usage("{$endpoint}?{$queryString}");
    } while (($result != '201') && ($attempts < 5));

    if ($result != '201') {
        logmsg("Error occurred while transmitting usage data.");
    }
} else {
    logmsg("Usage collection disabled.");
}
