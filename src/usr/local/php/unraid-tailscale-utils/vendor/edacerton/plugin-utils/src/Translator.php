<?php

namespace EDACerton\PluginUtils;

/*
    Copyright (C) 2025  Derek Kaser

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
*/

class Translator
{
    /** @var array<string, string> $lang */
    private array $lang;

    /**
     * @return array<string, string>
     * @param array<mixed,mixed> $array
     */
    private static function flattenArray(array $array, string $keys = ""): array
    {
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, self::flattenArray($value, $keys . $key . "."));
            } else {
                if (is_string($value) && ! empty($value)) {
                    $result[$keys . $key] = $value;
                }
            }
        }
        return $result;
    }

    public function __construct(string $basePath)
    {
        global $login_locale;

        $dynamix = parse_ini_file('/boot/config/plugins/dynamix/dynamix.cfg', true) ?: array();

        $locale        = $_SESSION['locale'] ?? ($login_locale ?? ($dynamix['display']['locale'] ?? "none"));
        $plugin_locale = (array) json_decode(file_get_contents($basePath . "/locales/en_US.json") ?: "{}", true);
        $plugin_lang   = self::flattenArray($plugin_locale);

        if (file_exists($basePath . "/locales/{$locale}.json")) {
            $current_locale = (array) json_decode(file_get_contents($basePath . "/locales/{$locale}.json") ?: "{}", true);
            $current_lang   = self::flattenArray($current_locale);
            $plugin_lang    = array_replace($plugin_lang, $current_lang);
        }

        $this->lang = $plugin_lang;
    }

    public function tr(string $message, bool $htmlencode = true): string
    {
        return $htmlencode ? htmlspecialchars($this->lang[strtolower($message)]) : $this->lang[strtolower($message)];
    }
}
