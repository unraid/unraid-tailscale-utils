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
    private const tailscaleSocket = '/var/run/tailscale/tailscaled.sock';

    private function tailscaleLocalAPI(string $url, APIMethods $method = APIMethods::GET, object $body = new \stdClass()): string
    {
        if (empty($url)) {
            throw new \InvalidArgumentException("URL cannot be empty");
        }

        $body_encoded = json_encode($body);

        if ( ! $body_encoded) {
            throw new \InvalidArgumentException("Failed to encode JSON");
        }

        $ch = curl_init();

        $headers = [];

        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_UNIX_SOCKET_PATH, $this::tailscaleSocket);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, "http://local-tailscaled.sock/localapi/{$url}");

        if ($method == APIMethods::POST) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body_encoded);
            Utils::logmsg("Tailscale Local API: {$url} POST " . $body_encoded);
            $headers[] = "Content-Type: application/json";
        }

        if ($method == APIMethods::PATCH) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body_encoded);
            Utils::logmsg("Tailscale Local API: {$url} PATCH " . $body_encoded);
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

    public function getServeConfig(): \stdClass
    {
        return (object) json_decode($this->tailscaleLocalAPI('v0/serve-config'));
    }

    public function resetServeConfig(): void
    {
        $this->tailscaleLocalAPI("v0/serve-config", APIMethods::POST, new \stdClass());
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

    public function expireKey(): void
    {
        $this->tailscaleLocalAPI('v0/set-expiry-sooner?expiry=0', APIMethods::POST);
    }
}
