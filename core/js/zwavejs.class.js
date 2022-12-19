
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


jeedom.zwavejs = function() {
}

jeedom.zwavejs.durationConvert = function(d) {
	n = Number(d)
	var d = Math.floor(n / 86400)
	var h = Math.floor(n % 86400 / 3600)
	var m = Math.floor(n % 3600 / 60)
	var s = Math.floor(n % 3600 % 60)
	return ((d > 0 ? d + " {{jour(s)}} " : "") + (h > 0 ? h + " {{heure(s)}} " : "") + (m > 0 ? m + " {{minute(s)}} " : "") + (s > 0 ? s + " {{secondes(s)}} " : ""))
}

jeedom.zwavejs.timestampConverter = function(time) {
	if (time == 1) {
		return "N/A"
	}
	var ret
	var date = new Date(time * 1000)
	var hours = date.getHours()
	if (hours < 10) {
		hours = "0" + hours
	}
	var minutes = date.getMinutes()
	if (minutes < 10) {
		minutes = "0" + minutes
	}
	var seconds = date.getSeconds()
	if (seconds < 10) {
		seconds = "0" + seconds
	}
	var num = date.getDate()
	if (num < 10) {
		num = "0" + num
	}
	var month = date.getMonth() + 1
	if (month < 10) {
		month = "0" + month
	}
	var year = date.getFullYear()
	var formattedTime = hours + ':' + minutes + ':' + seconds
	var formattedDate = num + "/" + month + "/" + year
	return formattedDate + ' ' + formattedTime
}

jeedom.zwavejs.normalizeClass = function(cc) {
	if (typeof cc === 'string' && cc.indexOf('0x') >= 0) {
		return parseInt(cc, 16)
	}
	return cc
}

/*************************Controller************************************************/

jeedom.zwavejs.controller = function() {
}

