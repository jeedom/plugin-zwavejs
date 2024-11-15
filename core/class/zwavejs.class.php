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

/* * ***************************Includes********************************* */



class zwavejs extends eqLogic {

	public static function dependancy_end() {
		config::save('zwavejsVersion', config::byKey('wantedVersion', __CLASS__), __CLASS__);
	}

	private function getValueLabels($_key, $_value) {
		$labelArray = array(
			"64-mode" => array(
				"0" => __('Inactif', __FILE__),
				"1" => __('En Chauffe', __FILE__),
				"2" => __('En Refroidissement', __FILE__),
				"3" => __('Auto', __FILE__),
				"5" => __('Resume (on)', __FILE__),
				"6" => __('Ventillation', __FILE__),
				"8" => __('Asséchement', __FILE__)
			),
			"66-state" => array(
				"0" => __('Inactif', __FILE__),
				"1" => __('En Chauffe', __FILE__),
				"2" => __('En Refroidissement', __FILE__),
				"3" => __('Ventillation', __FILE__),
				"4" => __('Chauffe en attente', __FILE__),
				"5" => __('Refroidissement en attente', __FILE__),
				"6" => __('Vent/Eco', __FILE__),
				"7" => __('Chauffe Aux', __FILE__),
				"8" => __('Chauffe 2nd', __FILE__),
				"9" => __('Refroidissement 2nd', __FILE__),
				"10" => __('Chauffe 2nd Aux', __FILE__),
				"11" => __('Chauffe 3ème Aux', __FILE__)
			),
			"68-mode" => array(
				"0" => __('Auto Bas', __FILE__),
				"1" => __('Bas', __FILE__),
				"3" => __('Haut', __FILE__),
				"5" => __('Moyen', __FILE__)
			),
			"91-scene" => array(
				"0" => __('Appui 1x', __FILE__),
				"1" => __('Relâchement', __FILE__),
				"2" => __('Appui long', __FILE__),
				"3" => __('Appui 2x', __FILE__),
				"4" => __('Appui 3x', __FILE__),
				"5" => __('Appui 4x', __FILE__),
				"6" => __('Appui 5x', __FILE__),
				"90" => 'N/A'
			),
			"102-currentState" => array(
				"0" => __('Fermé', __FILE__),
				"252" => __('Fermeture en cours', __FILE__),
				"253" => __('Arrêté', __FILE__),
				"254" => __('Ouverture en cours', __FILE__),
				"255" => __('Ouvert', __FILE__)
			)
		);
		$result = false;
		if (isset($labelArray[$_key]) && isset($labelArray[$_key][$_value])) {
			$result = $labelArray[$_key][$_value];
		}
		return $result;
	}
	/*     * *************************Attributs****************************** */

	public static $_excludeOnSendPlugin = array('zwavejs.log');

	/*     * ***********************Methode static*************************** */
	/*     * ********************************************************************** */
	/*     * ***********************zwavejs MANAGEMENT*************************** */

	public static function secondsToTime($seconds) {
		try {
			$dtF = new \DateTime('@0');
			$dtT = new \DateTime("@$seconds");
			return $dtF->diff($dtT)->format('%a j %h h %i min %s s');
		} catch (Exception $e) {
			return 'N/A';
		}
	}

	public static function convertArrayToColor($_color) {
		$color = '#';
		$color .= isset($_color['red']) ? str_pad(dechex($_color['red']), 2, '0', STR_PAD_LEFT) : '00';
		$color .= isset($_color['green']) ? str_pad(dechex($_color['green']), 2, '0', STR_PAD_LEFT) : '00';
		$color .= isset($_color['blue']) ? str_pad(dechex($_color['blue']), 2, '0', STR_PAD_LEFT) : '00';
		$color .= isset($_color['warmWhite']) ? str_pad(dechex($_color['warmWhite']), 2, '0', STR_PAD_LEFT) : '00';
		$color .= isset($_color['coldWhite']) ? str_pad(dechex($_color['coldWhite']), 2, '0', STR_PAD_LEFT) : '00';
		return $color;
	}

	public static function convertColorToArray($_color) {
		$array = array();
		$color = str_replace('#', '', $_color);
		$split_hex_color = str_split($color, 2);
		$array['red'] = hexdec($split_hex_color[0]);
		$array['green'] = hexdec($split_hex_color[1]);
		$array['blue'] = hexdec($split_hex_color[2]);
		$array['warmWhite'] = 0;
		$array['coldWhite'] = 0;
		return $array;
	}

	public static function cron() {
		$eqLogics = self::byType(__CLASS__);
		foreach ($eqLogics as $eqLogic) {
			$polling = $eqLogic->getConfiguration('polling', array());
			foreach ($polling as $class => $time) {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Polling Found for ' . $eqLogic->getHumanName() . ' ' . $time . ' ' . $class);
				if ($time != 'Aucun') {
					$c = new Cron\CronExpression(checkAndFixCron('*/' . $time . ' * * * *'), new Cron\FieldFactory);
					if ($c->isDue()) {
						$eqLogic->pollValue($class);
					}
				}
			}
		}
	}

	public static function cronHourly() {
		$deamon_info = self::deamon_info();
		if ($deamon_info['state'] != 'ok') {
			return;
		}
		self::getNodes('health');
	}

	public static function configureSettings($_path) {
		$file = $_path . '/settings.json';
		$settings = array();
		if (file_exists($file)) {
			unlink($file);
		}
		$settings['mqtt'] = array();
		$settings['gateway'] = array();
		$settings['zwave'] = array();

		$mqttInfos = mqtt2::getFormatedInfos();
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Informations reçues de MQTT Manager', __FILE__) . ' : ' . json_encode($mqttInfos));

		self::isValidKey(config::byKey('s2key_access', __CLASS__, '')) ? true : config::save('s2key_access', self::generateRandomKey(), __CLASS__);
		self::isValidKey(config::byKey('s2key_unauth', __CLASS__, '')) ? true : config::save('s2key_unauth', self::generateRandomKey(), __CLASS__);
		self::isValidKey(config::byKey('s2key_auth', __CLASS__, '')) ? true : config::save('s2key_auth', self::generateRandomKey(), __CLASS__);
		self::isValidKey(config::byKey('s2key_auth_long', __CLASS__, '')) ? true : config::save('s2key_auth_long', self::generateRandomKey(), __CLASS__);
		self::isValidKey(config::byKey('s2key_access_long', __CLASS__, '')) ? true : config::save('s2key_access_long', self::generateRandomKey(), __CLASS__);
		self::isValidKey(config::byKey('s0key', __CLASS__, '')) ? true : config::save('s0key', self::generateRandomKey(), __CLASS__);

		$settings['mqtt']['name'] = 'Jeedom';
		$settings['mqtt']['host'] = $mqttInfos['ip'];
		$settings['mqtt']['port'] = $mqttInfos['port'];
		$settings['mqtt']['auth'] = true;
		$settings['mqtt']['username'] = $mqttInfos['user'];
		$settings['mqtt']['password'] = $mqttInfos['password'];
		$settings['mqtt']['prefix'] = config::byKey('prefix', __CLASS__, 'zwave');
		//if ($port != 'auto') {
		//	$port = jeedom::getUsbMapping($port);
		//	exec(system::getCmdSudo() . 'chmod 777 ' . $port . ' > /dev/null 2>&1');
		//}
		$settings['zwave']['port'] = jeedom::getUsbMapping(config::byKey('port', __CLASS__));
		$settings['zwave']['commandsTimeout'] = 60;
		$settings['zwave']['logLevel'] = 'error';
		$settings['zwave']['logEnabled'] = true;
		$settings['zwave']['logToFile'] = false;
		$settings['zwave']['serverEnabled'] = false;
		if (config::byKey('softReset', __CLASS__, 1) == 1) {
			$settings['zwave']['enableSoftReset'] = true;
		} else {
			$settings['zwave']['enableSoftReset'] = false;
		}
		$settings['zwave']['disclaimerVersion'] = 1;
		$settings['zwave']['enableStatistics'] = false;
		$settings['zwave']['deviceConfigPriorityDir'] = realpath(dirname(__FILE__) . '/../config/config');
		$settings['zwave']['securityKeys'] = array(
			'S2_AccessControl' => config::byKey('s2key_access', __CLASS__),
			'S0_Legacy' => config::byKey('s0key', __CLASS__),
			'S2_Unauthenticated' => config::byKey('s2key_unauth', __CLASS__),
			'S2_Authenticated' => config::byKey('s2key_auth', __CLASS__)
		);
		$settings['zwave']['securityKeysLongRange'] = array(
			'S2_AccessControl' => config::byKey('s2key_access_long', __CLASS__),
			'S2_Authenticated' => config::byKey('s2key_auth_long', __CLASS__)
		);

		$settings['gateway']['type'] = 0;
		$settings['gateway']['authEnabled'] = true;
		$settings['gateway']['payloadType'] = 0;
		$settings['gateway']['nodeNames'] = false;
		$settings['gateway']['hassDiscovery'] = false;
		$settings['gateway']['ignoreLoc'] = true;
		$settings['gateway']['sendEvents'] = true;
		$settings['gateway']['includeNodeInfo'] = false;
		$settings['gateway']['publishNodeDetails'] = true;
		$settings['gateway']['logLevel'] = 'error';
		$settings['gateway']['logToFile'] = false;

		file_put_contents($file, json_encode($settings, JSON_FORCE_OBJECT));
	}

	public static function addFileEvent($_file, $_data) {
		$status_path = dirname(__FILE__) . '/../../data/status';
		if (!is_dir($status_path)) {
			mkdir($status_path, 0777, true);
		}
		$file = $status_path . '/' . $_file . '.json';
		file_put_contents($file, json_encode($_data, JSON_FORCE_OBJECT));
	}

	public static function getFile($_type, $_nodeId) {
		$status_path = dirname(__FILE__) . '/../../data/status';
		if ($_type == 'nodeInfo') {
			$file = $status_path . '/getNodeInfo' . $_nodeId . '.json';
		} else if ($_type == 'nodeValues') {
			$file = $status_path . '/getNodeValues' . $_nodeId . '.json';
		} else if ($_type == 'health') {
			$file = $status_path . '/getHealthPage.json';
		} else if ($_type == 'nodeStats') {
			$file = $status_path . '/getNodeStats.json';
		} else if ($_type == 'info') {
			$file = $status_path . '/getInfo.json';
		} else if ($_type == 'neighbors') {
			$file = $status_path . '/getNeighbors.json';
		} else if ($_type == 'NodeAssociations') {
			$file = $status_path . '/getNodeAssociations.json';
		} else if ($_type == 'group') {
			$file = $status_path . '/getNodeGroup.json';
		} else if ($_type == 'mobileHealth') {
			$file = $status_path . '/getHealthPageMobile.json';
		}
		$data = json_decode(file_get_contents($file), true);
		return $data;
	}

	public static function additionnalDependancyCheck() {
		$return = array();
		$return['state'] = 'ok';
		if (config::byKey('lastDependancyInstallTime', __CLASS__) == '') {
			$return['state'] = 'nok';
		} else if (!file_exists(__DIR__ . '/../../resources/zwave-js-ui/node_modules')) {
			$return['state'] = 'nok';
		}
		return $return;
	}

	public static function dependancy_info() {
		$return = array();
		$return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependance';
		$return['state'] = 'ok';
		if (config::byKey('lastDependancyInstallTime', __CLASS__) == '') {
			$return['state'] = 'nok';
		} else if (!file_exists(__DIR__ . '/../../resources/zwave-js-ui/node_modules')) {
			$return['state'] = 'nok';
		}
		return $return;
	}

	public static function deamon_info() {
		$return = array();
		$return['log'] = __CLASS__;
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		if (self::isRunning()) {
			$return['state'] = 'ok';
		}
		$port = config::byKey('port', __CLASS__);
		if ($port == 'none') {
			$return['launchable'] = 'nok';
			$return['launchable_message'] = __("Le port n'est pas configuré", __FILE__);
		} else {
			$port = jeedom::getUsbMapping($port);
			if (is_array($port) || @!file_exists($port)) {
				$return['launchable'] = 'nok';
				$return['launchable_message'] = __("Le port n'est pas configuré", __FILE__);
			}
		}
		if (!class_exists('mqtt2')) {
			$return['launchable'] = 'nok';
			$return['launchable_message'] = __("Le plugin MQTT Manager n'est pas installé", __FILE__);
		} else {
			if (mqtt2::deamon_info()['state'] != 'ok') {
				$return['launchable'] = 'nok';
				$return['launchable_message'] = __("Le démon MQTT Manager n'est pas démarré", __FILE__);
			}
		}
		if (class_exists('openzwave')) {
			if (openzwave::deamon_info()['state'] == 'ok' && config::byKey('port', __CLASS__) == config::byKey('port', 'openzwave')) {
				$return['launchable'] = 'nok';
				$return['launchable_message'] = __('Le démon OpenZwave est démarré sur le même contrôleur, il doit être stoppé.', __FILE__);
			}
		}
		return $return;
	}

