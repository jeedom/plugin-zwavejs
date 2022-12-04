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

try {
	require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';
	include_file('core', 'authentification', 'php');

	if (!isConnect('admin')) {
		throw new Exception('401 Unauthorized');
	}
	
	ajax::init(array('uploadNVMbackup'));

	if (init('action') == 'include') {
		zwavejs::inclusion(init('method'), init('options'));
		ajax::success();
	}

	if (init('action') == 'getInfo') {
		zwavejs::getInfo();
		ajax::success();
	}

	if (init('action') == 'getNodeInfo') {
		zwavejs::getNodeInfo(init('node'), init('info'));
		ajax::success();
	}

	if (init('action') == 'getNodeAssociations') {
		zwavejs::getNodeAssociations(init('nodeId'));
		ajax::success();
	}

	if (init('action') == 'setNodeValue') {
		zwavejs::setNodeValue(init('fullpath'), init('value'));
		ajax::success();
	}

	if (init('action') == 'getFile') {
		ajax::success(zwavejs::getFile(init('type'), init('node')));
	}

	if (init('action') == 'setPolling') {
		zwavejs::setPolling(init('nodeId'), init('cc'), init('endpoint'), init('property'), init('value'));
		ajax::success();
	}

	if (init('action') == 'refreshNodeCC') {
		zwavejs::refreshNodeCC(init('nodeId'), init('cc'));
		ajax::success();
	}

	if (init('action') == 'getNodes') {
		zwavejs::getNodes(init('mode'));
		ajax::success();
	}

	if (init('action') == 'controllerAction') {
		zwavejs::controllerAction(init('type'));
		ajax::success();
	}

	if (init('action') == 'namingAction') {
		zwavejs::namingAction(init('nodeId'));
		ajax::success();
	}

	if (init('action') == 'nodeAction') {
		zwavejs::nodeAction(init('type'), init('nodeId'));
		ajax::success();
	}

	if (init('action') == 'removeAssociation') {
		zwavejs::removeAssociation(init('nodeId'), init('groupId'), init('sourceEndpoint'), init('targetEndpoint'), init('assoNodeId'));
		ajax::success();
	}

	if (init('action') == 'removeAllAssociations') {
		zwavejs::removeAllAssociations(init('nodeId'));
		ajax::success();
	}

	if (init('action') == 'addAssociation') {
		zwavejs::addAssociation(init('nodeId'), init('group'), init('target'));
		ajax::success();
	}

	if (init('action') == 'applyRecommended') {
		$eqLogic = zwavejs::byLogicalId(init('nodeId'), 'zwavejs');
		if (!is_object($eqLogic)) {
			ajax::success();
		}
		ajax::success($eqLogic->applyRecommended());
	}

	if (init('action') == 'createCommandInfo') {
		ajax::success(zwavejs::autoCreateCommandInfo(init('path'), init('type'), init('label'), init('unit'), init('max'), init('min'), init('value')));
	}

	if (init('action') == 'createCommandAction') {
		ajax::success(zwavejs::autoCreateCommandAction(init('path'), init('type'), init('label'), init('unit'), init('max'), init('min'), init('value')));
	}

	if (init('action') == 'generateRandomKey') {
		ajax::success(zwavejs::generateRandomKey());
	}

	if (init('action') == 'autoDetectModule') {
		$eqLogic = zwavejs::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('Equipement Z-Wave non trouvé', __FILE__) . ' : ' . init('id'));
		}
		if (init('createcommand') == 1) {
			foreach ($eqLogic->getCmd() as $cmd) {
				$cmd->remove();
			}
		}
		$eqLogic->createCommand(init('createcommand'));
		ajax::success();
	}

	if (init('action') == 'getEqLogicInfos') {
		$eqLogic = zwavejs::byLogicalId(init('logicalId'), 'zwavejs');
		if (!is_object($eqLogic)) {
			ajax::success();
		}
		ajax::success($eqLogic->getEqLogicInfos());
	}
	
	if (init('action') == 'getWaiting') {
		ajax::success(zwavejs::getWaiting());
	}
	
	if (init('action') == 'removeWaiting') {
		$eqLogic = zwavejs::byLogicalId(init('logicalId'), 'zwavejs');
		if (is_object($eqLogic)) {
			$waiting = $eqLogic->getCache('waiting',array());
			if (isset($waiting[init('property')])){
				unset($waiting[init('property')]);
				$eqLogic->setCache('waiting',$waiting);
			}
		}
		ajax::success(zwavejs::getWaiting());
	}
	
	if (init('action') == 'listNVMbackup') {
		$list = array();
		foreach (ls(dirname(__FILE__) . '/../../data/store/backups/nvm', '*.bin', false, array('files', 'quiet')) as $file) {
			$list[] = array('folder'=>dirname(__FILE__) . '/../../data/store/backups/nvm/'. $file,'name'=>$file);
		}
		ajax::success($list);
	}
	
	if (init('action') == 'deleteNVMbackup') {
		$file = init('backup');
		if (file_exists($file)){
			unlink($file);
		}
		ajax::success();
	}
	
	if (init('action') == 'restoreNVMbackup') {
		$file = init('backup');
		if (file_exists($file)){
			$content = file_get_contents($file);
			$byteArr = str_split($content);
			foreach ($byteArr as $key=>$val) { 
				$byteArr[$key] = ord($val); 
			}
			zwavejs::publishMqttApi('restoreNVM', array("args"=>array(array("type"=>"Buffer","data"=>$byteArr))));
		} else {
			throw new Exception(__('Aucun fichier trouvé.', __FILE__));
		}
		ajax::success();
	}
	
	if (init('action') == 'uploadNVMbackup') {
		$uploaddir = dirname(__FILE__). '/../../data/store/backups/nvm';
		if (!file_exists($uploaddir)) {
			mkdir($uploaddir);
		}
		if (!file_exists($uploaddir)) {
			throw new Exception(__('Répertoire de téléversement non trouvé : ', __FILE__) . $uploaddir);
		}
		if (!isset($_FILES['file'])) {
			throw new Exception(__('Aucun fichier trouvé. Vérifiez le paramètre PHP (post size limit)', __FILE__));
		}
		$extension = strtolower(strrchr($_FILES['file']['name'], '.'));
		if (!in_array($extension, array('.bin'))) {
			throw new Exception('Extension du fichier non valide (autorisé .bin) : ' . $extension);
		}
		if (filesize($_FILES['file']['tmp_name']) > 500000) {
			throw new Exception(__('Le fichier est trop gros (maximum 500ko)', __FILE__));
		}
		if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploaddir . '/' . $_FILES['file']['name'])) {
			throw new Exception(__('Impossible de déplacer le fichier temporaire', __FILE__));
		}
		if (!file_exists($uploaddir . '/' . $_FILES['file']['name'])) {
			throw new Exception(__('Impossible de téléverser le fichier (limite du serveur web ?)', __FILE__));
		}
		ajax::success();
	}

	throw new Exception(__('Aucune méthode correspondante', __FILE__));
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayException($e), $e->getCode());
}
