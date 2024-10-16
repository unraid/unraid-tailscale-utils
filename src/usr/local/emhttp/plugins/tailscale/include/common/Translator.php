<?php

class Translator
{
    /** @var array<string, string> $tailscale_lang */
    private array $tailscale_lang;

    public function __construct()
    {
        global $login_locale;

        $dynamix = parse_ini_file('/boot/config/plugins/dynamix/dynamix.cfg', true) ?: array();

        $locale           = $_SESSION['locale'] ?? ($login_locale ?? $dynamix['display']['locale']);
        $tailscale_locale = (array) json_decode(file_get_contents("/usr/local/emhttp/plugins/tailscale/locales/en_US.json") ?: "{}", true);

        if (file_exists("/usr/local/emhttp/plugins/tailscale/locales/{$locale}.json")) {
            $current_locale   = (array) json_decode(file_get_contents("/usr/local/emhttp/plugins/tailscale/locales/{$locale}.json") ?: "{}", true);
            $tailscale_locale = array_replace_recursive($tailscale_locale, $current_locale);
        }

        $ritit          = new RecursiveIteratorIterator(new RecursiveArrayIterator($tailscale_locale));
        $tailscale_lang = array();
        foreach ($ritit as $leafValue) {
            $keys = array();
            foreach (range(0, $ritit->getDepth()) as $depth) {
                $keys[] = $ritit->getSubIterator($depth)->key();
            }
            if (is_string($leafValue)) {
                $tailscale_lang[ strtolower(join('.', $keys)) ] = $leafValue;
            }
        }

        $this->tailscale_lang = $tailscale_lang;
    }

    public function tr(string $message): string
    {
        return $this->tailscale_lang[strtolower($message)];
    }
}
