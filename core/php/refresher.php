<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/
declare (ticks = 1);

global $SIG;
$SIG = false;

function sig_handler($signo) {
	global $SIG;
	$SIG = true;
}

pcntl_signal(SIGTERM, "sig_handler");
pcntl_signal(SIGHUP, "sig_handler");

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
log::add('zwavejs', 'debug', 'Refresher');
if (isset($argv)) {
	foreach ($argv as $arg) {
		$argList = explode('=', $arg);
		if (isset($argList[0]) && isset($argList[1])) {
			$_GET[$argList[0]] = $argList[1];
		}
	}
}
if (init('id') == '') {
	log::add('zwavejs', 'debug', __('Refresher l\'id ne peut etre vide', __FILE__));
	die();
}
$eqLogic = zwavejs::byId(init('id'));
if (!is_object($eqLogic)) {
	die();
}
if ($eqLogic->getEqType_name() != 'zwavejs') {
	die();
}
$target = init('target');
$command = explode('-', $target, 3);
if (count($command)== 3) {
	$target = $command[1].'-'.$command[0].'-'.$command[2];
} else {
	die();
}
$number = init('number');
$sleep = init('sleep');
log::add('zwavejs', 'debug', 'Refresh '. $eqLogic->getHumanName() . ' ' . $target . ' ' .$number . ' times. Each ' . $sleep . 's');
set_time_limit(100);
$starttime = strtotime('now');
$realNumber = 0;
while (true) {
	usleep(round($sleep * 1000000));
	try {
		$eqLogic->pollValue($target);
		log::add('zwavejs', 'debug', 'Refresh '. $eqLogic->getHumanName() . ' ' . $target . ' number : ' .$realNumber);
		$realNumber +=1;
	} catch (Exception $e) {
		$realNumber +=1;
	}
	if ($SIG) {
		break;
	}
	if ($realNumber >= $number){
		break;
	}
	if ($SIG) {
		break;
	}
	if ((strtotime('now') - $starttime) > 100) {
		break;
	}
}
die();