	public static function isRunning() {
		if (!empty(system::ps('server/bin/www.js'))) {
			return true;
		}
		return false;
	}

	public static function deamon_start($_debug = false) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Inscription au plugin mqtt2');
		config::save('controllerStatus', 'none', __CLASS__);
		config::save('driverStatus', 0, __CLASS__);
		self::deamon_stop();
		mqtt2::addPluginTopic(__CLASS__, config::byKey('prefix', __CLASS__, 'zwave'));
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$zwavejs_path = realpath(dirname(__FILE__) . '/../../resources/zwave-js-ui');
		$data_path = dirname(__FILE__) . '/../../data/store';
		if (!is_dir($data_path)) {
			mkdir($data_path, 0777, true);
		}
		$backup_path = dirname(__FILE__) . '/../../data/store/backups/nvm';
		if (!is_dir($backup_path)) {
			mkdir($backup_path, 0777, true);
		}
		$cmd = "chown -R www-data:www-data " . dirname(__FILE__) . '/../../data/store/backups';
		exec(system::getCmdSudo() . $cmd . ' >> ' . log::getPathToLog('zwavejsd') . ' 2>&1 &');
		$status_path = realpath(dirname(__FILE__) . '/../../data/status');
		$files = glob($status_path . '/*.json');
		foreach ($files as $file) {
			if (is_file($file)) {
				unlink($file);
			}
		}
		$data_path = realpath(dirname(__FILE__) . '/../../data/store');
		self::configureSettings($data_path);
		chdir($zwavejs_path);
		$cmd = '';
		$cmd .= 'STORE_DIR=' . $data_path;
		$cmd .= ' KEY_S0_Legacy=' . config::byKey('s0key', __CLASS__);
		$cmd .= ' KEY_S2_Unauthenticated=' . config::byKey('s2key_unauth', __CLASS__);
		$cmd .= ' KEY_S2_Authenticated=' . config::byKey('s2key_auth', __CLASS__);
		$cmd .= ' KEY_S2_AccessControl=' . config::byKey('s2key_access', __CLASS__);
		$cmd .= ' SESSION_SECRET=' . 'jeedomSession';
		$cmd .= ' npm start';
		log::add(__CLASS__, 'info', __('Démarrage du démon ZwaveJS', __FILE__) . ' : ' . $cmd);
		exec(system::getCmdSudo() . $cmd . ' >> ' . log::getPathToLog('zwavejsd') . ' 2>&1 &');
		$i = 0;
		while ($i < 10) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'ok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 10) {
			log::add(__CLASS__, 'error', __('Impossible de démarrer le démon ZwaveJS, consultez les logs', __FILE__), 'unableStartDeamon');
			return false;
		}
		config::save('lastStart', time(), __CLASS__);
		message::removeAll(__CLASS__, 'unableStartDeamon');
		self::cleanHistory();
		// log::add(__CLASS__, 'info', 'Démon zwavejs lancé');
		return true;
	}

	public static function deamon_stop() {
		log::add(__CLASS__, 'info', __('Arrêt du démon ZwaveJS', __FILE__));
		config::save('controllerStatus', 'none', __CLASS__);
		$find = 'server/bin/www.js';
		$cmd = "(ps ax || ps w) | grep -ie '" . $find . "' | grep -v grep | awk '{print $1}' | xargs " . system::getCmdSudo() . "kill -15 > /dev/null 2>&1";
		exec($cmd);
		$i = 0;
		while ($i < 5) {
			$deamon_info = self::deamon_info();
			if ($deamon_info['state'] == 'nok') {
				break;
			}
			sleep(1);
			$i++;
		}
		if ($i >= 5) {
			system::kill('server/bin/www.js', true);
			$i = 0;
			while ($i < 5) {
				$deamon_info = self::deamon_info();
				if ($deamon_info['state'] == 'nok') {
					break;
				}
				sleep(1);
				$i++;
			}
		}
		system::fuserk(8091);
	}

	public static function generateRandomKey() {
		$randHexStr = strtoupper(implode('', array_map(function () {
			return dechex(mt_rand(0, 15));
		}, array_fill(0, 32, null))));
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $randHexStr);
		return $randHexStr;
	}

	public static function isValidKey($_key) {
		if (!ctype_xdigit($_key)) {
			return false;
		}
		if (strlen($_key) != 32) {
			return false;
		}
		return true;
	}

	public static function flatten_array($array, $prefix = '') {
		$result = array();
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$new_key = $prefix . (empty($prefix) ? '' : '-') . $key;
				$result = array_merge($result, self::flatten_array($value, $new_key));
			} else {
				$new_key = $prefix;
				$result[$new_key][$key] = $value;
			}
		}
		return $result;
	}

	public static function handleMqttMessage($_message) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Message Mqtt reçu');
		// log::add(__CLASS__, 'debug', json_encode($_message));
		if (isset($_message[config::byKey('prefix', __CLASS__, 'zwave')])) {
			$message = $_message[config::byKey('prefix', __CLASS__, 'zwave')];
		} else {
			log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __("Le message reçu n'est pas un message Z-Wave", __FILE__));
			return;
		}
		foreach ($message as $key => $value) {
			if ($key == '_EVENTS') {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Le message est un event');
				self::handleEvents($value);
			} else if ($key == '_CLIENTS') {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Le message est une réponse api Client');
				self::handleClients($value);
			} else if (is_int($key)) {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Le message est un event direct');
				self::handleNodeValueUpdateDirect($key, $value);
			} else if ($key == 'driver') {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Le message est une info driver');
				if (isset($value['status'])) {
					config::save('driverStatus', $value['status'], __CLASS__);
					event::add('zwavejs::driverStatus', array('status' => $value['status']));
				}
			} else {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Le message reçu est de type inconnu', __FILE__));
			}
		}
	}

	public static function handleClients($_clients) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Traitement d'un Client Api");
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . json_encode($_clients));
		$gateway = array_key_first($_clients);
		$client = $_clients[$gateway];
		foreach ($client as $key => $value) {
			// log::add(__CLASS__, 'debug', $key);
			if ($key == 'api') {
				self::handleApi($value);
			} else if ($key == 'version') {
				config::save('zwavejsVersion', $value['value'], __CLASS__);
				event::add('zwavejs::dependancy_end', array());
				if (config::byKey('wantedVersion', __CLASS__) != config::byKey('zwavejsVersion', __CLASS__)) {
					sleep(2);
					message::add('zwavejs', __("Votre version de ZwaveJS UI n'est pas celle recommandée par le plugin. Vous utilisez actuellement la version ", __FILE__) . config::byKey('zwavejsVersion', __CLASS__) . '. ' . __('Le plugin nécessite la version ', __FILE__) . config::byKey('wantedVersion', __CLASS__) . '. ' . __('Veuillez relancer les dépendances pour mettre à jour la librairie.', __FILE__));
				}
			}
		}
	}

	public static function handleEvents($_events) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Traitement d'un Event");
		//log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . json_encode($_events));
		$gateway = array_key_first($_events);
		$event = $_events[$gateway];
		foreach ($event as $key => $value) {
			// log::add(__CLASS__, 'debug', $key);
			if ($key == 'node') {
				self::handleNode($value);
			} else if ($key == 'controller') {
				self::handleController($value);
			}
		}
	}

	public static function handleApi($_api) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Traitement d'un retour api");
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . json_encode($_api));
		foreach ($_api as $key => $value) {
			if ($key == 'abortFirmwareUpdate') {
				if (isset($value['success']) && $value['success']) {
					event::add('zwavejs::firmware_update', array('node' => $value['args'][0], 'cancel' => true));
				}
			} else if ($key == 'restoreNVM') {
				if (isset($value['success']) && !$value['success']) {
					event::add('zwavejs::restoreNVM', array('message' => $value['message']));
				}
			} else if ($key == 'getInfo') {
				self::addFileEvent('getInfo', $value['result']);
				if (isset($value['result']['controllerId'])) {
					config::save('controllerId', $value['result']['controllerId'], __CLASS__);
				}
			} else if ($key == 'getNodes') {
				if ($value['origin']['type'] == 'sync') {
					self::syncNodes($value['result']);
				} else if ($value['origin']['type'] == 'stats') {
					$stats = array();
					$stats['totalNodes'] = count($value['result']);
					$sleepingNodes = 0;
					$networkTree = array('controllerId' => config::byKey('controllerId', __CLASS__, 0), 'data' => array());
					$data = array();
					foreach ($value['result'] as $node) {
						$data = $node;
						$eqLogic = self::byLogicalId($node['id'], __CLASS__);
						if (is_object($eqLogic)) {
							$data['eqName'] = $eqLogic->getHumanName(true);
							$data['name'] = $eqLogic->getHumanName();
							$data['img'] = $eqLogic->getImage();
						} else {
							$data['img'] = 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
						}
						// log::add(__CLASS__, 'debug', json_encode($node));
						if ($node['id'] == config::byKey('controllerId', __CLASS__, 0)) {
							$stats['controllerNeighbors'] = implode(' - ', $node['neighbors']);
							$stats['stats'] = $node['statistics'];
						}
						if ($node['status'] == 'Asleep') {
							$sleepingNodes += 1;
						}
						unset($data['deviceConfig']);
						unset($data['values']);
						$networkTree['data'][$data['id']] = $data;
					}
					$stats['sleepingNodes'] = $sleepingNodes;
					$stats['networkTree'] = $networkTree;
					self::addFileEvent('getNodeStats', $stats);
				} else if ($value['origin']['type'] == 'getNodeInfo') {
					foreach ($value['result'] as $node) {
						if ($node['id'] == $value['origin']['node']) {
							$node['neighbors'] = implode(' - ', $node['neighbors']);
							if (isset($node['deviceConfig']['filename']) && $node['deviceConfig']['filename'] != '') {
								$explodeFile = explode('/', $node['deviceConfig']['filename']);
								$fileExt = '(Jeedom)';
								if (in_array('@zwave-js', $explodeFile)) {
									$fileExt = '(Zwave-Js)';
								}
								$node['filename'] = end($explodeFile) . ' ' . $fileExt;
							} else {
								$node['filename'] = 'Aucun';
							}
							$node['numberGroups'] = count($node['groups']);
							$node['classBasic'] = $node['deviceClass']['basic'];
							$node['classGeneric'] = $node['deviceClass']['generic'];
							$node['classSpecific'] = $node['deviceClass']['specific'];
							$node['deviceIdNew'] = $node['manufacturerId'] . '-' . $node['productType'] . '-' . $node['productId'];
							$devClassFile = realpath(dirname(__FILE__) . '/../../resources/zwavejs2mqtt/node_modules/@zwave-js/config/config/deviceClasses.json');
							if (file_exists($devClassFile)) {
								$string = file_get_contents($devClassFile);
								$pattern = '/(((?<!http:|https:)\/\/.*|(\/\*)([\S\s]*?)(\*\/)))/im';
								$json = json_decode(preg_replace($pattern, '', $string), true);
								$basic = '0x' . str_pad(dechex(intval($node['deviceClass']['basic'])), 2, '0', STR_PAD_LEFT);
								$generic = '0x' . str_pad(dechex(intval($node['deviceClass']['generic'])), 2, '0', STR_PAD_LEFT);
								$specific = '0x' . str_pad(dechex(intval($node['deviceClass']['specific'])), 2, '0', STR_PAD_LEFT);
								if (isset($json['basic'][$basic])) {
									$node['classBasic'] = $json['basic'][$basic];
								}
								if (isset($json['generic'][$generic])) {
									$node['classGeneric'] = $json['generic'][$generic]['label'];
									if (isset($json['generic'][$generic]['specific'][$specific])) {
										$node['classSpecific'] = $json['generic'][$generic]['specific'][$specific]['label'];
									}
								}
							}
							$eqLogic = self::byLogicalId($node['id'], __CLASS__);
							$node['confJeedom'] = '-';
							if (is_object($eqLogic)) {
								$path = $eqLogic->getConfFilePath();
								if (is_file(dirname(__FILE__) . '/../config/devices/' . $path)) {
									$node['confJeedom'] = $path;
								}
								$device = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $eqLogic->getConfFilePath()), array());
								$node['confType'] = 'Configuration Jeedom <br>';
								if (isset($device['properties']) && count($device['properties']) > 0) {
									if (isset($device['firmProperties']) && $device['firmProperties'] == 1) {
										$found = false;
										foreach ($device['properties'] as $firm => $property) {
											if ($firm != 'default') {
												if (evaluate($eqLogic->getConfiguration('firmwareVersion') . $firm) === true) {
													$device['properties'] = $property;
													$found = true;
													break;
												}
											}
										}
										if (!$found) {
											$device['properties'] = $device['properties']['default'];
										}
									}
									$node['confType'] .= ' Properties : <br>';
									foreach ($device['properties'] as $property => $value) {
										if (isset($value['mode']) && $value['mode'] != $eqLogic->getConfiguration('confMode', '')) {
											continue;
										}
										$node['confType'] .= '  - ' . $property . ' : ' . json_encode($value) . '<br>';
									}
								}
								if (isset($device['commands']) && count($device['commands']) > 0) {
									$node['confType'] .= 'Commands : <br>';
									foreach ($device['commands'] as $command) {
										$node['confType'] .= '  - ' . $command['name'] . '<br>';
									}
								}
								try {
									$node['lastWakeup'] = 'N/A';
									$node['nextWakeup'] = 'N/A';
									$node['configWakeup'] = 'N/A';
									$configWakeup = $node['values']['132-0-wakeUpInterval']['value'];
									$node['configWakeup'] = self::secondsToTime($configWakeup);
									if ($eqLogic->getConfiguration('lastWakeUp', '') != '') {
										$lastWakeUp = time() - $eqLogic->getConfiguration('lastWakeUp', '');
										$node['lastWakeup'] = self::secondsToTime($lastWakeUp);
										if ($lastWakeUp > $configWakeup) {
											$node['nextWakeup'] = '- ' . self::secondsToTime($lastWakeUp - $configWakeup);
										} else {
											$node['nextWakeup'] = self::secondsToTime($configWakeup - $lastWakeUp);
										}
									}
								} catch (Exception $e) {
								}
							}
							self::addFileEvent('getNodeInfo' . $node['id'], $node);
						}
					}
				} else if ($value['origin']['type'] == 'getNodeValues') {
					foreach ($value['result'] as $node) {
						if ($node['id'] == $value['origin']['node']) {
							$values['id'] = $node['id'];
							$values['status'] = $node['status'];
							$values['nodeValues'] = self::constructValuePage($node['id'], $node['values'], $node['status']);
							self::addFileEvent('getNodeValues' . $node['id'], $values);
						}
					}
				} else if ($value['origin']['type'] == 'group') {
					$data = array();
					foreach ($value['result'] as $node) {
						$data[$node['id']] = array('groups' => $node['groups'], 'label' => $node['productLabel'], 'endpoints' => $node['endpointIndizes'], 'status' => $node['status']);
					}
					self::addFileEvent('getNodeGroup', $data);
				} else if ($value['origin']['type'] == 'health') {
					$healthData = array();
					foreach ($value['result'] as $node => $values) {
						$healthData[$values['id']] = $values;
					}
					$data = self::constructHealthPage($healthData);
					self::addFileEvent('getHealthPage', $data);
				} else if ($value['origin']['type'] == 'healthMobile') {
					$healthData = array();
					foreach ($value['result'] as $node => $values) {
						$healthData[$values['id']] = $values;
					}
					$data = self::constructHealthPage($healthData, true);
					self::addFileEvent('getHealthPageMobile', $data);
				} else if ($value['origin']['type'] == 'syncValues') {
					foreach ($value['result'] as $node) {
						if ($node['id'] == $value['origin']['node']) {
							$eqLogic = self::byLogicalId($node['id'], __CLASS__);
							if (is_object($eqLogic)) {
								$eqLogic->handleCommandUpdate($node['values'], true);
							}
						}
					}
				}
			} else if ($key == 'getAssociations') {
				if ($value['origin']['type'] == 'getNodeAssociations') {
					self::addFileEvent('getNodeAssociations', array('id' => $value['origin']['node'], 'data' => $value['result']));
				}
			}
		}
	}

	public static function handleNode($_node) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Traitement d'un node");
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . json_encode($_node));
		foreach ($_node as $key => $value) {
			// log::add(__CLASS__, 'debug', $key);
			if ($key == 'node_value_updated') {
				self::handleNodeValueUpdate($value);
			} elseif ($key == 'node_notification') {
				self::handleNodeNotification($value);
			} else if ($key == 'node_interview_stage_completed') {
				self::createEqLogic($value['data'][0]);
			} else if ($key == 'node_interview_completed') {
				self::createEqLogic($value['data'][0]);
			} else if ($key == 'node_firmware_update_progress') {
				$nodeData = $value['data'][0];
				$updateData = $value['data'][1];
				event::add('zwavejs::firmware_update', array('node' => $nodeData['id'], 'progress' => $updateData['progress'], 'files' => $updateData['currentFile'] . '/' . $updateData['totalFiles']));
			}
		}
	}

	public static function handleController($_controller) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Traitement du contrôleur');
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . json_encode($_controller));
		foreach ($_controller as $key => $value) {
			// log::add(__CLASS__, 'debug', $key);
			if ($key == 'inclusion_started') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Inclusion démarrée', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Inclusion démarrée, celle-ci restera active 60 secondes', __FILE__), 'type' => 'inclusion')
				);
				config::save('controllerStatus', 'inclusion', __CLASS__);
			} else if ($key == 'inclusion_stopped') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Inclusion arrêtée', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Inclusion arrêtée', __FILE__), 'type' => 'empty')
				);
				config::save('controllerStatus', 'none', __CLASS__);
			} else if ($key == 'inclusion_failed') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Inclusion échouée', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Inclusion échouée', __FILE__), 'type' => 'empty')
				);
				config::save('controllerStatus', 'none', __CLASS__);
			} else if ($key == 'inclusion_aborted') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Inclusion annulée', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Inclusion annulée', __FILE__), 'type' => 'empty')
				);
				config::save('controllerStatus', 'none', __CLASS__);
			} else if ($key == 'exclusion_started') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Exclusion démarrée', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Exclusion démarrée, celle-ci restera active 60 secondes', __FILE__), 'type' => 'exclusion')
				);
				config::save('controllerStatus', 'exclusion', __CLASS__);
			} else if ($key == 'exclusion_stopped') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Exclusion arrêtée', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Exclusion arrêtée', __FILE__), 'type' => 'empty')
				);
				config::save('controllerStatus', 'none', __CLASS__);
			} else if ($key == 'exclusion_failed') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Exclusion echouée', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Exclusion échouée', __FILE__), 'type' => 'empty')
				);
				config::save('controllerStatus', 'none', __CLASS__);
				self::deamon_start();
			} else if ($key == 'grant_security_classes') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Accorder la securité', __FILE__));
				$securityClasses = $value['data'][0]['securityClasses'];
				$clientSideAuth = $value['data'][0]['clientSideAuth'];
				event::add('zwavejs::grant_security_classes', array('classes' => $securityClasses, 'auth' => $clientSideAuth));
			} else if ($key == 'validate_dsk') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Validation DSK', __FILE__));
				event::add('zwavejs::validate_dsk', $value['data'][0]);
			} else if ($key == 'node_removed') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Nœud exclu', __FILE__));
				$id = $value['data'][0]['id'];
				$eqLogic = self::byLogicalId($id, __CLASS__);
				if (is_object($eqLogic)) {
					$id = $id .  $eqLogic->getHumanName();
				}
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Nœud Exclu', __FILE__) . ' : ' . $id, 'type' => 'empty')
				);
				config::save('controllerStatus', 'none', __CLASS__);
				if (config::byKey('autoRemoveExcludeDevice', __CLASS__) == 1) {
					if (is_object($eqLogic)) {
						$eqLogic->remove();
						event::add('zwavejs::includeDevice', '');
					}
				}
			} else if ($key == 'node_added') {
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Nœud inclus', __FILE__));
				event::add(
					'zwavejs::inclusion',
					array('message' => __('Nœud inclus', __FILE__) . ' : ' . $value['data'][0]['id'], 'type' => 'empty')
				);
				config::save('controllerStatus', 'none', __CLASS__);
				$eqLogic = self::byLogicalId($value['data'][0]['id'], __CLASS__);
				if (!is_object($eqLogic)) {
					event::add('jeedom::alert', array(
						'level' => 'warning',
						'page' => __CLASS__,
						'message' => __("Nouveau module Z-Wave détecté. L'équipement sera créé lorsque l'interview sera terminé.", __FILE__),
					));
				}
			}
		}
	}

	public static function handleNodeNotification($_value_update) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Traitement d'une Notification d'un node");
		//log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . json_encode($_value_update));
		$datas = $_value_update['data'];
		$node = $datas[0];
		$cc = $datas[1];
		$change = $datas[2];
		$eqLogic = self::byLogicalId($node['id'], __CLASS__);
		if (is_object($eqLogic)) {
			if ($eqLogic->getIsEnable()) {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Le nœud avec l'id : " . $node['id'] . ' existe ' . $eqLogic->getHumanName());
			}
			$eqLogic->handleNotificationUpdate($change, $cc);
		}
	}

	public static function handleNodeValueUpdate($_value_update) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Traitement d'un update de value d'un node");
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . json_encode($_value_update));
		$datas = $_value_update['data'];
		$node = $datas[0];
		$change = $datas[1];
		$eqLogic = self::byLogicalId($node['id'], __CLASS__);
		if (is_object($eqLogic)) {
			if ($eqLogic->getIsEnable()) {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Le nœud avec l'id : " . $node['id'] . ' existe ' . $eqLogic->getHumanName());
			}
			$eqLogic->handleCommandUpdate($change);
		}
	}

	public static function handleNodeValueUpdateDirect($_nodeId, $_value_update) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Traitement d'un update de value d'un node direct");
		//log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_nodeId . ' ' . json_encode($_value_update));
		$eqLogic = self::byLogicalId($_nodeId, __CLASS__);
		$flatten = self::flatten_array($_value_update);
		// log::add(__CLASS__, 'debug', json_encode($flatten, true));
		if (is_object($eqLogic)) {
			if ($eqLogic->getIsEnable()) {
				// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Le nœud avec l'id : " . $_nodeId . ' existe ' . $eqLogic->getHumanName());
				foreach ($flatten as $key => $data) {
					if ($key == 'status') {
						$currentValue = $eqLogic->getCmd(null, '0-0-nodeStatus')->execCmd();
						$eqLogic->updateCmd('0-0-nodeStatus', $data['status']);
						if ($data['status'] == 'Awake') {
							$eqLogic->setConfiguration('lastWakeUp', time());
							if ($eqLogic->getConfiguration('missedWakeup', false)) {
								$action = '<a href="/' . $eqLogic->getLinkToConfiguration() . '">' . __('Equipement', __FILE__) . '</a>';
								if (config::byKey('notifyMissWakeup', __CLASS__, 1) == 1 && $eqLogic->getIsEnable() == 1) {
									if (version_compare(jeedom::version(), '4.4.0', '>=')) {
										message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ', vient de se réveiller après avoir raté au minimum 4 réveils.', $action, 'Awake-' . $eqLogic->getLogicalId(), true, 'alerting');
									} else {
										message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ', vient de se réveiller après avoir raté au minimum 4 réveils.', $action, 'Awake-' . $eqLogic->getLogicalId(), true);
									}
								}
							}
							$eqLogic->setConfiguration('missedWakeup', false);
							$eqLogic->save();
						}
						if ($data['status'] == 'Dead' && $currentValue == 'Alive') {
							$action = '<a href="/' . $eqLogic->getLinkToConfiguration() . '">' . __('Equipement', __FILE__) . '</a>';
							if (config::byKey('notifyDead', __CLASS__, 1) == 1 && $eqLogic->getIsEnable() == 1) {
								if (version_compare(jeedom::version(), '4.4.0', '>=')) {
									message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ', vient de passer au statut Dead.', $action, 'Dead-' . $eqLogic->getLogicalId(), true, 'alerting');
								} else {
									message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ', vient de passer au statut Dead.', $action, 'Dead-' . $eqLogic->getLogicalId(), true);
								}
							}
						}
						if ($data['status'] == 'Alive' && $currentValue == 'Dead') {
							$action = '<a href="/' . $eqLogic->getLinkToConfiguration() . '">' . __('Equipement', __FILE__) . '</a>';
							if (config::byKey('notifyDead', __CLASS__, 1) == 1 && $eqLogic->getIsEnable() == 1) {
								if (version_compare(jeedom::version(), '4.4.0', '>=')) {
									message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ', vient de passer au statut Alive.', $action, 'Alive-' . $eqLogic->getLogicalId(), true, 'alertingReturnBack');
								} else {
									message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ', vient de passer au statut Alive.', $action, 'Alive-' . $eqLogic->getLogicalId(), true);
								}
							}
						}
					} else if (isset($data['value'])) {
						$eqLogic->updateCmd($key, $data['value']);
					} else if (strpos($key, 'scene-')) {
						$eqLogic->updateCmd($key, 90);
					} else if ($key == '51-0-currentColor-value') {
						$eqLogic->updateCmd('51-0-currentColor', $data);
					}
				}
			}
		}
	}

	public static function publishMqttApi($_api_name, $_args = array()) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Publication Mqtt Api ' . $_api_name . ' ' . json_encode($_args));
		mqtt2::publish(config::byKey('prefix', __CLASS__, 'zwave') . '/_CLIENTS/ZWAVE_GATEWAY-Jeedom/api/' . $_api_name . '/set', $_args);
	}

	public static function publishMqttValue($_node, $_path, $_args = array()) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Publication Mqtt Value' . $_node . ' ' . $_path . ' ' . json_encode($_args));
		mqtt2::publish(config::byKey('prefix', __CLASS__, 'zwave') . '/' . $_node . '/' . $_path . '/set', $_args);
	}

	public static function cleanHistory() {
		foreach (self::byType(__CLASS__) as $eqLogic) {
			$eqLogic->setCache('waiting', array());
		}
		return;
	}

	public static function getInfo() {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Demande d'info");
		self::publishMqttApi('getInfo', array('type' => 'getInfo'));
	}

	public static function getNodeInfo($_nodeId, $_type = 'getNodeInfo') {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Demande d'info d'un Node " . $_nodeId);
		self::publishMqttApi('getNodes', array('type' => $_type, 'node' => $_nodeId));
	}

	public static function getNodeValues($_nodeId) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Demande d'info d'un Node " . $_nodeId);
		self::publishMqttApi('getNodes', array('type' => 'getNodeValues', 'node' => $_nodeId));
	}

	public static function getNodeAssociations($_nodeId) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . "Demande des associations d'un Node " . $_nodeId);
		$args = array("args" => array(intval($_nodeId)));
		$args['type'] = 'getNodeAssociations';
		$args['node'] = $_nodeId;
		self::publishMqttApi('getAssociations', $args);
	}

	public static function getNodes($_mode = '') {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Demande des nodes ');
		if ($_mode == 'sync') {
			event::add(
				'zwavejs::sync',
				array('message' => __('Synchronisation en cours', __FILE__) . '...', 'type' => 'launched')
			);
		}
		self::publishMqttApi('getNodes', array('type' => $_mode));
	}

	public static function controllerAction($_type) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __("Exécution d'une action sur le contrôleur de type", __FILE__) . ' : ' . $_type);
		self::publishMqttApi($_type, array('type' => 'controllerAction'));
	}

	public static function namingAction($_nodeId) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Envoie des noms et locations pour le nœud', __FILE__) . ' ' . $_nodeId);
		$allEqLogics = array();
		if ($_nodeId == 'all') {
			$allEqLogics = self::byType(__CLASS__);
		} else {
			$eq = self::byLogicalId($_nodeId, __CLASS__);
			if (is_object($eq)) {
				$allEqLogics[] = $eq;
			}
		}
		foreach ($allEqLogics as $eqLogic) {
			try {
				$location = '';
				$objet = $eqLogic->getObject();
				if (is_object($objet)) {
					$location = $objet->getName();
				} else {
					$location = 'aucun';
				}
				$name = $eqLogic->getName();
				$eqLogic->setNameLocation($name, $location);
			} catch (Exception $e) {
			}
		}
	}

	public static function nodeAction($_type, $_nodeId) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __("Exécution d'une action sur le nœud", __FILE__) . ' ' . $_nodeId . ' ' . __('de type', __FILE__) . ' : ' . $_type);
		$args = array('args' => array(intval($_nodeId)), 'type' => 'nodeAction');
		if ($_type == 'syncValues') {
			self::publishMqttApi('getNodes', array('type' => 'syncValues', 'node' => $_nodeId));
		} else {
			self::publishMqttApi($_type, $args);
		}
	}

	public static function setNodeValue($_fullpath, $_value) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_fullpath . ' ' . $_value);
		$detailsPath = explode('-', $_fullpath, 2);
		$value = $_value;
		if (strpos($_fullpath, 'userCode') !== false) {
			if (strpos($_value, '0x') !== false) {
				$value = array("type" => "Buffer", "data" => array_map('hexdec', str_split(str_replace('0x', '', $_value), 2)));
			} else {
				$value = '"' . $_value . '"';
			}
		}
		self::publishMqttValue($detailsPath[0], str_replace('-', '/', $detailsPath[1]), $value);
		self::handleSetHistory($_fullpath, $_value);
	}

	public static function setPolling($_nodeId, $_cc, $_endpoint, $_property, $_value) {
		$eqLogic = self::byLogicalId($_nodeId, __CLASS__);
		if (is_object($eqLogic)) {
			$pollConfig = $eqLogic->getConfiguration('polling', array());
			$pollConfig[$_endpoint . '-' . $_cc . '-' . $_property] = $_value;
			$eqLogic->setConfiguration('polling', $pollConfig);
			$eqLogic->save();
		}
	}

	public static function handleSetHistory($_fullpath, $_value) {
		$elements = explode('-', str_replace('_', ' ', $_fullpath), 4);
		$eqLogic = self::byLogicalId($elements[0], __CLASS__);
		if (is_object($eqLogic)) {
			$class = $elements[1];
			$endpoint = $elements[2];
			$property = $elements[3];
			$logical = $class . '-' . $endpoint . '-' . $property;
			if (in_array($class, array('112', '132', '99'))) {
				$waiting = $eqLogic->getCache('waiting', array());
				$waiting[$logical] = array('value' => $_value, 'date' => date("d/m/Y H:i:s"));
				log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $logical . ' ' . $_value);
				$eqLogic->setCache('waiting', $waiting);
			}
		}
	}

	public static function removeAssociation($_nodeId, $_groupId, $_sourceEndpoint, $_targetEndpoint, $_assoNodeId) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_nodeId . ' ' . $_groupId . ' ' . $_sourceEndpoint . ' ' . $_targetEndpoint . ' ' . $_assoNodeId);
		$args = array();
		$node = array('nodeId' => intval($_nodeId), 'endpoint' => intval($_sourceEndpoint));
		if ($_targetEndpoint != 'Root') {
			$assoNode = array('nodeId' => intval($_assoNodeId), 'endpoint' => intval($_targetEndpoint));
		} else {
			$assoNode = array('nodeId' => intval($_assoNodeId));
		}
		$args = array('args' => array($node, intval($_groupId), array($assoNode)));
		self::publishMqttApi('removeAssociations', $args);
	}

	public static function removeAllAssociations($_nodeId) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_nodeId);
		$args = array('args' => array(intval($_nodeId)));
		self::publishMqttApi('removeAllAssociations', $args);
	}

	public static function addAssociation($_nodeId, $_group, $_target) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_nodeId . ' ' . $_group . ' ' . $_target);
		$args = array();
		$endpoint = explode('-', $_group)[0];
		$groupId = explode('-', $_group)[1];
		$targetEndpoint = explode('-', $_target)[0];
		$targetId = explode('-', $_target)[1];
		if ($endpoint != 'root') {
			$node = array('nodeId' => intval($_nodeId), 'endpoint' => intval($endpoint));
		} else {
			$node = array('nodeId' => intval($_nodeId));
		}
		if ($targetEndpoint != 'root') {
			$assoNode = array('nodeId' => intval($targetId), 'endpoint' => intval($targetEndpoint));
		} else {
			$assoNode = array('nodeId' => intval($targetId));
		}
		$args = array('args' => array($node, intval($groupId), array($assoNode)));
		self::publishMqttApi('addAssociations', $args);
	}

	public static function refreshNodeCC($_nodeId, $_cc) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_nodeId . ' ' . $_cc);
		$args = array('args' => array(intval($_nodeId), intval($_cc)));
		$args['type'] = 'refreshNodeCC';
		self::publishMqttApi('refreshCCValues', $args);
	}

	public static function inclusion($_method, $_options) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Démarrage du mode inclusion', __FILE__) . ' ' . $_method . ' ' . $_options);
		$args = array();
		if ($_method == 'include') {
			$api = 'startInclusion';
			$args = array('args' => array(intval($_options), array('forceSecurity' => false)));
		} else if ($_method == 'exclude') {
			$api = 'startExclusion';
		} else if ($_method == 'stop') {
			$api = 'stop' . $_options;
		}
		$args['type'] = 'inclusion';
		self::publishMqttApi($api, $args);
	}

	public static function grantSecurity($_security, $_auth) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Grant Security', __FILE__) . ' ' . $_security . ' ' . $_auth);
		$auth = false;
		$security = array();
		if ($_auth != "false") {
			$auth = true;
		}
		foreach ($_security as $class) {
			$security[] = intval($class);
		}
		$args = array('args' => array(array('securityClasses' => $security, 'clientSideAuth' => $auth)));
		self::publishMqttApi('grantSecurityClasses', $args);
	}

	public static function validateDSK($_dsk) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Validation DSK', __FILE__) . ' ' . $_dsk);
		$args = array('args' => array($_dsk));
		self::publishMqttApi('validateDSK', $args);
	}

	public static function abortInclusion() {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Annulation Inclusion', __FILE__));
		$args = array();
		self::publishMqttApi('abortInclusion', $args);
	}

	public static function syncNodes($_data) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Synchronisation des nœuds', __FILE__) . ' ' . json_encode($_data));
		event::add(
			'zwavejs::sync',
			array('message' => __('Découverte de', __FILE__) . ' ' . count($_data) . ' ' . __('nœud(s)', __FILE__), 'type' => 'running')
		);
		foreach ($_data as $node) {
			self::createEqLogic($node, true);
		}
		event::add(
			'zwavejs::sync',
			array('message' => __('Fin de la synchronisation', __FILE__), 'type' => 'finished')
		);
	}

	public static function createEqLogic($_node, $_ignoreEvent = false) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __('Création d\'un équipement', __FILE__) . ' ' . json_encode($_node));
		$eqLogic = self::byLogicalId($_node['id'], __CLASS__);
		$inited = $_node['inited'];
		$new = false;
		$refresh = false;
		if (!is_object($eqLogic)) {
			$eqLogic = new zwavejs();
			$eqLogic->setEqType_name(__CLASS__);
			$eqLogic->setIsEnable(1);
			$eqLogic->setLogicalId($_node['id']);
			$eqLogic->setIsVisible(1);
			$new = true;
			$refresh = true;
		}
		$wascomplete = $eqLogic->getConfiguration('interview', 'incomplete');
		if (($wascomplete == 'incomplete' && $inited) || ($inited && $new)) {
			if (($eqLogic->getName() == $eqLogic->getLogicalId() . ' - ' . 'Node inclus') || ($eqLogic->getName() == '')) {
				$eqLogic->setName($eqLogic->getLogicalId() . ' - ' . $_node['manufacturer'] . ' ' . $_node['productDescription'] . ' ' . $_node['productLabel']);
				$refresh = true;
			}
		} else if ($new) {
			$eqLogic->setName($eqLogic->getLogicalId() . ' - ' . 'Node inclus');
		}
		if ($inited === false) {
			if ($eqLogic->getConfiguration('interview', 'incomplete') != 'complete') {
				$eqLogic->setConfiguration('interview', 'incomplete');
			}
		} else {
			$eqLogic->setConfiguration('interview', 'complete');
		}
		$eqLogic->setConfiguration('manufacturer_id', $_node['manufacturerId']);
		$eqLogic->setConfiguration('product_type', $_node['productType']);
		$eqLogic->setConfiguration('product_id', $_node['productId']);
		$eqLogic->setConfiguration('product_name', $_node['productLabel'] . ' - ' . $_node['productDescription']);
		$eqLogic->setConfiguration('firmwareVersion', $_node['firmwareVersion']);
		$eqLogic->save();
		$eqLogic = self::byId($eqLogic->getId());
		if (!$_ignoreEvent) {
			if ($refresh) {
				event::add('zwavejs::includeDevice', $eqLogic->getId());
			}
		}
		if ($inited && $refresh) {
			$eqLogic->createCommand();
			$_node['values']['0-0-nodeStatus'] = array('value' => $_node['status']);
			$eqLogic->handleCommandUpdate($_node['values'], true);
			if (config::byKey('auto_applyRecommended', __CLASS__) == 1) {
				$eqLogic->applyRecommended();
			}
		}
		return $eqLogic;
	}

	public static function constructValuePage($_nodeId, $_values, $_nodeStatus) {
		$nodeValuesDict = array();
		$eqLogic = self::byLogicalId($_nodeId, __CLASS__);
		$waiting = $eqLogic->getCache('waiting', array());
		$nodeValues = '<div class="panel-group" id="accordionValues">';
		foreach ($_values as $key => $value) {
			$value['oriKey'] = $key;
			$value['ccName'] = $value['commandClassName'] . ' v' . $value['commandClassVersion'];
			$nodeValuesDict[intval($value['commandClass'])][] = $value;
		}
		ksort($nodeValuesDict);
		$updates = array();
		foreach ($nodeValuesDict as $cc => $datas) {
			$nodeValues .= '<div class="panel panel-default">';
			$nodeValues .= '<div class="panel-heading">';
			$nodeValues .= '<h3 class="panel-title cursor">';
			$nodeValues .= '<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionValues" href="#' . $datas[0]['commandClass'] . '">';
			$nodeValues .= '<i class="fas fa-circle-notch success" style="font-size:20px;"></i> <span style="font-size:18px;">' . $datas[0]['ccName'] . '<sub> (' . $cc . ')</sub></span></a>';
			$nodeValues .= '<i  class="refreshNodeCC fas fa-sync pull-right cursor" data-cc="' . $datas[0]['commandClass'] . '" data-nodeId="' . $_nodeId . '" title="' . __('Rafraîchir la CC', __FILE__) . '"></i>';
			$nodeValues .= '</h3>';
			$nodeValues .= '</div>';
			$nodeValues .= '<div id="' . $datas[0]['commandClass'] . '" class="panel-collapse collapse" aria-expanded="true">';
			$nodeValues .= '<div class="panel-body">';
			$nodeValues .= '<table id="' . $datas[0]['commandClass'] . 'Table" class="table table-condensed table-bordered">';
			$nodeValues .= '<tbody>';
			foreach ($datas as $data) {
				$nodeValues .= '<tr>';
				$nodeValues .= '<td>' . $data['endpoint'] . '</td>';
				if (isset($data['propertyKey'])) {
					$nodeValues .= '<td>' . $data['property'] . '-' . $data['propertyKey'] . '</td>';
					$prop = $data['property'] . '-' . $data['propertyKey'];
				} else {
					$nodeValues .= '<td>' . $data['property'] . '</td>';
					$prop = $data['property'];
				}
				$globProperty = $cc . '-' . $data['endpoint'] . '-' . $prop;
				if (isset($data['description'])) {
					$nodeValues .= '<td style="width:30%">' . $data['label'] . ' <sup><i class="fas fa-question-circle tooltips" title="' . $data['description'] . '"></i><sup></td>';
				} else {
					$nodeValues .= '<td style="width:30%">' . $data['label'] . '</td>';
				}
				$nodeValues .= '<td style="width:30%">';
				$waitingValue = '';
				if (isset($waiting[$globProperty])) {
					if ($waiting[$globProperty]['value'] == $data['value']) {
						unset($waiting[$globProperty]);
						$eqLogic->setCache('waiting', $waiting);
					} else {
						$waitingValue = $waiting[$globProperty]['value'];
					}
				}
				$tooltip = '';
				if ($data['readable']) {
					if (!isset($data['value'])) {
						$finalValue = 'N/A';
					} else if ($data['type'] == 'boolean') {
						$finalValue = __('OFF', __FILE__);
						if ($data['value']) {
							$finalValue = __('ON', __FILE__);
						}
					} else if ($data['type'] == 'string[]') {
						$finalValue = implode(' - ', $data['value']);
					} else if ($data['type'] == 'number') {
						if ($data['list']) {
							foreach ($data['states'] as $state) {
								if ($state['value'] == $data['value']) {
									$finalValue = $data['value'] . ' - ' . $state['text'];
									break;
								}
								$finalValue = $data['value'];
							}
						} else {
							$finalValue = $data['value'];
						}
					} else {
						if (is_array($data['value'])) {
							$finalValue = json_encode($data['value']);
						} else {
							$finalValue = $data['value'];
						}
					}
					if (isset($data['unit'])) {
						$finalValue .= ' ' . $data['unit'];
					}
					if (isset($data['states'])) {
						$tooltip .= 'Valeurs possibles : &#013;';
						foreach ($data['states'] as $state) {
							$tooltip .= $state['value'] . ' -> ' . $state['text'] . '&#013;';
						}
					}
					if (isset($data['min'])) {
						$tooltip .= 'min : ' . $data['min'] . '&#013;';
					}
					if (isset($data['max'])) {
						$tooltip .= 'max : ' . $data['max'] . '&#013;';
					}
					if (isset($data['default'])) {
						$tooltip .= 'défaut : ' . $data['default'] . '&#013;';
					}
				} else {
					if (isset($data['value'])) {
						$finalValue = $data['value'];
					} else {
						$finalValue = '-';
					}
				}
				$span = '';
				if ($finalValue && $finalValue == __('ON', __FILE__)) {
					$span .= '<span class="label label-success" style="font-size:1em;">';
				} else if ($finalValue && $finalValue == __('OFF', __FILE__)) {
					$span .= '<span class="label label-danger" style="font-size:1em;">';
				} else {
					$span .= '<span class="label label-primary" style="font-size:1em;" title="' . $tooltip . '">';
				}
				$span .= $finalValue . '</span>';
				if ($tooltip != '') {
					$span .= ' <sup><i class="fas fa-question-circle tooltips" title="' . $tooltip . '"></i></sup>';
				}
				if ($waitingValue != '') {
					$span .= ' <i class="fas fa-user-clock icon_orange" title="Paramètre demandé"><sup>' . $waitingValue . '</sup></i>';
				}
				$updates[str_replace(' ', '_', $data['id'])] = array('value' => $span);
				$nodeValues .= '<span class="' . str_replace(' ', '_', $data['id']) . '">' . $span . '</span>';
				if ($data['writeable']) {
					$nodeValues .= ' <a class="btn btn-xs btn-primary editValue pull-right"';
					$nodeValues .= ' data-type="' . $data['type'] . '"';
					if ($data['list']) {
						$states = '';
						foreach ($data['states'] as $state) {
							$states .= $state['value'] . '-' . $state['text'] . ';';
						}
					}
					$nodeValues .= ' data-states="' . substr($states, 0, -1) . '"';
					$nodeValues .= ' data-label="' . $data['label'] . '"';
					$nodeValues .= ' data-list="' . $data['list'] . '"';
					$nodeValues .= ' data-path="' . $data['id'] . '"';
					$nodeValues .= ' style="text-align: right;display:inline-block"><i class="fas fa-wrench"></i></a>';
				}
				$nodeValues .= '</td>';
				$nodeValues .= '<td class="' . str_replace(' ', '_', $data['id']) . '_lastUpdate">' . date("d/m/Y H:i:s", $data['lastUpdate'] / 1000) . '</td>';
				$updates[str_replace(' ', '_', $data['id'])]['lastUpdate'] = date("d/m/Y H:i:s", $data['lastUpdate'] / 1000);
				$nodeValues .= '<td>';
				$currentPolling = 'Aucun';
				if (is_object($eqLogic)) {
					$polling = $eqLogic->getConfiguration('polling', array());
					if (isset($polling[$data['endpoint'] . '-' . $cc . '-' . $prop])) {
						$currentPolling = $polling[$data['endpoint'] . '-' . $cc . '-' . $prop];
					}
				}
				$sub = '';
				if ($currentPolling == 'Aucun') {
					$color = 'btn-warning';
				} else {
					$color = 'btn-danger';
					$sub = '<sub>' . $currentPolling . '</sub>';
				}
				if ($data['readable'] && $_nodeStatus == 'Alive') {
					$nodeValues .= '<span class="' . str_replace(' ', '_', $data['id']) . '_Poll"><a class="btn btn-xs ' . $color . ' configPolling pull-right cursor" data-valueid="' . $data['id'] . '" data-property="' . $prop . '" data-currentpolling="' . $currentPolling . '" data-endpoint="' . $data['endpoint'] . '" data-cc="' . $datas[0]['commandClass'] . '" data-nodeid="' . $_nodeId . '" data-label="' . $data['label'] . '<sub> (' . $cc . '-' . $data['endpoint'] . ')</sub>" title="' . __('Configurer le Polling', __FILE__) . '"><i class="fas fa-wrench"></i>' . $sub . '</a></span>';
					$updates[str_replace(' ', '_', $data['id'])]['poll'] = '<a class="btn btn-xs ' . $color . ' configPolling pull-right cursor" data-valueid="' . $data['id'] . '" data-property="' . $prop . '" data-currentpolling="' . $currentPolling . '" data-endpoint="' . $data['endpoint'] . '" data-cc="' . $datas[0]['commandClass'] . '" data-nodeid="' . $_nodeId . '" data-label="' . $data['label'] . '<sub> (' . $cc . '-' . $data['endpoint'] . ')</sub>" title="' . __('Configurer le Polling', __FILE__) . '"><i class="fas fa-wrench"></i>' . $sub . '</a>';
				}
				if ($data['readable'] && (in_array($data['type'], array('number', 'boolean')))) {
					$nodeValues .= ' <a class="btn btn-xs btn-info createCommandInfo pull-right"';
					$nodeValues .= ' data-type="' . $data['type'] . '"';
					$nodeValues .= ' data-label="' . $data['label'] . '"';
					$nodeValues .= ' data-path="' . $data['id'] . '"';
					if (isset($data['unit'])) {
						$nodeValues .= ' data-unit="' . $data['unit'] . '"';
					} else {
						$nodeValues .= ' data-unit=""';
					}
					$nodeValues .= ' data-max="' . $data['max'] . '"';
					$nodeValues .= ' data-min="' . $data['min'] . '"';
					$nodeValues .= ' data-value="' . $data['value'] . '"';
					$nodeValues .= ' style="text-align: right;display:inline-block" title="' . __("Créer la commande Info correspondante dans l'équipement Jeedom", __FILE__) . '"><i class="fas fa-marker"></i></a>';
				}
				if ($data['writeable'] && (in_array($data['type'], array('number', 'boolean')))) {
					$nodeValues .= ' <a class="btn btn-xs btn-info createCommandAction pull-right"';
					$nodeValues .= ' data-type="' . $data['type'] . '"';
					$nodeValues .= ' data-label="' . $data['label'] . '"';
					$nodeValues .= ' data-path="' . $data['id'] . '"';
					if (isset($data['unit'])) {
						$nodeValues .= ' data-unit="' . $data['unit'] . '"';
					} else {
						$nodeValues .= ' data-unit=""';
					}
					$nodeValues .= ' data-max="' . $data['max'] . '"';
					$nodeValues .= ' data-min="' . $data['min'] . '"';
					$nodeValues .= ' data-value="' . $data['value'] . '"';
					$nodeValues .= ' style="text-align: right;display:inline-block" title="' . __("Créer la/les commande(s) Action correspondantes dans l'équipement Jeedom", __FILE__) . '"><i class="fas fa-pen"></i></a>';
				}
				$nodeValues .= '</td>';
			}
			$nodeValues .= '</tbody>';
			$nodeValues .= '</table>';
			$nodeValues .= '</div>';
			$nodeValues .= '</div>';
			$nodeValues .= '</div>';
		}
		$nodeValues .= '</div>';
		return array('init' => $nodeValues, 'updates' => $updates);
	}

	public static function constructHealthPage($_values, $_mobile = False) {
		$healthPage = '';
		ksort($_values);
		foreach ($_values as $node => $values) {
			if (!$_mobile) {
				$healthPage .= '<tr><td><span class="label label-primary">' . $values['id'] . '</span></td>';
				$eqLogic = self::byLogicalId($values['id'], __CLASS__);
				$productDetails = '<sup><i class="fas fa-question-circle tooltips" title="' . $values['manufacturer'] . ' ' . $values['productDescription'] . ' Firmware : ' . $values['firmwareVersion'] . '"></i><sup>';
				if (is_object($eqLogic)) {
					$image = 'plugins/zwavejs/core/config/devices/' . $eqLogic->getImgFilePath();
					if (!is_file(dirname(__FILE__) . '/../config/devices/' . $eqLogic->getImgFilePath())) {
						$image = 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
					}
					$healthPage .= '<td><img src="' . $image . '" height="40"/> <a href="index.php?v=d&p=zwavejs&m=zwavejs&id=' . $eqLogic->getId() . '">' . $eqLogic->getHumanName(true) .  '</a>' . ' ' . $productDetails . '</td>';
				} else {
					$healthPage .= '<td><img src="plugins/zwavejs/plugin_info/zwavejs_icon.png" height="40"/> ' . $values['productLabel'] . ' - ' . $values['productDescription'] . ' ' . $productDetails . '</td>';
				}
				$healthPage .= '<td><span class="label label-info" style="font-size : 1em;">' . $values['endpointsCount'] . '</span></td>';
				if (isset($values['isSecure']) && $values['isSecure']) {
					if (isset($values['security']) && $values['security']) {
						$secure = '<span title="Secure" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span> <sup><i class="fas fa-question-circle tooltips" title="' . $values['security'] . '"></i><sup>';
					} else {
						$secure = '<span title="Non Secure" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
					}
				} else {
					$secure = '<span title="Non Secure" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
				}
				$healthPage .= '<td>' . $secure . '</td>';

				if ($values['isFrequentListening']) {
					$flirs = '<span title="Flirs" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span>';
				} else {
					$flirs = '<span title="Non Flirs" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
				}
				$healthPage .= '<td>' . $flirs . '</td>';

				if (isset($values['zwavePlusVersion']) && $values['zwavePlusVersion']) {
					$zwavePlusVersion = '<span title="ZwavePlus" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span> <sup><i class="fas fa-question-circle tooltips" title="v' . $values['zwavePlusVersion'] . '"></i><sup>';
				} else {
					$zwavePlusVersion = '<span title="Non ZwavePlus" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
				}
				$healthPage .= '<td>' . $zwavePlusVersion . '</td>';

				if ($values['isRouting']) {
					$isRouting = '<span title="Routing" style="font-size : 1.5em;"><i class="fas fa-check icon_green" aria-hidden="true"></i></span>';
				} else {
					$isRouting = '<span title="No Routing" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
				}
				$healthPage .= '<td>' . $isRouting . '</td>';
				$numberPoll = 0;
				if (is_object($eqLogic)) {
					$currentPolling = $eqLogic->getConfiguration('polling', array());
					foreach ($currentPolling as $key => $value) {
						if ($value != 'Aucun') {
							$numberPoll += 1;
						}
					}
				}
				if ($numberPoll == 0) {
					$polling = '<span title="Nombre de polling actif 0" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span>';
				} else {
					$polling = '<span title="Nombre de polling actif" class="label label-warning" style="font-size : 1em;">' . $numberPoll . '</span>';
				}
				$healthPage .= '<td>' . $polling . '</td>';

				$numberRefresh = 0;
				if (is_object($eqLogic)) {
					$refreshes = $eqLogic->getConfiguration('refreshes', array());
					foreach ($refreshes as $key => $value) {
						$numberRefresh += 1;
					}
				}
				if ($numberRefresh == 0) {
					$refresh = '<span title="Nombre de refresh actif 0" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span>';
				} else {
					$refresh = '<span title="Nombre de refresh actif" class="label label-warning" style="font-size : 1em;">' . $numberRefresh . '</span>';
				}
				$healthPage .= '<td>' . $refresh . '</td>';

				if ($values['inited']) {
					$inited = '<span title="Initié" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span>';
				} else {
					$inited = '<span title="Non initié" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_red" aria-hidden="true"></i></span>';
				}
				$healthPage .= '<td>' . $inited . '</td>';
				if ($values['status'] == 'Alive') {
					$status = '<span title="Alive" style="font-size : 1.5em;"><i class="fas fa-check icon_green" aria-hidden="true"></i></span>';
				} else if (($values['status'] == 'Dead')) {
					$status = '<span title="Dead" style="font-size : 1.5em;"><i class="fas fa-skull-crossbones icon_red" aria-hidden="true"></i></span>';
				} else if (($values['status'] == 'Awake')) {
					$status = '<span title="Awake" style="font-size : 1.5em;"><i class="fas fa-grin icon_green" aria-hidden="true"></i></span>';
				} else if (($values['status'] == 'Asleep')) {
					$status = '<span title="Sleeping" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">z<sup>z<sup>z</sup></sup></i></span>';
				} else {
					$status = '<span title="Other" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">' . $values['status'] . '</i></span>';
				}
				$healthPage .= '<td>' . $status . '</td>';

				$labelInterview = 'label-warning';
				if ($values['interviewStage'] == 'Complete') {
					$labelInterview = 'label-success';
				} else if ($values['interviewStage'] == 'ProtocolInfo') {
					$labelInterview = 'label-danger';
				}
				$healthPage .= '<td><span class="label ' . $labelInterview . '" style="font-size : 1em;">' . $values['interviewStage'] . '</span></td>';

				$healthPage .= '<td>' . date("d/m/Y H:i:s", $values['lastActive'] / 1000);
				$wakedup = 'N/A';
				if (is_object($eqLogic) && $eqLogic->getConfiguration('lastWakeUp', '') != '') {
					$wakedup = time() - $eqLogic->getConfiguration('lastWakeUp', '');
				}
				if (($values['status'] == 'Asleep') && $wakedup != 'N/A') {
					$healthPage .= '<br><i class="fas fa-grin icon_blue" title="' . __('Dernier réveil', __FILE__) . '" aria-hidden="true"></i> <span title="' . __('Dernier réveil', __FILE__) . '" style="font-size : 0.7em;">' . self::secondsToTime($wakedup) . '</span>';
					if (isset($values['values']['132-0-wakeUpInterval']['value'])) {
						if ($wakedup > $values['values']['132-0-wakeUpInterval']['value']) {
							$next = '- ' . self::secondsToTime($wakedup - $values['values']['132-0-wakeUpInterval']['value']);
						} else {
							$next = self::secondsToTime($values['values']['132-0-wakeUpInterval']['value'] - $wakedup);
						}
						if ($wakedup > 3 * $values['values']['132-0-wakeUpInterval']['value']) {
							$action = '<a href="/' . $eqLogic->getLinkToConfiguration() . '">' . __('Equipement', __FILE__) . '</a>';
							if (config::byKey('notifyMissWakeup', __CLASS__, 1) == 1 && $eqLogic->getIsEnable() == 1) {
								if (version_compare(jeedom::version(), '4.4.0', '>=')) {
									message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ", ne s'est pas reveillé au moins 4 fois. Il a peut être un problème (batterie ou autres).", $action, 'Wakeup-' . $eqLogic->getLogicalId(), true, 'alertingReturnBack');
								} else {
									message::add('zwavejs', "L'équipement : " . $eqLogic->getHumanName(true) . ' avec le nodeId : ' . $eqLogic->getLogicalId() . ", ne s'est pas reveillé au moins 4 fois. Il a peut être un problème (batterie ou autres).", $action, 'Wakeup-' . $eqLogic->getLogicalId(), true);
								}
							}
							$eqLogic->setConfiguration('missedWakeup', true);
							$eqLogic->save();
						}
						$healthPage .= '<br><i class="fas fa-arrow-right icon_blue" title="' . __('Prochain réveil estimé', __FILE__) . '" aria-hidden="true"></i> <span title="' . __('Prochain réveil estimé', __FILE__) . '" style="font-size : 0.7em;">' . $next . '</span>';
						$healthPage .= '<br><i class="fas fa-wrench icon_blue" title="' . __('Intervalle de réveil', __FILE__) . '" aria-hidden="true"></i> <span title="' . __('Intervalle de réveil', __FILE__) . '" style="font-size : 0.7em;">' . self::secondsToTime($values['values']['132-0-wakeUpInterval']['value']) . '</span>';
					}
				}
				$healthPage .= '</td>';
				$healthPage .= '<td><a class="btn btn-info btn-xs pingDevice" data-id="' . $values['id'] . '"><i class="fas fa-eye"></i> Ping</a></td>';
				$healthPage .= '</tr>';
			} else {
				$healthPage .= '<tr><td>' . $values['id'] . '</td>';
				$eqLogic = self::byLogicalId($values['id'], __CLASS__);
				if (is_object($eqLogic)) {
					$image = 'plugins/zwavejs/core/config/devices/' . $eqLogic->getImgFilePath();
					if (!is_file(dirname(__FILE__) . '/../config/devices/' . $eqLogic->getImgFilePath())) {
						$image = 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
					}
					$healthPage .= '<td><img src="' . $image . '" height="40"/>' . $eqLogic->getHumanName(true) . '</td>';
				} else {
					$healthPage .= '<td><img src="plugins/zwavejs/plugin_info/zwavejs_icon.png" height="40"/> ' . $values['productLabel'] . ' - ' . $values['productDescription'] . '</td>';
				}
				if ($values['status'] == 'Alive') {
					$status = '<span title="Alive" style="font-size : 1.5em;"><i class="fas fa-check icon_green" aria-hidden="true"></i></span>';
				} else if (($values['status'] == 'Dead')) {
					$status = '<span title="Dead" style="font-size : 1.5em;"><i class="fas fa-skull-crossbones icon_red" aria-hidden="true"></i></span>';
				} else if (($values['status'] == 'Awake')) {
					$status = '<span title="Awake" style="font-size : 1.5em;"><i class="fas fa-grin icon_green" aria-hidden="true"></i></span>';
				} else if (($values['status'] == 'Asleep')) {
					$status = '<span title="Sleeping" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">z<sup>z<sup>z</sup></sup></i></span>';
				} else {
					$status = '<span title="Other" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">' . $values['status'] . '</i></span>';
				}
				$healthPage .= '<td>' . $status . '</td>';
				$wakedup = 'N/A';
				if (is_object($eqLogic) && $eqLogic->getConfiguration('lastWakeUp', '') != '') {
					$wakedup = time() - $eqLogic->getConfiguration('lastWakeUp', '');
				}
				$healthPage .= '<td>' . date("d/m/Y H:i:s", $values['lastActive'] / 1000);
				if (($values['status'] == 'Asleep') && $wakedup != 'N/A') {
					$healthPage .= '<br><i class="fas fa-grin icon_blue" title="' . __('Dernier réveil', __FILE__) . '" aria-hidden="true"></i> <span title="' . __('Dernier réveil', __FILE__) . '" style="font-size : 0.7em;">' . self::secondsToTime($wakedup) . '</span>';
					if ($wakedup > $values['values']['132-0-wakeUpInterval']['value']) {
						$next = '- ' . self::secondsToTime($wakedup - $values['values']['132-0-wakeUpInterval']['value']);
					} else {
						$next = self::secondsToTime($values['values']['132-0-wakeUpInterval']['value'] - $wakedup);
					}
					$healthPage .= '<br><i class="fas fa-arrow-right icon_blue" title="' . __('Prochain réveil estimé', __FILE__) . '" aria-hidden="true"></i> <span title="' . __('Prochain réveil estimé', __FILE__) . '" style="font-size : 0.7em;">' . $next . '</span>';
					$healthPage .= '<br><i class="fas fa-wrench icon_blue" title="' . __('Intervalle de réveil', __FILE__) . '" aria-hidden="true"></i> <span title="' . __('Intervalle de réveil', __FILE__) . '" style="font-size : 0.7em;">' . self::secondsToTime($values['values']['132-0-wakeUpInterval']['value']) . '</span>';
				}
				$healthPage .= '</td>';
				$healthPage .= '</tr>';
			}
		}
		return $healthPage;
	}

	public static function autoCreateCommandInfo($_path, $_type, $_label, $_unit, $_max, $_min, $_currentValue) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . _("Création d'une commande info", __FILE__) . ' ' . $_path);
		$elements = explode('-', str_replace('_', ' ', $_path), 4);
		$eqLogic = self::byLogicalId($elements[0], __CLASS__);
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . print_r($elements, true));
		if (is_object($eqLogic)) {
			$class = $elements[1];
			$endpoint = $elements[2];
			$property = $elements[3];
			$logical = $class . '-' . $endpoint . '-' . $property;
			$command = $eqLogic->getCmd(null, $logical);
			if (!is_object($command)) {
				$command = new zwavejscmd();
				$command->setLogicalId($logical);
				$label = $_label . '-' . rand(0, 99999);
				$command->setName($label);
				$command->setIsVisible(0);
				$command->setEqLogic_id($eqLogic->getId());
				$command->setConfiguration('class', $class);
				$command->setConfiguration('endpoint', $endpoint);
				$command->setConfiguration('property', $property);
				$command->setType('info');
				if ($_type == 'boolean') {
					$command->setSubType('binary');
				} else if ($_type == 'number') {
					$command->setSubType('numeric');
					if ($_unit) {
						$command->setUnite($_unit);
					}
					if ($_max) {
						$command->setConfiguration('maxValue', $_max);
					}
					if ($_min) {
						$command->setConfiguration('minValue', $_min);
					}
				} else {
					$command->setSubType('string');
				}
				$command->save();
				$eqLogic->checkAndUpdateCmd($logical, $_currentValue);
			}
		}
	}

	public static function autoCreateCommandAction($_path, $_type, $_label, $_unit, $_max, $_min, $_currentValue) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . _("Création d'une commande action", __FILE__) . ' ' . $_path);
		$elements = explode('-', str_replace('_', ' ', $_path), 4);
		$eqLogic = self::byLogicalId($elements[0], __CLASS__);
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . print_r($elements, true));
		if (is_object($eqLogic)) {
			$class = $elements[1];
			$endpoint = $elements[2];
			$property = $elements[3];
			$logical = $class . '-' . $endpoint . '-' . $property;
			if ($_type == 'boolean') {
				$candidates = [array("name" => "On", "command" => "true"), array("name" => "Off", "command" => "false")];
				foreach ($candidates as $candidate) {
					$command = $eqLogic->getCmd(null, $logical . '-' . $candidate['command']);
					if (!is_object($command)) {
						$command = new zwavejscmd();
						$command->setLogicalId($logical . '-' . $candidate['command']);
						$label = $candidate['name'] . '-' . $_label . '-' . rand(0, 99999);
						$command->setName($label);
						$command->setIsVisible(0);
						$command->setEqLogic_id($eqLogic->getId());
						$command->setConfiguration('class', $class);
						$command->setConfiguration('endpoint', $endpoint);
						$command->setConfiguration('property', $property);
						$command->setConfiguration('value', $candidate['command']);
						$command->setType('action');
						$command->setSubType('other');
						$command->save();
					}
				}
			}
			if ($_type == 'number') {
				$command = $eqLogic->getCmd(null, $logical . '-#slider#');
				if (!is_object($command)) {
					$command = new zwavejscmd();
					$command->setLogicalId($logical . '-#slider#');
					$label = $_label . '-' . rand(0, 99999);
					$command->setName($label);
					$command->setIsVisible(0);
					$command->setEqLogic_id($eqLogic->getId());
					$command->setConfiguration('class', $class);
					$command->setConfiguration('endpoint', $endpoint);
					$command->setConfiguration('property', $property);
					$command->setConfiguration('value', "#slider#");
					$command->setType('action');
					$command->setSubType('slider');
					if ($_unit) {
						$command->setUnite($_unit);
					}
					if ($_max) {
						$command->setConfiguration('maxValue', $_max);
					}
					if ($_min) {
						$command->setConfiguration('minValue', $_min);
					}
					$command->save();
				}
			}
		}
	}

	public static function getWaiting() {
		$globWaiting = array();
		foreach (self::byType(__CLASS__) as $eqLogic) {
			$waitings = $eqLogic->getCache('waiting', array());
			if (is_object($eqLogic)) {
				$image = 'plugins/zwavejs/core/config/devices/' . $eqLogic->getImgFilePath();
				if (!is_file(dirname(__FILE__) . '/../config/devices/' . $eqLogic->getImgFilePath())) {
					$image = 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
				}
				foreach ($waitings as $property => $data) {
					$globWaiting[] = array(
						'id' => $eqLogic->getLogicalId(),
						'eqId' => $eqLogic->getId(),
						'image' => $image,
						'name' => $eqLogic->getHumanName(true),
						'property' => $property,
						'value' => $data['value'],
						'date' => $data['date']
					);
				}
			}
		}
		return $globWaiting;
	}
	/*     * *********************Methode d'instance************************* */

	public function handleCommandUpdate($_change, $_init = false) {
		if ($_init) {
			// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Init de valeur pour le node ' . $this->getHumanName());
			// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $this->getHumanName() . ' ' . json_encode($_change));
			foreach ($_change as $key => $value) {
				$this->updateCmd($key, $value['value']);
			}
		} else {
			// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Changement de valeur pour le node ' . $this->getHumanName());
			// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $this->getHumanName() . ' ' . json_encode($_change));
			$endpoint = 0;
			if (isset($_change['endpoint'])) {
				$endpoint = $_change['endpoint'];
			}
			$cmdId = $_change['commandClass'] . '-' . $endpoint . '-' . $_change['property'];
			if (isset($_change['propertyKey'])) {
				$cmdId .= '-' . $_change['propertyKey'];
			}
			// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Changement pour ' . $cmdId . ' ' . json_encode($_change));
			$this->updateCmd($cmdId, $_change['newValue']);
		}
	}

	public function handleNotificationUpdate($_change, $_cc) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Notification pour le node ' . $this->getHumanName());
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $this->getHumanName() . ' ' . json_encode($_change) . ' sur la CC ' . $_cc);
		foreach ($_change as $key => $value) {
			if ($key == 'parameters') {
				foreach ($value as $keyparam => $valueparam) {
					$this->updateCmd($_cc . '-' . '0-notification-' . $key . '-' . $keyparam, $valueparam);
				}
			} else {
				$this->updateCmd($_cc . '-' . '0-notification-' . $key, $value);
			}
		}
	}

	public function updateCmd($_cmdId, $_value) {
		$dictReplace = array(
			"Door-Window" => "Door/Window",
			"Air_temperature" => "Air temperature"
		);
		//log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_cmdId . ' ' . $_value);
		$waiting = $this->getCache('waiting', array());
		$_cmdId = str_replace('_', ' ', $_cmdId);
		$cmdId = explode('-', $_cmdId, 3);
		$class = $cmdId[0];
		$endpoint = (count($cmdId) > 1) ? $cmdId[1] : null;
		$property = (count($cmdId) > 2) ? $cmdId[2] : null;
		$value = $_value;
		if (isset($dictReplace[$property])) {
			$_cmdId = $class . '-' . $endpoint . '-' . $dictReplace[$property];
		}
		if ($property == 'hexColor') {
			$value = '#' . $value;
		}
		if ($property == 'currentColor') {
			$value = self::convertArrayToColor($value);
		}
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $this->getLogicalId() . '  :  ' . $class . ' ' . $endpoint . ' ' . $property . ' ' . $value);
		$startTime = config::byKey('lastStart', __CLASS__, 0);
		$cmd = $this->getCmd(null, $_cmdId);
		if ((time() - $startTime) < 30) {
			if (is_object($cmd)) {
				$returnStateTime = $cmd->getConfiguration('returnStateTime', 0);
				if ($returnStateTime != 0) {
					log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . __("Démon démarré depuis moins de 30 secondes et commande avec retour d'état Jeedom, on ignore", __FILE__) . ' (' . $this->getHumanName() . ' ' . $cmd->getName() . ')');
					return;
				}
			}
		}
		if (is_object($cmd)) {
			if ($cmd->getConfiguration('convertFaren', 0) == 1) {
				$value = round(($value - 32) * 5 / 9, 2);
			}
			$this->checkAndUpdateCmd($_cmdId, $value);
		}
		if ($class == '128' && $property == 'level' && $endpoint == '0') {
			$this->batteryStatus($value);
		}
		$propertyCheck = $property;
		if (strpos($property, 'scene-') !== false) {
			$propertyCheck = 'scene';
		}
		$label = self::getValueLabels($class . '-' . $propertyCheck, $value);
		if ($label) {
			// log::add(__CLASS__, 'debug', $label);
			$this->checkAndUpdateCmd($_cmdId . '-label', $label);
		}
		if (isset($waiting[$_cmdId])) {
			if ($waiting[$_cmdId]['value'] == $_value) {
				unset($waiting[$_cmdId]);
				$this->setCache('waiting', $waiting);
			}
		}
	}

	public function handleProperties($_device) {
		$device = $_device;
		if (!isset($device['commands'])) {
			$device['commands'] = array();
		}
		if (isset($device['firmProperties']) && $device['firmProperties'] == 1) {
			$found = false;
			foreach ($device['properties'] as $firm => $property) {
				if ($firm != 'default') {
					if (evaluate($this->getConfiguration('firmwareVersion') . $firm) === true) {
						$device['properties'] = $property;
						$found = true;
						break;
					}
				}
			}
			if (!$found) {
				$device['properties'] = $device['properties']['default'];
			}
		}
		foreach ($device['properties'] as $property => $details) {
			$propertyArray = explode('|', $property);
			$property = $propertyArray[0];
			if (!is_file(dirname(__FILE__) . '/../config/properties/' . strtolower($property) . '.json')) {
				continue;
			}
			$propertyjson = is_json(file_get_contents(dirname(__FILE__) . '/../config/properties/' . strtolower($property) . '.json'), false);
			if (!is_array($propertyjson)) {
				continue;
			}
			if (isset($details['mode']) && $details['mode'] != $this->getConfiguration('confMode', '')) {
				continue;
			}
			$type = 'standard';
			if (isset($details["type"])) {
				$type = $details["type"];
			}
			$ignore = array();
			if (isset($details["ignore"])) {
				$ignore = $details["ignore"];
			}
			$listCommand = array(1);
			if (isset($details['multi'])) {
				$listCommand = $details['multi'];
			}
			$multiName = false;
			if (count($listCommand) > 1) {
				$multiName = true;
			}
			$replace_array = array("#endpoint#" => 0);
			foreach ($listCommand as $numberCommand) {
				if (isset($propertyjson[$type])) {
					foreach ($propertyjson[$type] as $command) {
						if (in_array($command["name"], $ignore)) {
							log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $command["name"] . ' ' . __('sera ignoré', __FILE__));
							continue;
						}
						if (isset($command['configuration']['cmdFilter'])) {
							if (isset($details['cmdFilter'])) {
								$present = false;
								foreach ($details['cmdFilter'] as $filter) {
									if (in_array($filter, $command['configuration']['cmdFilter'])) {
										$present = true;
									}
								}
								if (!$present) {
									continue;
								}
							}
						}
						if (isset($details['replace'])) {
							foreach ($details['replace'] as $keyReplace => $valueReplace) {
								if ($valueReplace === 'multiKey') {
									if ($keyReplace == '#centralscene#') {
										$valueReplace = str_pad($numberCommand, 3, '0', STR_PAD_LEFT);
									} else {
										$valueReplace = $numberCommand;
									}
								}
								$replace_array[$keyReplace] = $valueReplace;
							}
						}
						foreach ($replace_array as $source => $target) {
							$command = json_decode(str_replace($source, $target, json_encode($command)), true);
						}
						if (isset($details['multiValue'])) {
							$command['configuration']['value'] = $details['multiValue'][$numberCommand];
						}
						if (isset($details['multiProperty'])) {
							$command['configuration']['property'] .= $details['multiProperty'][$numberCommand];
						}
						if ($multiName || !is_numeric($numberCommand)) {
							$command['name'] .= '-' . $numberCommand;
							if (isset($command['value'])) {
								$command['value'] .= '-' . $numberCommand;
							}
						}
						if (isset($details['split']) && $details['split'] == 1) {
							$command['name'] .= '-' . $propertyArray[1];
							if (isset($command['value'])) {
								$command['value'] .= '-' . $propertyArray[1];
							}
						}
						if (isset($details['isVisible'])) {
							$command['isVisible'] = $details['isVisible'];
						}
						if (isset($details['isHistorized'])) {
							$command['isHistorized'] = $details['isHistorized'];
						}
						if (isset($details['unite'])) {
							$command['unite'] = $details['unite'];
						}
						if (isset($details['returnStateTime'])) {
							$command['configuration']['returnStateTime'] = $details['returnStateTime'];
						}
						if (isset($details['returnStateValue'])) {
							$command['configuration']['returnStateValue'] = $details['returnStateValue'];
						}
						if (isset($details['repeatEventManagement'])) {
							$command['configuration']['repeatEventManagement'] = $details['repeatEventManagement'];
						}
						if (isset($details['calculValueOffset'])) {
							$command['configuration']['calculValueOffset'] = $details['calculValueOffset'];
						}
						if (isset($details['dashboard'])) {
							$command['template']['dashboard'] = $details['dashboard'];
						}
						if (isset($details['mobile'])) {
							$command['template']['mobile'] = $details['mobile'];
						}
						if (isset($details['generic_type'])) {
							$command['display']['generic_type'] = $details['generic_type'];
						}
						if (isset($details['maxValue'])) {
							$command['configuration']['maxValue'] = $details['maxValue'];
						}
						if (isset($details['minValue'])) {
							$command['configuration']['minValue'] = $details['maxValue'];
						}
						if (isset($details['name'])) {
							$command['name'] = $details['name'];
						}
						if (isset($details['filterVisible'])) {
							if (in_array($command['name'], $details['filterVisible']['commands'])) {
								$command['isVisible'] = $details['filterVisible']['value'];
							}
						}
						$device['commands'][] = $command;
					}
				}
			}
		}
		return $device;
	}

	public function loadCmdFromConf($_update = 0) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath())) {
			return;
		}
		$device = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath()), array());
		if (!is_array($device) || (!isset($device['commands']) && !isset($device['properties']))) {
			return true;
		}
		if (isset($device['modes'])) {
			if ($this->getConfiguration('confMode', '') == '') {
				$this->setConfiguration('confMode', array_key_first($device['modes']));
			}
		}
		if (isset($device['properties'])) {
			$device = self::handleProperties($device);
		}
		$commands = array();
		foreach ($device['commands'] as $command) {
			$command['logicalId'] = $command['configuration']['class'] . '-' . $command['configuration']['endpoint'] . '-' . $command['configuration']['property'];
			if (isset($command['configuration']['value'])) {
				$command['logicalId'] .= '-' . $command['configuration']['value'];
			}
			$commands[] = $command;
		}
		$device['commands'] = $commands;
		$device['commands'][] = array('logicalId' => '0-0-pingNode');
		$device['commands'][] = array('logicalId' => '0-0-healNode');
		$device['commands'][] = array('logicalId' => '0-0-nodeStatus');
		$device['commands'][] = array('logicalId' => '0-0-isFailedNode');
		if ($_update == 2) {
			$this->import($device, false);
		} else {
			$this->import($device, true);
		}
		sleep(1);
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => __CLASS__,
			'message' => '',
		));
	}

	public function setNameLocation($_name, $_location) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . '');
		$argName = array("args" => array(intval($this->getLogicalId()), $_name));
		$argLocation = array("args" => array(intval($this->getLogicalId()), $_location));
		if ($this->getConfiguration('name', '') != $argName) {
			self::publishMqttApi('setNodeName', $argName);
		}
		if ($this->getConfiguration('location', '') != $argLocation) {
			self::publishMqttApi('setNodeLocation', $argLocation);
		}
	}

	public function applyRecommended() {
		if (!$this->getIsEnable()) {
			return;
		}
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath())) {
			return;
		}
		$device = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath()), array());
		if (!is_array($device) || !isset($device['recommended'])) {
			return true;
		}
		if (isset($device['recommended']['params'])) {
			// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Param recommandé');
			foreach ($device['recommended']['params'] as $value) {
				$fullpath = $this->getLogicalId() . '-' . $value['path'];
				self::setNodeValue($fullpath, $value['value']);
				event::add(
					'zwavejs::recommended',
					array('message' => __('Application de la valeur', __FILE__) . ' ' . $value['value'] . ' ' . __('au paramètre', __FILE__) . ' ' . $value['path'])
				);
				sleep(1);
			}
		}
		if (isset($device['recommended']['groups'])) {
			// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . 'Groupe recommandé');
		}
		if (isset($device['recommended']['needswakeup']) && $device['recommended']['needswakeup'] == true) {
			return "wakeup";
		}
		return;
	}

	public function getEqLogicInfos() {
		$result = array();
		$result['interview'] = $this->getConfiguration('interview', false);
		$command_counter = 0;
		foreach ($this->getCmd() as $cmd) {
			if (!in_array($cmd->getLogicalId(), array('0-0-nodeStatus', '0-0-pingNode', '0-0-healNode', '0-0-isFailedNode'))) {
				$command_counter += 1;
			}
		}
		$result['command_counter'] = strval($command_counter);
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath())) {
			return $result;
		}
		$device = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath()), array());
		if (!is_array($device) || (!isset($device['commands']) && !isset($device['properties']))) {
			return $result;
		}
		if (isset($device['modes'])) {
			$result['modes'] = $device['modes'];
			$result['actualMode'] = $this->getConfiguration('confMode', '');
		} else {
			$result['modes'] = 'aucun';
		}
		if (isset($device['assistant'])) {
			$result['assistant'] = $device['assistant'];
		}
		$result['confType'] = 'Configuration Jeedom <br>';
		if (isset($device['properties']) && count($device['properties']) > 0) {
			if (isset($device['firmProperties']) && $device['firmProperties'] == 1) {
				$found = false;
				foreach ($device['properties'] as $firm => $property) {
					if ($firm != 'default') {
						if (evaluate($this->getConfiguration('firmwareVersion') . $firm) === true) {
							$device['properties'] = $property;
							$found = true;
							break;
						}
					}
				}
				if (!$found) {
					$device['properties'] = $device['properties']['default'];
				}
			}
			$result['confType'] .= 'Properties : <br>';
			foreach ($device['properties'] as $property => $value) {
				if (isset($value['mode']) && $value['mode'] != $this->getConfiguration('confMode', '')) {
					continue;
				}
				$result['confType'] .= '  -' . $property . ' : ' . json_encode($value) . '<br>';
			}
		}
		if (isset($device['commands']) && count($device['commands']) > 0) {
			$result['confType'] .= 'Commands : <br>';
			foreach ($device['commands'] as $command) {
				$result['confType'] .= '  -' . $command['name'] . '<br>';
			}
		}
		if (isset($device['recommended'])) {
			$result['recommended'] = $device['recommended'];
		}
		return $result;
	}

	public function postSave() {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ');

		$nodeStatus = $this->getCmd(null, '0-0-nodeStatus');
		if (!is_object($nodeStatus)) {
			$nodeStatus = new zwavejscmd();
			$nodeStatus->setLogicalId('0-0-nodeStatus');
			$nodeStatus->setName(__('Statut nœud', __FILE__));
			$nodeStatus->setIsVisible(0);
			$nodeStatus->setDisplay('icon', '<i class="fas fa-info"></i>');
		}
		$nodeStatus->setEqLogic_id($this->getId());
		$nodeStatus->setConfiguration('class', 0);
		$nodeStatus->setConfiguration('endpoint', 0);
		$nodeStatus->setConfiguration('property', 'nodeStatus');
		$nodeStatus->setType('info');
		$nodeStatus->setSubType('string');
		$nodeStatus->save();

		$pingNode = $this->getCmd(null, '0-0-pingNode');
		if (!is_object($pingNode)) {
			$pingNode = new zwavejscmd();
			$pingNode->setLogicalId('0-0-pingNode');
			$pingNode->setIsVisible(0);
			$pingNode->setDisplay('icon', '<i class="fas fa-sitemap"></i>');
			$pingNode->setName(__('Pinguer Nœud', __FILE__));
		}
		$pingNode->setType('action');
		$pingNode->setSubType('other');
		$pingNode->setConfiguration('class', 0);
		$pingNode->setConfiguration('endpoint', 0);
		$pingNode->setConfiguration('property', 'pingNode');
		$pingNode->setEqLogic_id($this->getId());
		$pingNode->save();

		$healNode = $this->getCmd(null, '0-0-healNode');
		if (!is_object($healNode)) {
			$healNode = new zwavejscmd();
			$healNode->setLogicalId('0-0-healNode');
			$healNode->setIsVisible(0);
			$healNode->setDisplay('icon', '<i class="fas fa-medkit"></i>');
			$healNode->setName(__('Soigner Nœud', __FILE__));
		}
		$healNode->setType('action');
		$healNode->setSubType('other');
		$healNode->setConfiguration('class', 0);
		$healNode->setConfiguration('endpoint', 0);
		$healNode->setConfiguration('property', 'healNode');
		$healNode->setEqLogic_id($this->getId());
		$healNode->save();

		$isFailed = $this->getCmd(null, '0-0-isFailedNode');
		if (!is_object($isFailed)) {
			$isFailed = new zwavejscmd();
			$isFailed->setLogicalId('0-0-isFailedNode');
			$isFailed->setDisplay('icon', '<i class="fas fa-heartbeat"></i>');
			$isFailed->setIsVisible(0);
			$isFailed->setName(__('Tester Nœud', __FILE__));
		}
		$isFailed->setType('action');
		$isFailed->setSubType('other');
		$isFailed->setConfiguration('class', 0);
		$isFailed->setConfiguration('endpoint', 0);
		$isFailed->setConfiguration('property', 'isFailedNode');
		$isFailed->setEqLogic_id($this->getId());
		$isFailed->save();
	}

	public function getConfFilePath($_all = false) {
		foreach (ls(dirname(__FILE__) . '/../config/devices', '*_' . $this->getConfiguration('manufacturer_id'), false, array('folders', 'quiet')) as $folder) {
			foreach (ls(dirname(__FILE__) . '/../config/devices/' . $folder, '*.json', false, array('files', 'quiet')) as $file) {
				$conf = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $folder . '/' . $file), array());
				if (!is_array($conf)) {
					continue;
				}
				if (isset($conf['versions']) && isset($conf['versions'][$this->getConfiguration('product_type')])) {
					if (in_array($this->getConfiguration('product_id'), $conf['versions'][$this->getConfiguration('product_type')])) {
						$return = $folder . $file;
						return $return;
					}
				}
			}
		}
		return false;
	}

	public function getImgFilePath() {
		$path = str_replace('.json', '', $this->getConfFilePath());
		if (is_file(dirname(__FILE__) . '/../config/devices/' . $path . '.png')) {
			return  $path . '.png';
		} else if (is_file(dirname(__FILE__) . '/../config/devices/' . $path . '.jpg')) {
			return  $path . '.jpg';
		}
		return false;
	}

	public function getImage() {
		$file = 'plugins/zwavejs/core/config/devices/' . $this->getImgFilePath();
		if (!is_file(__DIR__ . '/../../../../' . $file)) {
			return 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
		}
		return $file;
	}

	public function createCommand($_update = 0) {
		if (!is_numeric($this->getLogicalId())) {
			return;
		}
		if (is_file(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath())) {
			$this->loadCmdFromConf($_update);
			self::nodeAction('syncValues', $this->getLogicalId());
			return;
		}
	}

	public function pollValue($_class) {
		// log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_class);
		if (stripos($_class, '-value-') === false) {
			$command = explode('-', $_class, 3);
			$args = array('args' => array(array('nodeId' => intval($this->getLogicalId()), 'commandClass' => intval($command[1]), 'endpoint' => intval($command[0]), 'property' => $command[2])));
		} else {
			$command = explode('-', $_class, 4);
			$args = array('args' => array(array('nodeId' => intval($this->getLogicalId()), 'commandClass' => intval($command[1]), 'endpoint' => intval($command[0]), 'property' => $command[2], 'propertyKey' => intval($command[3]))));
		}
		log::add(__CLASS__, 'debug', $this->getHumanName() . '[' . __FUNCTION__ . '] ' . json_encode($args));
		self::publishMqttApi('pollValue', $args);
	}

	public function refreshIfNeeded($_path, $_value) {
		log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $_path . ' ' . $_value);
		$refreshes = $this->getConfiguration('refreshes', '');
		if (is_array($refreshes) && count($refreshes) > 0) {
			foreach ($refreshes as $refresh) {
				$source = $refresh['refresh::source'];
				if (strpos(str_replace('/', '-', $_path) . '-' . $_value, $source) !== false) {
					log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] Found refresh');
					$cmd = 'php ' . dirname(__FILE__) . '/../../core/php/refresher.php id=' . $this->getId();
					$cmd .= ' target=' . $refresh['refresh::target'] . '';
					$cmd .= ' sleep=' . $refresh['refresh::sleep'] . '';
					$cmd .= ' number=' . $refresh['refresh::number'] . '';
					$cmd .= ' >> ' . log::getPathToLog('zwavejs') . ' 2>&1 &';
					log::add(__CLASS__, 'debug', '[' . __FUNCTION__ . '] ' . $cmd);
					shell_exec($cmd);
					return;
				}
			}
		} else {
			return;
		}
	}
}

