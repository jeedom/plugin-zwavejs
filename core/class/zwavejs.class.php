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
	/*     * *************************Attributs****************************** */
	
	public static $_excludeOnSendPlugin = array('zwavejs.log');
	
	/*     * ***********************Methode static*************************** */
	/*     * ********************************************************************** */
	/*     * ***********************zwavejs MANAGEMENT*************************** */
	
	function secondsToTime($seconds) {
		$dtF = new \DateTime('@0');
		$dtT = new \DateTime("@$seconds");
		return $dtF->diff($dtT)->format('%a j %h h %i min %s s');
	}
	
	public static function configureSettings($_path) {
		$file = $_path .'/settings.json';
		$settings = array();
		if (file_exists($file)) {
			unlink($file);
		}
		$settings['mqtt'] = array();
		$settings['gateway'] = array();
		$settings['zwave'] = array();
		
		$mqttInfos = mqtt2::getFormatedInfos();
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Informations reçues de mqtt2 : ' . json_encode($mqttInfos));
		
		zwavejs::isValidKey(config::byKey('s2key_access', 'zwavejs','')) ? true : config::save('s2key_access',zwavejs::generateRandomKey(), 'zwavejs');
		zwavejs::isValidKey(config::byKey('s2key_unauth', 'zwavejs','')) ? true : config::save('s2key_unauth',zwavejs::generateRandomKey(), 'zwavejs');
		zwavejs::isValidKey(config::byKey('s2key_auth', 'zwavejs','')) ? true : config::save('s2key_auth',zwavejs::generateRandomKey(), 'zwavejs');
		zwavejs::isValidKey(config::byKey('s0key', 'zwavejs','')) ? true : config::save('s0key',zwavejs::generateRandomKey(), 'zwavejs');
		
		$settings['mqtt']['name'] = 'Jeedom';
		$settings['mqtt']['host'] = $mqttInfos['ip'];
		$settings['mqtt']['port'] = $mqttInfos['port'];
		$settings['mqtt']['auth'] = true;
		$settings['mqtt']['username'] = $mqttInfos['user'];
		$settings['mqtt']['password'] = $mqttInfos['password'];
		$settings['mqtt']['prefix'] = config::byKey('prefix', 'zwavejs','zwave');
		//if ($port != 'auto') {
		//	$port = jeedom::getUsbMapping($port);
		//	exec(system::getCmdSudo() . 'chmod 777 ' . $port . ' > /dev/null 2>&1');
		//}
		$settings['zwave']['port'] = jeedom::getUsbMapping(config::byKey('port', 'zwavejs'));
		$settings['zwave']['commandsTimeout'] = 60;
		$settings['zwave']['logLevel'] = 'error';
		$settings['zwave']['logEnabled'] = true;
		$settings['zwave']['logToFile'] = false;
		$settings['zwave']['serverEnabled'] = false;
		$settings['zwave']['enableSoftReset'] = true;
		$settings['zwave']['disclaimerVersion'] = 1;
		$settings['zwave']['enableStatistics'] = false;
		$settings['zwave']['deviceConfigPriorityDir'] = realpath(dirname(__FILE__) . '/../config/config');
		$settings['zwave']['securityKeys'] =array('S2_AccessControl'=>config::byKey('s2key_access', 'zwavejs'),
													'S0_Legacy'=>config::byKey('s0key', 'zwavejs'),
													'S2_Unauthenticated'=>config::byKey('s2key_unauth', 'zwavejs'),
													'S2_Authenticated'=>config::byKey('s2key_auth', 'zwavejs')
											);
		
		$settings['gateway']['type'] = 0;
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
	
	public static function dependancy_info() {
		$return = array();
		$return['progress_file'] = jeedom::getTmpFolder(__CLASS__) . '/dependance';
		$return['state'] = 'ok';
		if (config::byKey('lastDependancyInstallTime', __CLASS__) == '') {
		$return['state'] = 'nok';
		}
		else if (!file_exists(__DIR__.'/../../resources/zwavejs2mqtt/node_modules')){
		$return['state'] = 'nok';
		}
		return $return;
	}
	
	public static function deamon_info() {
		$return = array();
		$return['log'] = 'zwavejs';
		$return['launchable'] = 'ok';
		$return['state'] = 'nok';
		if (self::isRunning()) {
			$return['state'] = 'ok';
		}
		$port = config::byKey('port', 'zwavejs');
		$port = jeedom::getUsbMapping($port);
		if (@!file_exists($port)) {
			$return['launchable'] = 'nok';
			$return['launchable_message'] = __('Le port n\'est pas configuré', __FILE__);
		}
		if (!class_exists('mqtt2')) {
			$return['launchable'] = 'nok';
			$return['launchable_message'] = __('Le plugin mqtt2 n\'est pas installé', __FILE__);
		} else {
			if (mqtt2::deamon_info()['state']!='ok'){
				$return['launchable'] = 'nok';
				$return['launchable_message'] = __('Le démon mqtt2 n\'est pas demarré', __FILE__);
			}
		}
		if (class_exists('openzwave')) {
			if (openzwave::deamon_info()['state'] =='ok'){
				$return['launchable'] = 'nok';
				$return['launchable_message'] = __('Le démon openzwave tourne. Il doit être coupé', __FILE__);
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
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Inscription au plugin mqtt2');
		config::save('controllerStatus','none','zwavejs');
		self::deamon_stop();
		mqtt2::addPluginTopic('zwavejs',config::byKey('prefix', 'zwavejs','zwave'));
		$deamon_info = self::deamon_info();
		if ($deamon_info['launchable'] != 'ok') {
			throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
		}
		$zwavejs_path = realpath(dirname(__FILE__) . '/../../resources/zwavejs2mqtt');
		$data_path = dirname(__FILE__) . '/../../data/store';
		if (!is_dir($data_path)) {
			mkdir($data_path, 0777, true);
		}
		$data_path = realpath(dirname(__FILE__) . '/../../data/store');
		self::configureSettings($data_path);
		chdir($zwavejs_path);
		$cmd = ''; 
		$cmd .= 'STORE_DIR='.$data_path;
		$cmd .= ' KEY_S0_Legacy='. config::byKey('s0key', 'zwavejs');
		$cmd .= ' KEY_S2_Unauthenticated='. config::byKey('s2key_unauth', 'zwavejs');
		$cmd .= ' KEY_S2_Authenticated='. config::byKey('s2key_auth', 'zwavejs');
		$cmd .= ' KEY_S2_AccessControl='. config::byKey('s2key_access', 'zwavejs');
		$cmd .= ' yarn start';
		log::add('zwavejs', 'info','[' . __FUNCTION__ . '] '. 'Lancement démon zwavejs : ' . $cmd);
		exec($cmd . ' >> ' . log::getPathToLog('zwavejsd') . ' 2>&1 &');
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
			log::add('zwavejs', 'error', 'Impossible de lancer le démon zwavejs, vérifiez la log', 'unableStartDeamon');
			return false;
		}
		message::removeAll('zwavejs', 'unableStartDeamon');
		log::add('zwavejs', 'info', 'Démon zwavejs lancé');
		return true;
	}
	
	public static function deamon_stop() {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Stop démon');
		config::save('controllerStatus','none','zwavejs');
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
			system::kill('server/bin/www.js',true);
			$i =0;
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
	
	public static function generateRandomKey(){
		$randHexStr = strtoupper(implode(array_map(function(){return dechex(mt_rand(0,15));}, array_fill(0,32,null))));
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$randHexStr);
		return $randHexStr;
	}
	
	public static function isValidKey($_key){
		if (!ctype_xdigit($_key)) {
			return false;
		}
		if (strlen($_key) != 32) {
			return false;
		}
		return true;
	}
	
	public static function handleMqttMessage($_message) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Message Mqtt reçu');
		log::add('zwavejs','debug', json_encode($_message));
		if (isset($_message[config::byKey('prefix', 'zwavejs','zwave')])){
			$message = $_message['zwave'];
		} else {
			log::add('zwavejs','debug','Le message reçu n\'est pas un message Zwave');
			return;
		}
		foreach ($message as $key => $value){
			if ($key == '_EVENTS'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Le message est un event');
				self::handleEvents($value);
			} else if ($key == '_CLIENTS'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Le message est une réponse api Client');
				self::handleClients($value);
			} else if (is_int($key)){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Le message est un event direct');
				self::handleNodeValueUpdateDirect($key,$value);
			} else if ($key == 'driver'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Le message est une info driver');
				if (isset($value['status'])){
					config::save('driverStatus',$value['status'],'zwavejs');
				}
			} else {
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Le message est de type inconnu');
			}
		}
	}
	
	public static function handleClients($_clients) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Traitement d\'un Client Api');
		log::add('zwavejs','debug', '[' . __FUNCTION__ . '] '.json_encode($_clients));
		$gateway = array_key_first($_clients);
		$client=$_clients[$gateway];
		foreach ($client as $key => $value){
			log::add('zwavejs','debug', $key);
			if ($key == 'api'){
				self::handleApi($value);
			}
		}
	}
	
	public static function handleEvents($_events) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Traitement d\'un Event');
		log::add('zwavejs','debug', '[' . __FUNCTION__ . '] '.json_encode($_events));
		$gateway = array_key_first($_events);
		$event=$_events[$gateway];
		foreach ($event as $key => $value){
			log::add('zwavejs','debug', $key);
			if ($key == 'node'){
				self::handleNode($value);
			} else if ($key == 'controller'){
				self::handleController($value);
			}
		}
	}
	
	public static function handleApi($_api) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Traitement d\'un retour api');
		log::add('zwavejs','debug', '[' . __FUNCTION__ . '] '.json_encode($_api));
		foreach ($_api as $key => $value){
			if ($key == 'getInfo'){
				event::add('zwavejs::getInfo',array($value['result']));
				if (isset($value['result']['controllerId'])){
					config::save('controllerId',$value['result']['controllerId'],'zwavejs');
				}
			} 
			else if ($key == 'getNodes'){
				if ($value['origin']['type'] == 'sync') {
					zwavejs::syncNodes($value['result']);
				} else if ($value['origin']['type'] == 'tree') {
					$tree = array();
					foreach ($value['result'] as $node){
						$tree[$node['id']]=$node;
					}
					event::add('zwavejs::getNodeTree',$tree);
				} else if ($value['origin']['type'] == 'stats'){
					$stats = array();
					$stats['totalNodes'] = count($value['result']);
					$sleepingNodes = 0;
					$networkTree = array('controllerId'=>config::byKey('controllerId','zwavejs',0),'data'=>array());
					$data = array();
					foreach ($value['result'] as $node){
						$data = $node;
						$eqLogic = zwavejs::byLogicalId($node['id'], 'zwavejs');
						if (is_object($eqLogic)) {
							$data['eqName'] = $eqLogic->getHumanName(true);
							$data['name'] = $eqLogic->getHumanName();
							$data['img'] = $eqLogic->getImage();
						} else {
							$data['img'] = 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
						}
						log::add('zwavejs','debug',json_encode($node));
						if ($node['id'] == config::byKey('controllerId','zwavejs',0)) {
							$stats['controllerNeighbors'] = implode($node['neighbors'],' - ');
							$stats['stats'] = $node['statistics'];
						}
						if ($node['status'] == 'Asleep'){
							$sleepingNodes += 1;
						}
						$networkTree['data'][$data['id']]=$data;
					}
					$stats['sleepingNodes'] = $sleepingNodes;
					$stats['networkTree'] = $networkTree;
					event::add('zwavejs::getNodeStats',$stats);
				} else if ($value['origin']['type'] == 'getNodeInfo'){
					foreach ($value['result'] as $node){
						if ($node['id']==$value['origin']['node']){
							$node['neighbors'] = implode($node['neighbors'],' - ');
							if (isset($node['deviceConfig']['filename']) && $node['deviceConfig']['filename'] != ''){
								$explodeFile = explode('/',$node['deviceConfig']['filename']);
								$fileExt = '(Jeedom)';
								if (in_array('@zwave-js', $explodeFile)){
									$fileExt = '(Zwave-Js)';
								}
								$node['filename'] = end($explodeFile) . ' '.$fileExt;
							} else {
								$node['filename'] = 'Aucun';
							}
							$node['numberGroups'] = count($node['groups']);
							$node['classBasic'] = $node['deviceClass']['basic'];
							$node['classGeneric'] = $node['deviceClass']['generic'];
							$node['classSpecific'] = $node['deviceClass']['specific'];
							$node['deviceIdNew'] = $node['manufacturerId']. '-'.$node['productType'].'-'.$node['productId'];
							$devClassFile = realpath(dirname(__FILE__) . '/../../resources/zwavejs2mqtt/node_modules/@zwave-js/config/config/deviceClasses.json');
							if (file_exists($devClassFile)) {
								$string = file_get_contents($devClassFile);
								$pattern = '/(((?<!http:|https:)\/\/.*|(\/\*)([\S\s]*?)(\*\/)))/im';
								$json = json_decode ( preg_replace ( $pattern, '', $string ),true );
								$basic = '0x'.str_pad(dechex(intval($node['deviceClass']['basic'])),2,'0',STR_PAD_LEFT);
								$generic = '0x'.str_pad(dechex(intval($node['deviceClass']['generic'])),2,'0',STR_PAD_LEFT);
								$specific = '0x'.str_pad(dechex(intval($node['deviceClass']['specific'])),2,'0',STR_PAD_LEFT);
								if (isset($json['basic'][$basic])){
									$node['classBasic'] = $json['basic'][$basic];
								}
								if (isset($json['generic'][$generic])){
									$node['classGeneric'] = $json['generic'][$generic]['label'];
									if (isset($json['generic'][$generic]['specific'][$specific])){
										$node['classSpecific'] = $json['generic'][$generic]['specific'][$specific]['label'];
									}
								}
							}
							$eqLogic = zwavejs::byLogicalId($node['id'], 'zwavejs');
							$node['confJeedom'] = '-';
							if (is_object($eqLogic)) {
								$path = $eqLogic->getConfFilePath();
								if (is_file(dirname(__FILE__) . '/../config/devices/' . $path)) {
									$node['confJeedom'] = $path;
								}
								$device = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $eqLogic->getConfFilePath()), array());
								$node['confType'] = 'Configuration Jeedom <br>';
								if (isset($device['properties']) && count($device['properties']>0)){
									if (isset($device['firmProperties']) && $device['firmProperties'] == 1){
										$found = false;
										foreach ($device['properties'] as $firm=>$property){
											if ($firm != 'default'){
												if (evaluate($eqLogic->getConfiguration('firmwareVersion').$firm)===true){
													$device['properties']=$property;
													$found = true;
													break;
												}
											}
										}
										if (!$found){
											$device['properties'] = $device['properties']['default'];
										}
									}
									$node['confType'] .= ' Properties : <br>';
									foreach ($device['properties'] as $property=>$value){
										if (isset($value['mode']) && $value['mode'] != $eqLogic->getConfiguration('confMode','')){
											continue;
										}
										$node['confType'] .= '  - '.$property . ' : ' .json_encode($value) .'<br>';
									}
								}
								if (isset($device['commands']) && count($device['commands']>0)){
									$node['confType'] .= 'Commands : <br>';
									foreach ($device['commands'] as $command){
										$node['confType'] .= '  - '.$command['name'] .'<br>';
									}
								}
								try{
									$node['lastWakeup'] ='N/A';
									$node['nextWakeup'] = 'N/A';
									$node['configWakeup'] = 'N/A';
									$configWakeup = $node['values']['132-0-wakeUpInterval']['value'];
									$node['configWakeup'] = self::secondsToTime($configWakeup);
									if ($eqLogic->getConfiguration('lastWakeUp','') != ''){
										$lastWakeUp = time() - $eqLogic->getConfiguration('lastWakeUp','');
										$node['lastWakeup'] = self::secondsToTime($lastWakeUp);
										if ($lastWakeUp > $configWakeup) {
											$node['nextWakeup'] = '- '. self::secondsToTime($lastWakeUp - $configWakeup);
										} else {
											$node['nextWakeup'] = self::secondsToTime($configWakeup - $lastWakeUp);
										}
									} 
								}catch(Exception $e){
								}
							}
							event::add('zwavejs::getNodeInfo',$node);
						}
					}
				} else if ($value['origin']['type'] == 'getNodeValues'){
					foreach ($value['result'] as $node){
						if ($node['id']==$value['origin']['node']){
							$values['id']=$node['id'];
							$values['status']=$node['status'];
							$values['nodeValues'] = zwavejs::constructValuePage($node['id'],$node['values']);
							event::add('zwavejs::getNodeValues',$values);
						}
					}
				} else if ($value['origin']['type'] == 'group'){
					$data =array();
					foreach ($value['result'] as $node){
						$data[$node['id']]=array('groups'=>$node['groups'],'label'=>$node['productLabel'],'endpoints'=>$node[endpointIndizes],'status'=>$node['status']);
					}
					event::add('zwavejs::getNodeGroup',$data);
				} else if ($value['origin']['type'] == 'health'){
					$healthData = array();
					foreach ($value['result'] as $node=>$values){
						$healthData[$values['id']]=$values;
					}
					$data = zwavejs::constructHealthPage($healthData);
					event::add('zwavejs::getHealthPage',$data);
				} else if ($value['origin']['type'] == 'syncValues'){
					foreach ($value['result'] as $node){
						if ($node['id']==$value['origin']['node']){
							$eqLogic = zwavejs::byLogicalId($node['id'], 'zwavejs');
							if (is_object($eqLogic)) {
								$eqLogic->handleCommandUpdate($node['values'],true);
							}
						}
					}
				}
			}
			else if ($key == 'refreshNeighbors'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.json_encode($value));
				foreach ($value['result'] as $node => $neighbors){
					if ($node == config::byKey('controllerId','zwavejs',0)) {
						$value['result']['controllerNeighbors'] = implode($neighbors,' - ');
					}
				}
				event::add('zwavejs::getNeighbors',$value['result']);
			}
			else if ($key == 'getAssociations'){
				if ($value['origin']['type'] == 'getNodeAssociations'){
					event::add('zwavejs::getNodeAssociations',array('id'=>$value['origin']['node'],'data'=>$value['result']));
				}
			}
		}
	}
	
	public static function handleNode($_node) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Traitement d\'un node');
		log::add('zwavejs','debug', '[' . __FUNCTION__ . '] '.json_encode($_node));
		foreach ($_node as $key => $value){
			log::add('zwavejs','debug', $key);
			if ($key == 'node_value_updated'){
				self::handleNodeValueUpdate($value);
			}
			else if ($key == 'node_interview_stage_completed'){
				self::createEqLogic($value['data'][0]);
			}
			else if ($key == 'node_interview_completed'){
				self::createEqLogic($value['data'][0]);
			}
		}
	}
	
	public static function handleController($_controller) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Traitement du controller');
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. json_encode($_controller));
		foreach ($_controller as $key => $value){
			log::add('zwavejs','debug', $key);
			if ($key == 'inclusion_started'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Inclusion démarrée');
				event::add('zwavejs::inclusion',
					array('message' => 'Inclusion démarrée, celle-ci restera active 60 secondes','type'=>'inclusion')
				);
				config::save('controllerStatus','inclusion','zwavejs');
			}
			else if ($key == 'inclusion_stopped'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Inclusion arrêtée');
				event::add('zwavejs::inclusion',
					array('message' => 'Inclusion arrêtée','type'=>'empty')
				);
				config::save('controllerStatus','none','zwavejs');
			}
			else if ($key == 'inclusion_failed'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Inclusion échouée');
				event::add('zwavejs::inclusion',
					array('message' => 'Inclusion échouée','type'=>'empty')
				);
				config::save('controllerStatus','none','zwavejs');
			}
			else if ($key == 'exclusion_started'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Exclusion démarrée');
				event::add('zwavejs::inclusion',
					array('message' => 'Exclusion démarrée, celle-ci restera active 60 secondes','type'=>'exclusion')
				);
				config::save('controllerStatus','exclusion','zwavejs');
			}
			else if ($key == 'exclusion_stopped'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Exclusion arrêtée');
				event::add('zwavejs::inclusion',
					array('message' => 'Exclusion arrêtée','type'=>'empty')
				);
				config::save('controllerStatus','none','zwavejs');
			}
			else if ($key == 'exclusion_failed'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Exclusion echouée');
				event::add('zwavejs::inclusion',
					array('message' => 'Exclusion échouée','type'=>'empty')
				);
				config::save('controllerStatus','none','zwavejs');
			}
			else if ($key == 'node_removed'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Noeud exclu');
				$id = $value['data'][0]['id'];
				$eqLogic = self::byLogicalId($id, 'zwavejs');
				if (is_object($eqLogic)) {
					$id = $id .  $eqLogic->getHumanName();
				}
				event::add('zwavejs::inclusion',
					array('message' => 'Noeud Exclu : '.$id,'type'=>'empty')
				);
				config::save('controllerStatus','none','zwavejs');
				if (config::byKey('autoRemoveExcludeDevice', 'zwavejs') == 1) {
					if (is_object($eqLogic)) {
						$eqLogic->remove();
						event::add('zwavejs::includeDevice', '');
					}
				}
			}
			else if ($key == 'node_added'){
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Noeud inclus');
				event::add('zwavejs::inclusion',
					array('message' => 'Noeud Inclus : '.$value['data'][0]['id'],'type'=>'empty')
				);
				config::save('controllerStatus','none','zwavejs');
				$eqLogic = zwavejs::byLogicalId($value['data'][0]['id'], 'zwavejs');
				if (!is_object($eqLogic)) {
					event::add('jeedom::alert', array(
						'level' => 'warning',
						'page' => 'zwavejs',
						'message' => __('Nouveau module Z-Wave détecté. Le device sera créé lorsque son interview sera terminé', __FILE__),
					));
				}
			}
		}
	}
	
	public static function handleNodeValueUpdate($_value_update) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Traitement d\'un update de value d\'un node');
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. json_encode($_value_update));
		$datas = $_value_update['data'];
		$node = $datas[0];
		$change = $datas[1];
		$eqLogic = zwavejs::byLogicalId($node['id'], 'zwavejs');
		if (is_object($eqLogic)) {
			if ($eqLogic->getIsEnable()) {
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Le noeud avec l\'id : ' . $node['id'] . ' existe ' . $eqLogic->getHumanName());
			}
			$eqLogic->handleCommandUpdate($change);
		}
	}
	
	public static function handleNodeValueUpdateDirect($_nodeId,$_value_update) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Traitement d\'un update de value d\'un node direct');
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $_nodeId . ' ' . json_encode($_value_update));
		$eqLogic = zwavejs::byLogicalId($_nodeId, 'zwavejs');
		if (is_object($eqLogic)) {
			if ($eqLogic->getIsEnable()) {
				log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Le noeud avec l\'id : ' . $_nodeId . ' existe ' . $eqLogic->getHumanName());
				foreach ($_value_update as $class=>$value){
					log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$class);
					if (is_int($class)){
						if (is_array($value[0])){
							log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Value is an array endpoint is defined as 0');
							foreach ($value as $element){
								foreach($element as $property=>$data) {
									if ($property == 'scene') {
										if (isset($data['value'])){
											$eqLogic->updateCmd($class.'-0-'.$property, $data['value']);
										}
									}
									else if (isset($data['value'])){
										log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$class.'-0-'.$property .':' .json_encode($data));
										$eqLogic->updateCmd($class.'-0-'.$property, $data['value']);
									} else {
										foreach($data as $propertyKey=>$final) {
											log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$class.'-0-'.$property . '-'.$propertyKey.':' .json_encode($final));
											$eqLogic->updateCmd($class.'-0-'.$property.'-'.$propertyKey, $final['value']);
										}
									}
								}
							}
						} else {
							log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Value is not an array there are some endpoints');
							foreach ($value as $endpoint=>$element){
								foreach($element as $property=>$data) {
									if (isset($data['value'])){
										log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$class.'-'.$endpoint.'-'.$property .':' .json_encode($data));
										$eqLogic->updateCmd($class.'-'.$endpoint.'-'.$property, $data['value']);
									} else {
										foreach($data as $propertyKey=>$final) {
											log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$class.'-'.$endpoint.'-'.$property . '-'.$propertyKey.':' .json_encode($final));
											$eqLogic->updateCmd($class.'-'.$endpoint.'-'.$property.'-'.$propertyKey, $final['value']);
										}
									}
								}
							}
						}
					} else {
						if ($class == 'status'){
							$eqLogic->updateCmd('0-0-nodeStatus', $value['status']);
							if ($value['status'] == 'Awake'){
								$eqLogic->setConfiguration('lastWakeUp', time());
								$eqLogic->save();
							}
						}
					}
				}
			}
		}
	}
	
	public static function publishMqttApi($_api_name,$_args=array()) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Publication Mqtt Api ' . $_api_name . ' ' . json_encode($_args));
		mqtt2::publish(config::byKey('prefix', 'zwavejs','zwave').'/_CLIENTS/ZWAVE_GATEWAY-Jeedom/api/'.$_api_name.'/set',$_args);
	}
	
	public static function publishMqttValue($_node,$_path,$_args=array()) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Publication Mqtt Value' . $_node . ' ' . $_path . ' ' . json_encode($_args));
		mqtt2::publish(config::byKey('prefix', 'zwavejs','zwave').'/'.$_node.'/'.$_path.'/set',$_args);
	}
	
	public static function getInfo() {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Demande d\'info ' );
		self::publishMqttApi('getInfo',array('type'=>'getInfo'));
	}
	
	public static function getNodeInfo($_nodeId,$_type='getNodeInfo') {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Demande d\'info d\'un Node '. $_nodeId );
		self::publishMqttApi('getNodes',array('type'=>$_type,'node'=>$_nodeId));
	}
	
	public static function getNodeValues($_nodeId) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Demande d\'info d\'un Node '. $_nodeId );
		self::publishMqttApi('getNodes',array('type'=>'getNodeValues','node'=>$_nodeId));
	}
	
	public static function getNodeAssociations($_nodeId) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Demande des associations d\'un Node '. $_nodeId );
		$args = array("args"=>array(intval($_nodeId)));
		$args['type']='getNodeAssociations';
		$args['node']=$_nodeId;
		self::publishMqttApi('getAssociations',$args);
	}
	
	public static function getNodes($_mode = '') {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Demande des nodes ' );
		if ($_mode == 'sync'){
			event::add('zwavejs::sync',
			array('message' => 'Synchronisation en cours ...','type'=>'launched')
		);
		}
		self::publishMqttApi('getNodes',array('type'=>$_mode));
	}
	
	public static function controllerAction($_type) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Lancement d\'une action controller avec comme type ' . $_type );
		self::publishMqttApi($_type,array('type'=>'controllerAction'));
	}
	
	public static function namingAction($_nodeId) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Envoie des noms et locations pour le noeud ' . $_nodeId );
		$allEqLogics = array();
		if ($_nodeId == 'all'){
			$allEqLogics = eqLogic::byType('zwavejs');
		} else {
			$eq = zwavejs::byLogicalId($_nodeId, 'zwavejs');
			if (is_object($eq)) {
				$allEqLogics[] = $eq;
			}
		}
		foreach ($allEqLogics as $eqLogic){
			try {
				$location = '';
				$objet = $eqLogic->getObject();
				if (is_object($objet)) {
					$location = str_replace(array_keys($replace), $replace, $objet->getName());
				} else {
					$location = 'aucun';
				}
				$name = $eqLogic->getName();
				$eqLogic->setNameLocation($name,$location);
			} catch (Exception $e) {
			
			}
		}
	}
	
	public static function nodeAction($_type,$_nodeId) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Lancement d\'une action node avec comme type ' . $_type . ' - ' .$_nodeId );
		$args=array('args'=>array(intval($_nodeId)),'type'=>'nodeAction');
		if ($_type == 'syncValues'){
			self::publishMqttApi('getNodes',array('type'=>'syncValues','node'=>$_nodeId));
		} else {
			self::publishMqttApi($_type,$args);
		}
	}
	
	public static function setNodeValue($_fullpath,$_value) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $_fullpath . ' '.$_value );
		$detailsPath = explode('-',$_fullpath, 2);
		zwavejs::publishMqttValue($detailsPath[0],str_replace('-','/',$detailsPath[1]),$_value);
	}
	
	public static function removeAssociation($_nodeId,$_groupId,$_sourceEndpoint,$_targetEndpoint,$_assoNodeId) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $_nodeId . ' '.$_groupId . ' '.$_sourceEndpoint . ' '.$_targetEndpoint . ' '.$_assoNodeId);
		$args=array();
		$node = array('nodeId'=>intval($_nodeId), 'endpoint' =>intval($_sourceEndpoint));
		if ($_targetEndpoint != 'Root'){
			$assoNode = array('nodeId'=>intval($_assoNodeId), 'endpoint' =>intval($_targetEndpoint));
		} else {
			$assoNode = array('nodeId'=>intval($_assoNodeId));
		}
		$args=array('args' => array($node,intval($_groupId),array($assoNode)));
		zwavejs::publishMqttApi('removeAssociations',$args);
	}
	
	public static function removeAllAssociations($_nodeId) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $_nodeId);
		$args=array('args' => array(intval($_nodeId)));
		zwavejs::publishMqttApi('removeAllAssociations',$args);
	}
	
	public static function addAssociation($_nodeId, $_group, $_target) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $_nodeId . ' ' . $_group . ' ' . $_target);
		$args=array();
		$endpoint = explode('-',$_group)[0];
		$groupId = explode('-',$_group)[1];
		$targetEndpoint = explode('-',$_target)[0];
		$targetId = explode('-',$_target)[1];
		if ($endpoint != 'root'){
			$node = array('nodeId'=>intval($_nodeId), 'endpoint' =>intval($endpoint));
		} else {
			$node = array('nodeId'=>intval($_nodeId));
		}
		if ($targetEndpoint != 'root'){
			$assoNode = array('nodeId'=>intval($targetId), 'endpoint' =>intval($targetEndpoint));
		} else {
			$assoNode = array('nodeId'=>intval($targetId));
		}
		$args=array('args' => array($node,intval($groupId),array($assoNode)));
		zwavejs::publishMqttApi('addAssociations',$args);
	}
	
	public static function refreshNodeCC($_nodeId,$_cc) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $_nodeId . ' '.$_cc );
		$args=array('args' => array(intval($_nodeId),intval($_cc)));
		$args['type']='refreshNodeCC';
		zwavejs::publishMqttApi('refreshCCValues',$args);
	}
	
	public static function inclusion($_method, $_options) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Lancement du mode inclusion avec ' . $_method . ' '.$_options);
		$args=array();
		if ($_method == 'include') {
			$api = 'startInclusion';
			$args = array('args'=> array(intval($_options),array('forceSecurity'=>false)));
		}
		else if ($_method == 'exclude') {
			$api = 'startExclusion';
		}
		else if ($_method == 'stop') {
			$api = 'stop'.$_options;
		}
		$args['type']='inclusion';
		self::publishMqttApi($api,$args);
	}
	
	public static function syncNodes($_data) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Synchronisation des noeuds');
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. json_encode($_data));
		event::add('zwavejs::sync',
			array('message' => 'Découverte de ' .count($_data) . ' noeud(s)','type'=>'running')
		);
		foreach ($_data as $node){
			zwavejs::createEqLogic($node,$_ignoreEvent=true);
		}
		event::add('zwavejs::sync',
			array('message' => 'Fin de la synchronisation ...','type'=>'finished')
		);
	}
	
	public static function createEqLogic($_node,$_ignoreEvent=false) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Création d\'un node');
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. json_encode($_node));
		$eqLogic = zwavejs::byLogicalId($_node['id'], 'zwavejs');
		$inited = $_node['inited'];
		$new = false;
		$refresh = false;
		if (!is_object($eqLogic)) {
			$eqLogic = new zwavejs();
			$eqLogic->setEqType_name('zwavejs');
			$eqLogic->setIsEnable(1);
			$eqLogic->setLogicalId($_node['id']);
			$eqLogic->setIsVisible(1);
			$new =true;
			$refresh = true;
			if (config::byKey('auto_applyRecommended', 'zwavejs') == 1) {
				$eqLogic->applyRecommended();
			}
		}
		$wascomplete = $eqLogic->getConfiguration('interview','incomplete');
		if (($wascomplete == 'incomplete' && $inited) || ($inited && $new)){
			if (($eqLogic->getName() == $eqLogic->getLogicalId() . ' - ' . 'Node inclus') || ($eqLogic->getName() == '')){
				$eqLogic->setName($eqLogic->getLogicalId() . ' - ' . $_node['manufacturer'] . ' ' . $_node['productDescription'] . ' '. $_node['productLabel']);
				$refresh = true;
			}
		} else if ($new) {
			$eqLogic->setName($eqLogic->getLogicalId() . ' - ' . 'Node inclus');
		}
		if ($inited === false) {
			if ($eqLogic->getConfiguration('interview','incomplete') != 'complete') {
				$eqLogic->setConfiguration('interview', 'incomplete');
			}
		} else {
			$eqLogic->setConfiguration('interview', 'complete');
		}
		$eqLogic->setConfiguration('manufacturer_id', $_node['manufacturerId']);
		$eqLogic->setConfiguration('product_type', $_node['productType']);
		$eqLogic->setConfiguration('product_id', $_node['productId']);
		$eqLogic->setConfiguration('product_name', $_node['productLabel'].' - '.$_node['productDescription']);
		$eqLogic->setConfiguration('firmwareVersion', $_node['firmwareVersion']);
		$eqLogic->save();
		$eqLogic = zwavejs::byId($eqLogic->getId());
		if (!$_ignoreEvent){
			if ($refresh){
				event::add('zwavejs::includeDevice', $eqLogic->getId());
			}
		}
		if ($inited && $refresh){
			$eqLogic->createCommand();
			$_node['values']['0-0-nodeStatus'] = array('value'=>$_node['status']);
			$eqLogic->handleCommandUpdate($_node['values'],true);
		}
		return $eqLogic;
	}
	
	public static function constructValuePage($_nodeId, $_values) {
		$nodeValuesDict = array();
		$nodeValues = '<div class="panel-group" id="accordionValues">';
		foreach($_values as $key=>$value){
			$value['oriKey'] = $key;
			$value['ccName'] = $value['commandClassName']. ' v'.$value['commandClassVersion'];
			$nodeValuesDict[intval($value['commandClass'])][]=$value;
		}	
		ksort($nodeValuesDict);
		$updates = array();
		foreach($nodeValuesDict as $cc=>$datas){
			$nodeValues .= '<div class="panel panel-default">';
			$nodeValues .= '<div class="panel-heading">';
			$nodeValues .= '<h3 class="panel-title cursor">';
			$nodeValues .= '<a class="accordion-toggle" data-toggle="collapse" data-parent="#accordionValues" href="#'.$datas[0]['commandClass'].'">';
			$nodeValues .= '<i class="fas fa-circle-notch success" style="font-size:20px;"></i> <span style="font-size:18px;">'.$datas[0]['ccName'].'<sub> ('.$cc.')</sub></span></a>';
			$nodeValues .= '<i  class="refreshNodeCC fas fa-sync pull-right cursor" data-cc="'.$datas[0]['commandClass'].'" data-nodeId="'.$_nodeId.'" title="'.__('Rafraîchir la CC', __FILE__).'"></i>';
			$nodeValues .= '</h3>';
			$nodeValues .= '</div>';
			$nodeValues .= '<div id="'.$datas[0]['commandClass'].'" class="panel-collapse collapse" aria-expanded="true">';
			$nodeValues .= '<div class="panel-body">';
			$nodeValues .= '<table id="'.$datas[0]['commandClass'].'Table" class="table table-condensed table-bordered">';
			$nodeValues .= '<tbody>';
			foreach($datas as $data){
				$nodeValues .= '<tr>';
				$nodeValues .= '<td>'.$data['endpoint'].'</td>';
				if (isset($data['propertyKey'])){
					$nodeValues .= '<td>'.$data['property'].'-'.$data['propertyKey'].'</td>';
				} else {
					$nodeValues .= '<td>'.$data['property'].'</td>';
				}
				if (isset($data['description'])){
					$nodeValues .= '<td style="width:30%">'.$data['label'].' <sup><i class="fas fa-question-circle tooltips" title="'.$data['description'].'"></i><sup></td>';
				} else {
					$nodeValues .= '<td style="width:30%">'.$data['label'].'</td>';
				}
				$nodeValues .= '<td style="width:30%">';
				$tooltip = '';
				if ($data['readable']){
					if (!isset($data['value'])){
						$finalValue = 'N/A';
					} else if ($data['type'] == 'boolean'){
						$finalValue = __('OFF', __FILE__);
						if ($data['value']){
							$finalValue = __('ON', __FILE__);
						}
					} else if ($data['type'] == 'string[]'){
						$finalValue = implode($data['value'], ' - ');
					} else if ($data['type'] == 'number'){
						if ($data['list']){
							foreach ($data['states'] as $state){
								if ($state['value'] == $data['value']){
									$finalValue = $data['value'].' - '. $state['text'];
									break;
								}
								$finalValue = $data['value'];
							}
						} else {
							$finalValue = $data['value'];
						}
					} else {
						$finalValue = $data['value'];
					}
					if ($data['unit']){
						$finalValue .= ' ' . $data['unit'];
					}
					if (isset($data['states'])){
						$tooltip .= 'Valeurs possibles : &#013;';
						foreach ($data['states'] as $state){
							$tooltip .= $state['value'] . ' -> ' . $state['text'].'&#013;';
						}
					}
					if (isset($data['min'])){
						$tooltip .= 'min : ' . $data['min'].'&#013;';
					}
					if (isset($data['max'])){
						$tooltip .= 'max : ' . $data['max'].'&#013;';
					}
					if (isset($data['default'])){
						$tooltip .= 'défaut : ' . $data['default'].'&#013;';
					}
				} else {
					if (isset($data['value'])){
						$finalValue = $data['value'];
					} else {
						$finalValue = '-';
					}
				}
				$span = '';
				if ($finalValue && $finalValue == __('ON', __FILE__)){
					$span .= '<span class="label label-success" style="font-size:1em;">';
				} else if ($finalValue && $finalValue == __('OFF', __FILE__)) {
					$span .= '<span class="label label-danger" style="font-size:1em;">';
				} else {
					$span .= '<span class="label label-primary" style="font-size:1em;" title="'.$tooltip.'">';
				}
				$span .= $finalValue.'</span>';
				if ($tooltip != ''){
					$span .= ' <sup><i class="fas fa-question-circle tooltips" title="'.$tooltip.'"></i><sup>';
				}
				$updates[str_replace(' ','_',$data['id'])]=array('value'=>$span);
				$nodeValues .= '<span class="'.str_replace(' ','_',$data['id']).'">'.$span.'</span>';
				if ($data['writeable']){
					$nodeValues .= ' <a class="btn btn-xs btn-primary editValue pull-right"';
					$nodeValues .= ' data-type="'.$data['type'].'"';
					if ($data['list']){
						$states='';
						foreach ($data['states'] as $state){
							$states.=$state['value'].'-'.$state['text'].';';
						}
					}
					$nodeValues .= ' data-states="'.substr($states, 0, -1).'"';
					$nodeValues .= ' data-label="'.$data['label'].'"';
					$nodeValues .= ' data-list="'.$data['list'].'"';
					$nodeValues .= ' data-path="'.$data['id'].'"';
					$nodeValues .= ' style="text-align: right;display:inline-block"><i class="fas fa-wrench"></i></a>';
				}
				$nodeValues .= '</td>';
				$nodeValues .= '<td class="'.str_replace(' ','_',$data['id']).'_lastUpdate">'.date("d/m/Y H:i:s",$data['lastUpdate']/ 1000).'</td>';
				$updates[str_replace(' ','_',$data['id'])]['lastUpdate']=date("d/m/Y H:i:s",$data['lastUpdate']/ 1000);
				if ($data['readable'] && (in_array($data['type'],array('number','boolean')))){
					$nodeValues .= '<td>';
					$nodeValues .= ' <a class="btn btn-xs btn-warning createCommandInfo pull-right"';
					$nodeValues .= ' data-type="'.$data['type'].'"';
					$nodeValues .= ' data-label="'.$data['label'].'"';
					$nodeValues .= ' data-path="'.$data['id'].'"';
					$nodeValues .= ' data-unit="'.$data['unit'].'"';
					$nodeValues .= ' data-max="'.$data['max'].'"';
					$nodeValues .= ' data-min="'.$data['min'].'"';
					$nodeValues .= ' data-value="'.$data['value'].'"';
					$nodeValues .= ' style="text-align: right;display:inline-block" title="Créer la commande Info dans Jeedom"><i class="fas fa-marker"></i></a>';
					$nodeValues .= '</td>';
				} else {
					$nodeValues .= '<td></td>';
				}
				$nodeValues .= '</tr>';
			}
			$nodeValues .='</tbody>';
			$nodeValues .='</table>';
			$nodeValues .='</div>';
			$nodeValues .='</div>';
			$nodeValues .='</div>';
		}
		$nodeValues .='</div>';
		return array('init'=>$nodeValues,'updates'=>$updates);
	}
	
	public static function constructHealthPage($_values) {
		$healthPage = '';
		ksort($_values);
		foreach ($_values as $node=>$values){
			$healthPage .= '<tr><td>'.$values['id'].'</td>';
			$eqLogic = zwavejs::byLogicalId($values['id'], 'zwavejs');
			$productDetails = '<sup><i class="fas fa-question-circle tooltips" title="'.$values['manufacturer'].' '.$values['productDescription']. ' Firmware : '.$values['firmwareVersion'].'"></i><sup>';
			if (is_object($eqLogic)){
				$image = 'plugins/zwavejs/core/config/devices/'.$eqLogic->getImgFilePath();
				if (!is_file(dirname(__FILE__) . '/../config/devices/'.$eqLogic->getImgFilePath())){
					$image = 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
				}
				$healthPage .= '<td><img src="'.$image.'" height="40"/> <a href="index.php?v=d&p=zwavejs&m=zwavejs&logical_id=' . $eqLogic->getLogicalId() . '">' . $eqLogic->getHumanName(true).  '</a>'.' ' .$productDetails.'</td>';
			} else {
				$healthPage .= '<td><img src="plugins/zwavejs/plugin_info/zwavejs_icon.png" height="40"/> '.$values['productLabel'].' - '.$values['productDescription'].' ' .$productDetails.'</td>';
			}
			$healthPage .= '<td><span class="label label-info" style="font-size : 1em;">'.$values['endpointsCount'].'</span></td>';
			if ($values['isSecure']) {
				if($values['security']){
					$secure = '<span title="Secure" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span> <sup><i class="fas fa-question-circle tooltips" title="'.$values['security'].'"></i><sup>';
				} else {
					$secure = '<span title="Non Secure" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
				}
			} else {
				$secure = '<span title="Non Secure" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
			}
			$healthPage .= '<td>'.$secure.'</td>';
			
			if ($values['supportsBeaming']) {
				$beaming = '<span title="Beaming" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span>';
			} else {
				$beaming = '<span title="Non Beaming" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
			}
			$healthPage .= '<td>'.$beaming.'</td>';
			
			if ($values['isFrequentListening']) {
				$flirs = '<span title="Flirs" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span>';
			} else {
				$flirs = '<span title="Non Flirs" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
			}
			$healthPage .= '<td>'.$flirs.'</td>';
			
			if ($values['zwavePlusVersion']) {
				$zwavePlusVersion = '<span title="Secure" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span> <sup><i class="fas fa-question-circle tooltips" title="v'.$values['zwavePlusVersion'].'"></i><sup>';
			} else {
				$zwavePlusVersion = '<span title="Non ZwavePlus" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
			}
			$healthPage .= '<td>'.$zwavePlusVersion.'</td>';
			
			if ($values['isRouting']) {
				$isRouting = '<span title="Routing" style="font-size : 1.5em;"><i class="fas fa-check icon_green" aria-hidden="true"></i></span>';
			} else {
				$isRouting = '<span title="No Routing" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_orange" aria-hidden="true"></i></span>';
			}
			$healthPage .= '<td>'.$isRouting.'</td>';
			
			if ($values['inited']) {
				$inited = '<span title="Initié" style="font-size : 1.5em;"><i class="fas fa-check-circle icon_green" aria-hidden="true"></i></span>';
			} else {
				$inited = '<span title="Non initié" style="font-size : 1.5em;"><i class="fas fa-minus-circle icon_red" aria-hidden="true"></i></span>';
			}
			$healthPage .= '<td>'.$inited.'</td>';
			$wakedup ='N/A';
			if ($eqLogic->getConfiguration('lastWakeUp','') != ''){
				$wakedup = time() - $eqLogic->getConfiguration('lastWakeUp','');
			}
			if ($values['status'] == 'Alive') {
				$status = '<span title="Alive" style="font-size : 1.5em;"><i class="fas fa-check icon_green" aria-hidden="true"></i></span>';
			} else if (($values['status'] == 'Dead')){
				$status = '<span title="Dead" style="font-size : 1.5em;"><i class="fas fa-skull-crossbones icon_red" aria-hidden="true"></i></span>';
			} else if (($values['status'] == 'Awake')){
				$status = '<span title="Awake" style="font-size : 1.5em;"><i class="fas fa-grin icon_green" aria-hidden="true"></i></span>';
			} else if (($values['status'] == 'Asleep')){
				$status = '<span title="Sleeping" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">z<sup>z<sup>z</sup></sup></i></span>';
			} else {
				$status = '<span title="Other" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">'.$values['status'].'</i></span>';
			}
			$healthPage .= '<td>'.$status.'</td>';
			
			$labelInterview ='label-warning';
			if ($values['interviewStage'] == 'Complete'){
				$labelInterview ='label-success';
			} else if ($values['interviewStage'] == 'ProtocolInfo'){
				$labelInterview ='label-danger';
			}
			$healthPage .= '<td><span class="label '.$labelInterview .'" style="font-size : 1em;">'.$values['interviewStage'].'</span></td>';
			
			$healthPage .= '<td>'.date("d/m/Y H:i:s",$values['lastActive']/ 1000);
			if (($values['status'] == 'Asleep') && $wakedup !='N/A'){
				$healthPage .='<br><i class="fas fa-grin icon_blue" title="Dernier réveil" aria-hidden="true"></i> <span title="Dernier réveil" style="font-size : 0.7em;">'.self::secondsToTime($wakedup).'</span>';
				if ($wakedup > $values['values']['132-0-wakeUpInterval']['value']) {
					$next = '- ' . self::secondsToTime($wakedup - $values['values']['132-0-wakeUpInterval']['value']);
				} else {
					$next = self::secondsToTime($values['values']['132-0-wakeUpInterval']['value'] - $wakedup);
				}
				$healthPage .='<br><i class="fas fa-arrow-right icon_blue" title="Prochain réveil estimé" aria-hidden="true"></i> <span title="Prochain réveil estimé" style="font-size : 0.7em;">'.$next.'</span>';
				$healthPage .='<br><i class="fas fa-wrench icon_blue" title="WakeUp Interval" aria-hidden="true"></i> <span title="WakeUp Interval" style="font-size : 0.7em;">'.self::secondsToTime($values['values']['132-0-wakeUpInterval']['value']).'</span>';
			}
			$healthPage .='</td>';
			$healthPage .= '<td><a class="btn btn-info btn-xs pingDevice" data-id="' . $values['id'] . '"><i class="fas fa-eye"></i> Ping</a></td>';
			$healthPage .= '</tr>';
		}
		return $healthPage;
	}

	public static function autoCreateCommandInfo($_path,$_type,$_label,$_unit,$_max,$_min,$_currentValue){
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Création d\'une commande info '. $_path);
		$elements = explode('-',str_replace('_',' ',$_path), 4);
		$eqLogic = zwavejs::byLogicalId($elements[0],'zwavejs');
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.print_r($elements,true));
		if (is_object($eqLogic)) {
			$class = $elements[1];
			$endpoint = $elements[2];
			$property = $elements[3];
			$logical = $class.'-'.$endpoint.'-'.$property;
			$command = $eqLogic->getCmd(null, $logical);
			if (!is_object($command)) {
				$command = new zwavejscmd();
				$command->setLogicalId($logical);
				$label = $_label.'-'.rand(0,99999);
				$command->setName($label);
				$command->setIsVisible(0);
				$command->setEqLogic_id($eqLogic->getId());
				$command->setConfiguration('class',$class);
				$command->setConfiguration('endpoint',$endpoint);
				$command->setConfiguration('property',$property);
				$command->setType('info');
				if ($_type == 'boolean') {
					$command->setSubType('binary');
				}
				else if ($_type == 'number') {
					$command->setSubType('numeric');
					if ($_unit){
						$command->setUnite($_unit);
					}
					if ($_max){
						$command->setConfiguration('maxValue',$_max);
					}
					if ($_min){
						$command->setConfiguration('minValue',$_min);
					}
				}
				else {
					$command->setSubType('string');
				}
				$command->save();
				$eqLogic->checkAndUpdateCmd($logical,$_currentValue);
			}
			
		}
		
	}
	
	/*     * *********************Methode d'instance************************* */
	
	public function handleCommandUpdate($_change,$_init = false) {
		if ($_init){
			log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Init de valeur pour le node ' . $this->getHumanName());
			log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $this->getHumanName() . ' ' .json_encode($_change));
			foreach ($_change as $key=>$value){
				$this->updateCmd($key, $value['value']);
			}
		} else {
			log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Changement de valeur pour le node ' . $this->getHumanName());
			log::add('zwavejs','debug','[' . __FUNCTION__ . '] '. $this->getHumanName() . ' ' .json_encode($_change));
			$endpoint = 0;
			if (isset($_change['endpoint'])){
				$endpoint = $_change['endpoint'];
			}
			$cmdId = $_change['commandClass'].'-'.$endpoint.'-'.$_change['property'];
			if (isset($_change['propertyKey'])){
				$cmdId.='-'.$_change['propertyKey'];
			}
			log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Changement pour ' . $cmdId);
			$this->updateCmd($cmdId, $_change['newValue']);
		}
	}
	
	public function updateCmd($_cmdId,$_value) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$_cmdId .' ' .$_value);
		$_cmdId = str_replace('_',' ',$_cmdId);
		$cmdId = explode('-',$_cmdId, 3);
		$class = $cmdId[0];
		$endpoint = $cmdId[1];
		$property = $cmdId[2];
		$value = $_value;
		if ($property == 'hexColor'){
			$value = '#'.$value;
		}
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.$this->getLogicalId().'  :  '.$class .' ' .$endpoint . ' '.$property.' '.$value);
		$this->checkAndUpdateCmd($_cmdId, $value);
		if ($class == '128' && $property == 'level'){
			$this->batteryStatus($value);
		}
	}
	
	public function handleProperties($_device) {
		$device = $_device;
		if (!isset($device['commands'])){
			$device['commands'] = array();
		}
		if (isset($device['firmProperties']) && $device['firmProperties'] == 1){
			$found = false;
			foreach ($device['properties'] as $firm=>$property){
				if ($firm != 'default'){
					if (evaluate($this->getConfiguration('firmwareVersion').$firm)===true){
						$device['properties']=$property;
						$found = true;
						break;
					}
				}
			}
			if (!$found){
				$device['properties'] = $device['properties']['default'];
			}
		}
		foreach ($device['properties'] as $property => $details){
			$property=explode('|',$property)[0];
			if (!is_file(dirname(__FILE__) . '/../config/properties/' . strtolower($property).'.json')) {
				continue;
			}
			$propertyjson = is_json(file_get_contents(dirname(__FILE__) . '/../config/properties/' . strtolower($property).'.json'),false);
			if (!is_array($propertyjson)) {
				continue;
			}
			if (isset($details['mode']) && $details['mode'] != $this->getConfiguration('confMode','')){
				continue;
			}
			$type = 'standard';
			if (isset($details["type"])){
				$type = $details["type"];
			}
			$listCommand = array(1);
			if (isset($details['multi'])){
				$listCommand=$details['multi'];
			}
			$multiName = false;
			if (count($listCommand)>1){
				$multiName = true;
			}
			$replace_array = array("#endpoint#"=>0);
			foreach ($listCommand as $numberCommand){
				if (isset($propertyjson[$type])){
					foreach($propertyjson[$type] as $command){
						if (isset($details['replace'])) {
							foreach ($details['replace'] as $keyReplace =>$valueReplace){
								if ($valueReplace == 'multiKey'){
									if ($keyReplace == '#centralscene#'){
										$valueReplace = str_pad($numberCommand,3,'0',STR_PAD_LEFT);
									} else {
										$valueReplace = $numberCommand;
									}
								}
								$replace_array[$keyReplace]= $valueReplace;
							}
						}
						foreach ($replace_array as $source => $target){
							$command = json_decode(str_replace($source,$target,json_encode($command)),true);
						}
						if ($multiName){
							$command['name'].='-'.$numberCommand;
							if (isset($command['value'])){
								$command['value'].='-'.$numberCommand;
							}
						}
						if (isset($details['isVisible'])){
							$command['isVisible'] =$details['isVisible'];
						}
						if (isset($details['isHistorized'])){
							$command['isHistorized'] =$details['isHistorized'];
						}
						if (isset($details['returnStateTime'])){
							$command['configuration']['returnStateTime'] =$details['returnStateTime'];
						}
						if (isset($details['returnStateValue'])){
							$command['configuration']['returnStateValue'] =$details['returnStateValue'];
						}
						if (isset($details['calculValueOffset'])){
							$command['configuration']['calculValueOffset'] =$details['calculValueOffset'];
						}
						if (isset($details['dashboard'])){
							$command['template']['dashboard'] =$details['dashboard'];
						}
						if (isset($details['mobile'])){
							$command['template']['mobile'] =$details['mobile'];
						}
						if (isset($details['generic_type'])){
							$command['display']['generic_type'] =$details['generic_type'];
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
		if (isset($device['modes'])){
			if ($this->getConfiguration('confMode','') == ''){
				$this->setConfiguration('confMode',array_key_first($device['modes']));
			}
		}
		if (isset($device['properties'])){
			$device = zwavejs::handleProperties($device);
		}
		$commands = array();
		foreach ($device['commands'] as $command){
			$command['logicalId'] = $command['configuration']['class']. '-' . $command['configuration']['endpoint'] . '-' . $command['configuration']['property'];
			if (isset($command['configuration']['value'])){
				$command['logicalId'] .= '-'.$command['configuration']['value'];
			}
			$commands[]=$command;
		}
		$device['commands'] = $commands;
		$device['commands'][] = array('logicalId'=>'0-0-pingNode');
		$device['commands'][] = array('logicalId'=>'0-0-healNode');
		$device['commands'][] = array('logicalId'=>'0-0-nodeStatus');
		$device['commands'][] = array('logicalId'=>'0-0-isFailedNode');
		if ($_update == 2){
			$this->import($device,false);
		} else {
			$this->import($device,true);
		}
		sleep(1);
		event::add('jeedom::alert', array(
			'level' => 'warning',
			'page' => 'zwavejs',
			'message' => '',
		));
	}
	
	public function setNameLocation($_name, $_location) {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'');
		$argName = array("args"=>array(intval($this->getLogicalId()),$_name));
		$argLocation = array("args"=>array(intval($this->getLogicalId()),$_location));
		if ($this->getConfiguration('name','') != $argName) {
			self::publishMqttApi('setNodeName',$argName);
		}
		if ($this->getConfiguration('location','') != $argLocation) {
			self::publishMqttApi('setNodeLocation',$argLocation);
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
			log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Param recommandé');
			foreach ($device['recommended']['params'] as $value) {
				$fullpath=$this->getLogicalId().'-'.$value['path'];
				zwavejs::setNodeValue($fullpath,$value['value']);
			}
		}
		if (isset($device['recommended']['groups'])) {
			log::add('zwavejs','debug','[' . __FUNCTION__ . '] '.'Groupe recommandé');
		}
		if (isset($device['recommended']['needswakeup']) && $device['recommended']['needswakeup'] == true) {
			return "wakeup";
		}
		return;
	}
	
	public function getEqLogicInfos() {
		$result = array();
		$result['interview']= $this->getConfiguration('interview',false);
		if (!is_file(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath())) {
			return $result;
		}
		$device = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $this->getConfFilePath()), array());
		if (!is_array($device) || (!isset($device['commands']) && !isset($device['properties']))) {
			return $result;
		}
		if (isset($device['modes'])){
			$result['modes'] = $device['modes'];
			$result['actualMode'] = $this->getConfiguration('confMode','');
		} else {
			$result['modes'] = 'aucun';
		}
		$result['confType'] = 'Configuration Jeedom <br>';
		if (isset($device['properties']) && count($device['properties']>0)){
			if (isset($device['firmProperties']) && $device['firmProperties'] == 1){
				$found = false;
				foreach ($device['properties'] as $firm=>$property){
					if ($firm != 'default'){
						if (evaluate($this->getConfiguration('firmwareVersion').$firm)===true){
							$device['properties']=$property;
							$found = true;
							break;
						}
					}
				}
				if (!$found){
					$device['properties'] = $device['properties']['default'];
				}
			}
			$result['confType'] .= 'Properties : <br>';
			foreach ($device['properties'] as $property=>$value){
				if (isset($value['mode']) && $value['mode'] != $this->getConfiguration('confMode','')){
					continue;
				}
				$result['confType'] .= '  -'.$property . ' : ' .json_encode($value) .'<br>';
			}
		}
		if (isset($device['commands']) && count($device['commands']>0)){
			$result['confType'] .= 'Commands : <br>';
			foreach ($device['commands'] as $command){
				$result['confType'] .= '  -'.$command['name'] .'<br>';
			}
		}
		if (isset($device['recommended'])){
			$result['recommended'] = $device['recommended'];
		}
		return $result;
	}
	
	public function postSave() {
		log::add('zwavejs','debug','[' . __FUNCTION__ . '] ');
		
		$nodeStatus = $this->getCmd(null, '0-0-nodeStatus');
		if (!is_object($nodeStatus)) {
			$nodeStatus = new zwavejscmd();
			$nodeStatus->setLogicalId('0-0-nodeStatus');
			$nodeStatus->setName(__('Statut noeud', __FILE__));
			$nodeStatus->setIsVisible(0);
			$nodeStatus->setDisplay('icon','<i class="fas fa-info"></i>');
		}
		$nodeStatus->setEqLogic_id($this->getId());
		$nodeStatus->setConfiguration('class',0);
		$nodeStatus->setConfiguration('endpoint',0);
		$nodeStatus->setConfiguration('property','nodeStatus');
		$nodeStatus->setType('info');
		$nodeStatus->setSubType('string');
		$nodeStatus->save();
		
		$pingNode = $this->getCmd(null, '0-0-pingNode');
		if (!is_object($pingNode)) {
			$pingNode = new zwavejscmd();
			$pingNode->setLogicalId('0-0-pingNode');
			$pingNode->setIsVisible(0);
			$pingNode->setDisplay('icon','<i class="fas fa-sitemap"></i>');
			$pingNode->setName(__('Pinguer Noeud', __FILE__));
		}
		$pingNode->setType('action');
		$pingNode->setSubType('other');
		$pingNode->setConfiguration('class',0);
		$pingNode->setConfiguration('endpoint',0);
		$pingNode->setConfiguration('property','pingNode');
		$pingNode->setEqLogic_id($this->getId());
		$pingNode->save();
		
		$healNode = $this->getCmd(null, '0-0-healNode');
		if (!is_object($healNode)) {
			$healNode = new zwavejscmd();
			$healNode->setLogicalId('0-0-healNode');
			$healNode->setIsVisible(0);
			$healNode->setDisplay('icon','<i class="fas fa-medkit"></i>');
			$healNode->setName(__('Soigner Noeud', __FILE__));
		}
		$healNode->setType('action');
		$healNode->setSubType('other');
		$healNode->setConfiguration('class',0);
		$healNode->setConfiguration('endpoint',0);
		$healNode->setConfiguration('property','healNode');
		$healNode->setEqLogic_id($this->getId());
		$healNode->save();
		
		$isFailed = $this->getCmd(null, '0-0-isFailedNode');
		if (!is_object($isFailed)) {
			$isFailed = new zwavejscmd();
			$isFailed->setLogicalId('0-0-isFailedNode');
			$isFailed->setDisplay('icon','<i class="fas fa-heartbeat"></i>');
			$isFailed->setIsVisible(0);
			$isFailed->setName(__('Tester Noeud', __FILE__));
		}
		$isFailed->setType('action');
		$isFailed->setSubType('other');
		$isFailed->setConfiguration('class',0);
		$isFailed->setConfiguration('endpoint',0);
		$isFailed->setConfiguration('property','isFailedNode');
		$isFailed->setEqLogic_id($this->getId());
		$isFailed->save();
	}
	
	public function getConfFilePath($_all = false) {
		foreach (ls(dirname(__FILE__) . '/../config/devices', '*_'.$this->getConfiguration('manufacturer_id'), false, array('folders', 'quiet')) as $folder) {
			foreach (ls(dirname(__FILE__) . '/../config/devices/' . $folder, '*.json', false, array('files', 'quiet')) as $file) {
				$conf = is_json(file_get_contents(dirname(__FILE__) . '/../config/devices/' . $folder .'/'.$file), array());
				if (!is_array($conf)){
					continue;
				}
				if (isset($conf['versions']) && isset($conf['versions'][$this->getConfiguration('product_type')])){
					if (in_array($this->getConfiguration('product_id'),$conf['versions'][$this->getConfiguration('product_type')])){
						$return = $folder . $file;
						return $return;
					}
				}
			}
		}
		return false;
	}
	
	public function getImgFilePath() {
		$path = str_replace('.json','',$this->getConfFilePath());
		if (is_file(dirname(__FILE__) . '/../config/devices/' . $path.'.jpg')){
			return  $path.'.jpg';
		} else if (is_file(dirname(__FILE__) . '/../config/devices/' . $path.'.png')){
			return  $path.'.png';
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
			return;
		}
	}
	
}

class zwavejsCmd extends cmd {
	/*     * ***********************Methode static*************************** */
	
	/*     * *********************Methode d'instance************************* */
	
	public function preSave() {
		if ($this->getConfiguration('endpoint') === '') {
			$this->setConfiguration('endpoint', '0');
		}
		if ($this->getConfiguration('property') === '') {
			$this->setConfiguration('property', '0');
		}
		$logical = $this->getConfiguration('class') . '-' . $this->getConfiguration('endpoint') . '-' . $this->getConfiguration('property');
		if ($this->getConfiguration('value','')){
			$logical .= '-'.$this->getConfiguration('value','');
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
		$path = $cc.'/'.$endpoint.'/'.$property;
		if ($value == 'get'){
			$args=array('args'=>array(array('nodeId'=>intval($node),'commandClass'=>intval($cc)),'get',array($property)));
			zwavejs::publishMqttApi('sendCommand',$args);
			return;
		}
		if ($property == 'sendReport'){
			$elements=explode('-',$value);
			$report = array();
			foreach ($elements as $element) {
				$elementArray = explode(':',$element);
				$report[$elementArray[0]]=intval($elementArray[1]);
			}
			$args=array('args'=>array(array('nodeId'=>intval($node),'commandClass'=>intval($cc)),'sendReport',array($report)));
			zwavejs::publishMqttApi('sendCommand',$args);
			return;
		}
		if (substr($value,0,3) == 'set'){
			$fullPath = $node.'-'.$cc.'-'.$endpoint.'-'.$property;
			$val = explode('-',$value,2)[1];
			$eqLogic->setNodeValue($fullPath,$val);
			return;
		}
		if ($cc == 0 && $endpoint== 0){
			$args=array('args'=>array(intval($node)));
			zwavejs::publishMqttApi($property,$args);
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
			$value = strval(str_replace('#color#', $_options['color'], $value));
		}
		zwavejs::publishMqttValue($node,$path,$value);
	}
	
}