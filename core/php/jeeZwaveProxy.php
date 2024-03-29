<?php

/* This file is part of Plugin zwavejs for jeedom.
 *
 * Plugin zwavejs for jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Plugin zwavejs for jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Plugin zwavejs for jeedom. If not, see <http://www.gnu.org/licenses/>.
 */
require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	echo __('401 - Accès non autorisé', __FILE__);
	die();
}
ajax::init();
try {
	echo json_encode(zwavejs::callzwavejs(str_replace('//', '/', init('request'))));
} catch (Exception $e) {
	http_response_code(500);
	die($e->getMessage());
}
