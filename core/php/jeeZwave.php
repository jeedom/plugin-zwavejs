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

if (!jeedom::apiAccess(init('apikey'), 'zwavejs')) {
	echo __('Vous n\'etes pas autorisé à effectuer cette action', __FILE__);
	die();
}
if (isset($_GET['test'])) {
	echo 'OK';
	die();
}
$results = json_decode(file_get_contents("php://input"), true);
if (!is_array($results)) {
	die();
}
$delta = time() - config::byKey('lastinclusion', 'zwavejs', 0);
if (isset($results['devices'])) {
	foreach ($results['devices'] as $node_id => $datas) {
		$eqLogic = zwavejs::byLogicalId($node_id, 'zwavejs');
		if (is_object($eqLogic)) {
			foreach ($datas as $result) {
				foreach ($eqLogic->getCmd('info', $result['instance'] . '.' . $result['CommandClass'] . '.' . $result['index'], null, true) as $cmd) {
					$cmd->event($result['value']);
				}
				if ($result['CommandClass'] == '128') {
					$eqLogic->batteryStatus($result['value']);
				}
			}
		}
	}
}

if (isset($results['controller'])) {
	if (isset($results['controller']['state'])) {
		event::add('zwave::controller.data.controllerState',
			array('state' => $results['controller']['state']['value'])
		);
	}
	if (isset($results['controller']['excluded']) && $delta >15) {
		if ($results['controller']['excluded']['value'] == 999) {
			event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'zwavejs',
			'message' => __('Un périphérique Z-Wave qui ne fait pas parti du réseau est en cours d\'exclusion', __FILE__),
		));
		} else {
			event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'zwavejs',
				'message' => __('Un périphérique Z-Wave est en cours d\'exclusion. Logical ID : ', __FILE__) . $results['controller']['excluded']['value'],
			));
		}
		sleep(2);
		zwavejs::syncEqLogicWithzwavejs($results['controller']['excluded']['value'], 1);
	}
	if (isset($results['controller']['included'])) {
		config::save('lastinclusion', time(), 'zwavejs');
		if ($delta>15) {
			for ($i = 0; $i < 10; $i++) {
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'zwavejs',
					'message' => __('Nouveau module Z-Wave détecté. Début de l\'intégration. Pause de ', __FILE__) . (10 - $i) . __(' pour synchronisation avec le module', __FILE__),
				));
				sleep(1);
			}
			event::add('jeedom::alert', array(
				'level' => 'warning',
				'page' => 'zwavejs',
				'message' => __('Inclusion en cours...', __FILE__),
			));
		}
		zwavejs::syncEqLogicWithzwavejs($results['controller']['included']['value']);
	}
}

if (isset($results['network'])) {
	if (isset($results['network']['state']) && isset($results['network']['state']['value'])) {
		switch ($results['network']['state']['value']) {
			case 0: # STATE_STOPPED = 0
				event::add('jeedom::alert', array(
					'level' => 'danger',
					'page' => 'zwavejs',
					'message' => __('Le réseau Z-Wave est arrêté sur le serveur', __FILE__),
				));
				break;
			case 1: # STATE_FAILED = 1
				event::add('jeedom::alert', array(
					'level' => 'danger',
					'page' => 'zwavejs',
					'message' => __('Le réseau Z-Wave est en erreur sur le serveur', __FILE__),
				));
				break;
			case 3: # STATE_RESET = 3
				event::add('jeedom::alert', array(
					'level' => 'danger',
					'page' => 'zwavejs',
					'message' => __('Le réseau Z-Wave est remis à zéro sur le serveur', __FILE__),
				));
				break;
			case 5: # STATE_STARTED = 5
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'zwavejs',
					'message' => __('Le réseau Z-Wave est en cours de démarrage sur le serveur', __FILE__),
				));
				break;
			case 7: # STATE_AWAKED = 7
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'zwavejs',
					'message' => '',
				));
				break;
			case 10: # STATE_READY = 10
				event::add('jeedom::alert', array(
					'level' => 'warning',
					'page' => 'zwavejs',
					'message' => '',
				));
				break;
		}
	}
}

if (isset($results['message'])) {
	log::add('zwavejs', 'error', $results['message']);
}

if (isset($results['alert'])) {
	switch ($results['alert']['type']) {
		case 'node_dead':
			$message = '';
			$eqLogic = zwavejs::byLogicalId($results['alert']['id'], 'zwavejs');
			if (is_object($eqLogic)) {
				if ($eqLogic->getIsEnable()) {
					$message = __('Le noeud', __FILE__) . ' ' . $eqLogic->getHumanName() . ' (' . $results['alert']['id'] . ') ' . __('est présumé mort', __FILE__);
				}
			} else {
				$message = __('Le noeud', __FILE__) . ' ' . $results['alert']['id'] . ' ' . __('est présumé mort', __FILE__);
			}
			if ($message != '') {
				log::add('zwavejs', 'error', $message, 'node_dead_' . $results['alert']['id']);
			}
			break;
		case 'node_alive':
			message::removeAll('zwavejs', 'node_dead_' . $results['alert']['id']);
			break;
	}
}