class zwavejsCmd extends cmd {

	public function preSave() {
		if ($this->getConfiguration('endpoint') === '') {
			$this->setConfiguration('endpoint', '0');
		}
		if ($this->getConfiguration('property') === '') {
			$this->setConfiguration('property', '0');
		}
		$logical = $this->getConfiguration('class') . '-' . $this->getConfiguration('endpoint') . '-' . $this->getConfiguration('property');
		if ($this->getConfiguration('value', '')) {
			$logical .= '-' . $this->getConfiguration('value', '');
		}
		$this->setLogicalId($logical);
	}

	public function execute($_options = array()) {
		if ($this->getType() != 'action') {
			return;
		}
		$value = $this->getConfiguration('value');
		$eqLogic = $this->getEqLogic();
		$node = $eqLogic->getLogicalId();
		$cc = $this->getConfiguration('class');
		$endpoint = $this->getConfiguration('endpoint');
		$property = $this->getConfiguration('property');
		$path = $cc . '/' . $endpoint . '/' . str_replace('-', '/', $property);
		if ($value == 'get') {
			if (is_numeric($property)) {
				$args = array('args' => array(array('nodeId' => intval($node), 'commandClass' => intval($cc)), 'get', array(intval($property))));
			} else {
				$args = array('args' => array(array('nodeId' => intval($node), 'commandClass' => intval($cc)), 'get', array($property)));
			}
			zwavejs::publishMqttApi('sendCommand', $args);
			return;
		}
		if ($property == 'sendReport') {
			$elements = explode('-', $value);
			$report = array();
			foreach ($elements as $element) {
				$elementArray = explode(':', $element);
				$report[$elementArray[0]] = intval($elementArray[1]);
			}
			$args = array('args' => array(array('nodeId' => intval($node), 'commandClass' => intval($cc)), 'sendReport', array($report)));
			zwavejs::publishMqttApi('sendCommand', $args);
			return;
		}
		if ($property == 'refreshNodeCC') {
			zwavejs::refreshNodeCC(intval($node), intval($cc));
			return;
		}
		if ($cc == 0 && $endpoint == 0) {
			$args = array('args' => array(intval($node)));
			zwavejs::publishMqttApi($property, $args);
			return;
		}
		switch ($this->getSubType()) {
			case 'message':
				$value = str_replace('#message#', $_options['message'], $value);
				break;
			case 'slider':
				$value = str_replace('#slider#', $_options['slider'], $value);
				break;
			case 'select':
				$value = str_replace('#select#', $_options['select'], $value);
				break;
			case 'color':
				if ($property == 'targetColor') {
					$value = zwavejs::convertColorToArray($_options['color']);
				} else {
					$value = strval(str_replace('#color#', $_options['color'], $value));
				}
		}
		if ($cc == 99) {
			$fullPath = $node . '-' . $cc . '-' . $endpoint . '-' . $property;
			$eqLogic->setNodeValue($fullPath, $value);
			return;
		}
		if (substr($value, 0, 3) == 'set') {
			$fullPath = $node . '-' . $cc . '-' . $endpoint . '-' . $property;
			$val = explode('-', $value, 2)[1];
			$eqLogic->setNodeValue($fullPath, $val);
			return;
		}
		zwavejs::publishMqttValue($node, $path, $value);
		$eqLogic->refreshIfNeeded($path, $value);
	}
}