jeedom.zwavejs.controller.action = function(_params) {
	var paramsRequired = ['action']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'controllerAction',
		type: _params.action,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.controller.namingAction = function(_params) {
	var paramsRequired = ['action', 'nodeId']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'namingAction',
		nodeId: _params.nodeId,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.controller.include = function(_params) {
	var paramsRequired = ['method', 'options']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'include',
		method: _params.method,
		options: _params.options,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.controller.grantSecurity = function(_params) {
	var paramsRequired = ['security', 'auth']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'grantSecurity',
		security: _params.security,
		auth: _params.auth,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.controller.validateDSK = function(_params) {
	var paramsRequired = ['dsk']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'validateDSK',
		dsk: _params.dsk
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.controller.abortInclusion = function(_params) {
	var paramsRequired = []
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'abortInclusion'
	}
	$.ajax(paramsAJAX)
}

/*************************Node************************************************/
jeedom.zwavejs.node = function() {
}

jeedom.zwavejs.node.info = function(_params) {
	var paramsRequired = ['info', 'node']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'getNodeInfo',
		info: _params.info,
		node: _params.node
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.node.set = function(_params) {
	var paramsRequired = ['fullpath', 'value']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'setNodeValue',
		fullpath: _params.fullpath,
		value: _params.value,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.node.setPolling = function(_params) {
	var paramsRequired = ['nodeId', 'cc', 'value', 'endpoint', 'property']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'setPolling',
		nodeId: _params.nodeId,
		cc: _params.cc,
		endpoint: _params.endpoint,
		property: _params.property,
		value: _params.value,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.node.refreshNodeCC = function(_params) {
	var paramsRequired = ['nodeId', 'cc']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'refreshNodeCC',
		nodeId: _params.nodeId,
		cc: _params.cc,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.node.getAssociations = function(_params) {
	var paramsRequired = ['nodeId']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'getNodeAssociations',
		nodeId: _params.nodeId
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.node.action = function(_params) {
	var paramsRequired = ['action', 'nodeId']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'nodeAction',
		type: _params.action,
		nodeId: _params.nodeId,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.node.removeAssociation = function(_params) {
	var paramsRequired = ['nodeId', 'groupId', 'sourceEndpoint', 'targetEndpoint', 'assoNodeId']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'removeAssociation',
		nodeId: _params.nodeId,
		groupId: _params.groupId,
		sourceEndpoint: _params.sourceEndpoint,
		targetEndpoint: _params.targetEndpoint,
		assoNodeId: _params.assoNodeId,
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.node.removeAllAssociations = function(_params) {
	var paramsRequired = ['nodeId']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'removeAllAssociations',
		nodeId: _params.nodeId,
	}
	$.ajax(paramsAJAX)
}
jeedom.zwavejs.node.addAssociation = function(_params) {
	var paramsRequired = ['nodeId', 'group', 'target']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'addAssociation',
		nodeId: _params.nodeId,
		group: _params.group,
		target: _params.target,
	}
	$.ajax(paramsAJAX)
}


/*************************File************************************************/
jeedom.zwavejs.file = function() {
}

jeedom.zwavejs.file.get = function(_params) {
	var paramsRequired = ['node', 'type']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'getFile',
		type: _params.type,
		node: _params.node
	}
	$.ajax(paramsAJAX)
}

/*************************Waiting************************************************/
jeedom.zwavejs.waiting = function() {
}

jeedom.zwavejs.waiting.get = function(_params) {
	var paramsRequired = []
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'getWaiting'
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.waiting.remove = function(_params) {
	var paramsRequired = ['logical','property']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'removeWaiting',
		logicalId: _params.logical,
		property: _params.property
	}
	$.ajax(paramsAJAX)
}

/*************************network************************************************/

jeedom.zwavejs.network = function() {
}

jeedom.zwavejs.network.info = function(_params) {
	var paramsRequired = ['info']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'getInfo',
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.network.getNodes = function(_params) {
	var paramsRequired = ['info', 'mode']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'getNodes',
		mode: _params.mode
	}
	$.ajax(paramsAJAX)
}

jeedom.zwavejs.network.refreshNeighbors = function(_params) {
	var paramsRequired = ['info']
	var paramsSpecifics = {}
	try {
		jeedom.private.checkParamsRequired(_params || {}, paramsRequired)
	} catch (e) {
		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e)
		return
	}
	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {})
	var paramsAJAX = jeedom.private.getParamsAJAX(params)
	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php'
	paramsAJAX.data = {
		action: 'refreshNeighbors'
	}
	$.ajax(paramsAJAX)
}

/*************************backup************************************************/

jeedom.zwavejs.nvmbackup = function() {
 };

 jeedom.zwavejs.nvmbackup.list = function (_params) {
 	var paramsRequired = [];
 	var paramsSpecifics = {};
 	try {
 		jeedom.private.checkParamsRequired(_params || {}, paramsRequired);
 	} catch (e) {
 		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e);
 		return;
 	}
 	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {});
 	var paramsAJAX = jeedom.private.getParamsAJAX(params);
	paramsAJAX.global= false;
 	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php';
 	paramsAJAX.data = {
 		action: 'listNVMbackup'
 	};
 	$.ajax(paramsAJAX);
 }
 
 jeedom.zwavejs.nvmbackup.restore = function (_params) {
 	var paramsRequired = ['backup'];
 	var paramsSpecifics = {};
 	try {
 		jeedom.private.checkParamsRequired(_params || {}, paramsRequired);
 	} catch (e) {
 		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e);
 		return;
 	}
 	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {});
 	var paramsAJAX = jeedom.private.getParamsAJAX(params);
	paramsAJAX.global= false;
 	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php';
 	paramsAJAX.data = {
		backup: _params.backup,
 		action: 'restoreNVMbackup'
 	};
 	$.ajax(paramsAJAX);
 }
 
  jeedom.zwavejs.nvmbackup.delete = function (_params) {
 	var paramsRequired = ['backup'];
 	var paramsSpecifics = {};
 	try {
 		jeedom.private.checkParamsRequired(_params || {}, paramsRequired);
 	} catch (e) {
 		(_params.error || paramsSpecifics.error || jeedom.private.default_params.error)(e);
 		return;
 	}
 	var params = $.extend({}, jeedom.private.default_params, paramsSpecifics, _params || {});
 	var paramsAJAX = jeedom.private.getParamsAJAX(params);
	paramsAJAX.global= false;
 	paramsAJAX.url = 'plugins/zwavejs/core/ajax/zwavejs.ajax.php';
 	paramsAJAX.data = {
		backup: _params.backup,
 		action: 'deleteNVMbackup'
 	};
 	$.ajax(paramsAJAX);
 }
