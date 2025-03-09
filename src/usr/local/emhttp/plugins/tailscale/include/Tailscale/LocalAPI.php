<?php

namespace Tailscale;

class LocalAPI
{
    private string $tailscaleSocket = '/var/run/tailscale/tailscaled.sock';

    private function tailscaleLocalAPI(string $url, bool $post = false, string $body = ""): string
    {
        Utils::logmsg("Tailscale Local API: {$url}");
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, $this->tailscaleSocket);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "http://local-tailscaled.sock/localapi/{$url}");

        if ($post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $out = curl_exec($ch) ?: false;
        curl_close($ch);
        return strval($out);
    }

    public function getTailscaleStatus(): \stdClass
    {
        return (object) json_decode($this->tailscaleLocalAPI('v0/status'));
    }

    public function getTailscalePrefs(): \stdClass
    {
        return (object) json_decode($this->tailscaleLocalAPI('v0/prefs'));
    }

    public function getTailscaleLockStatus(): \stdClass
    {
        return (object) json_decode($this->tailscaleLocalAPI('v0/tka/status'));
    }

    public function requestAuthURL(): void
    {
        $this->tailscaleLocalAPI('v0/login-interactive', true);
    }
}
