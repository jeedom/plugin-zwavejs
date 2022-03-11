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

	if (init('action') == 'include') {
		zwavejs::inclusion(init('method'),init('options'));
		ajax::success();
	}
	
	if (init('action') == 'getInfo') {
		zwavejs::getInfo();
		ajax::success();
	}
	
	if (init('action') == 'getNodeInfo') {
		zwavejs::getNodeInfo(init('node'));
		ajax::success();
	}
	
	if (init('action') == 'getNodeAssociations') {
		zwavejs::getNodeAssociations(init('nodeId'));
		ajax::success();
	}
	
	if (init('action') == 'setNodeValue') {
		zwavejs::setNodeValue(init('fullpath'),init('value'));
		ajax::success();
	}
	
	if (init('action') == 'refreshNodeCC') {
		zwavejs::refreshNodeCC(init('nodeId'),init('cc'));
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
		zwavejs::nodeAction(init('type'),init('nodeId'));
		ajax::success();
	}
	
	if (init('action') == 'removeAssociation') {
		zwavejs::removeAssociation(init('nodeId'),init('groupId'),init('sourceEndpoint'),init('targetEndpoint'),init('assoNodeId'));
		ajax::success();
	}
	
	if (init('action') == 'removeAllAssociations') {
		zwavejs::removeAllAssociations(init('nodeId'));
		ajax::success();
	}
	
	if (init('action') == 'addAssociation') {
		zwavejs::addAssociation(init('nodeId'),init('group'),init('target'));
		ajax::success();
	}
	
	if (init('action') == 'applyRecommended') {
		$eqLogic = zwavejs::byLogicalId(init('nodeId'),'zwavejs');
		if (!is_object($eqLogic)) {
			ajax::success();
		}
		ajax::success($eqLogic->applyRecommended());
	}
	if (init('action') == 'createCommandInfo') {
		ajax::success(zwavejs::autoCreateCommandInfo(init('path'),init('type'),init('label'),init('unit'),init('max'),init('min'),init('value')));
	}
	
	if (init('action') == 'generateRandomKey') {
		ajax::success(zwavejs::generateRandomKey());
	}

	if (init('action') == 'autoDetectModule') {
		$eqLogic = zwavejs::byId(init('id'));
		if (!is_object($eqLogic)) {
			throw new Exception(__('Zwave eqLogic non trouvé : ', __FILE__) . init('id'));
		}
		if (init('createcommand') == 1){
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

	throw new Exception('Aucune methode correspondante');
	/*     * *********Catch exeption*************** */
} catch (Exception $e) {
	ajax::error(displayException($e), $e->getCode());
}