<?php

namespace Tailscale;

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

define(__NAMESPACE__ . "\PLUGIN_ROOT", dirname(dirname(__FILE__)));
define(__NAMESPACE__ . "\PLUGIN_NAME", "tailscale-utils");

// @phpstan-ignore requireOnce.fileNotFound
require_once "/usr/local/php/unraid-tailscale-utils/vendor/autoload.php";

$utils = new Utils(PLUGIN_NAME);
$utils->setPHPDebug();
