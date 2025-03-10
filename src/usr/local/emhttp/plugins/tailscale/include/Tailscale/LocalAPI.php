<?php

namespace Tailscale;

enum APIMethods
{
    case GET;
    case POST;
    case PATCH;
}

class LocalAPI
{
    private string $tailscaleSocket = '/var/run/tailscale/tailscaled.sock';

    private function tailscaleLocalAPI(string $url, APIMethods $method = APIMethods::GET, object $body = new \stdClass()): string
    {
        $ch = curl_init();

        $headers = [];

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, $this->tailscaleSocket);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "http://local-tailscaled.sock/localapi/{$url}");

        if ($method == APIMethods::POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            Utils::logmsg("Tailscale Local API: {$url} POST " . json_encode($body));
            $headers[] = "Content-Type: application/json";
        }

        if ($method == APIMethods::PATCH) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
            Utils::logmsg("Tailscale Local API: {$url} PATCH " . json_encode($body));
            $headers[] = "Content-Type: application/json";
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $out = curl_exec($ch) ?: false;
        curl_close($ch);
        return strval($out);
    }

    public function getStatus(): \stdClass
    {
        return (object) json_decode($this->tailscaleLocalAPI('v0/status'));
    }

    public function getPrefs(): \stdClass
    {
        return (object) json_decode($this->tailscaleLocalAPI('v0/prefs'));
    }

    public function getTkaStatus(): \stdClass
    {
        return (object) json_decode($this->tailscaleLocalAPI('v0/tka/status'));
    }

    public function postLoginInteractive(): void
    {
        $this->tailscaleLocalAPI('v0/login-interactive', APIMethods::POST);
    }

    public function patchPref(string $key, mixed $value): void
    {
        $body              = [];
        $body[$key]        = $value;
        $body["{$key}Set"] = true;

        $this->tailscaleLocalAPI('v0/prefs', APIMethods::PATCH, (object) $body);
    }

    public function postTkaSign(string $key): void
    {
        $body = ["NodeKey" => $key];
        $this->tailscaleLocalAPI("v0/tka/sign", APIMethods::POST, (object) $body);
    }
}
