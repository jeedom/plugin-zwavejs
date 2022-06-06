/* This file is part of Plugin openzwave for jeedom.
*
* Plugin openzwave for jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Plugin openzwave for jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Plugin openzwave for jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

$("body").off("click", ".nodeAction").on("click", ".nodeAction", function(e) {
	jeedom.zwavejs.node.action({
		action: $(this).data('action'),
		nodeId: nodeId,
		error: function(error) {
			$('#div_nodeInformationsZwaveJsAlert').showAlert({ message: error.message, level: 'danger' })
		},
		success: function() {
			$('#div_nodeInformationsZwaveJsAlert').showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
		}
	})
})

$("body").off("click", ".namingAction").on("click", ".namingAction", function(e) {
	jeedom.zwavejs.controller.namingAction({
		action: 'namingAction',
		nodeId: nodeId,
		error: function(error) {
			$('#div_nodeInformationsZwaveJsAlert').showAlert({ message: error.message, level: 'danger' })
		},
		success: function() {
			$('#div_nodeInformationsZwaveJsAlert').showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
		}
	})
})

function network_load_nodes() {
	jeedom.zwavejs.node.info({
		info: 'getNodeInfo',
		node: nodeId,
		global: false,
		error: function(error) {
			$('#div_nodeInformationsZwaveJsAlert').showAlert({ message: error.message, level: 'danger' })
			if ($('.modalNodeInformations').is(":visible")) {
				infoloadnodes = setTimeout(function() { network_load_nodes() }, 2000)
			}
		},
		success: function() {
			if ($('.modalNodeInformations').is(":visible")) {
				infoloadnodes = setTimeout(function() { network_load_nodes() }, 2000)
			}
		}
	})
}

function read_nodes() {
	jeedom.zwavejs.file.get({
		node: nodeId,
		type: 'nodeInfo',
		global: false,
		error: function(error) {
			$('#div_nodeInformationsZwaveJsAlert').showAlert({ message: error.message, level: 'danger' })
			if ($('.modalNodeInformations').is(":visible")) {
				readnodes = setTimeout(function() { read_nodes() }, 2000)
			}
		},
		success: function(nodeData) {
			if (nodeData['id'] == nodeId) {
				$('#div_nodeDataTree').empty().html(JSONTree.create(nodeData))
				for (key in nodeData) {
					data = nodeData[key]
					if (key == 'statistics') {
						for (stat in data) {
							valueStat = data[stat]
							$('.getNodeStats-' + stat).empty().append(valueStat)
						}
					} else if (key == 'dbLink') {
						html = '<a href="' + data.toString() + '" target="_blank"><i class="fas fa-book"></i></a>'
						$('.getNodeInfo-' + key).empty().append(html)
					} else if (key == 'lastActive') {
						$('.getNodeInfo-' + key).empty().append(jeedom.zwavejs.timestampConverter(data / 1000))
					} else if (key == 'status') {
						if (data == 'Asleep' || data == 'Awake') {
							$('.wakeupInfo').show()
						} else {
							$('.wakeupInfo').hide()
						}
						if (data == 'Asleep') {
							newdata = '<span title="Sleeping" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">z<sup>z<sup>z</sup></sup></i></span>'
						} else if (data == 'Dead') {
							newdata = '<span title="Dead" style="font-size : 1.5em;"><i class="fas fa-skull-crossbones icon_red" aria-hidden="true"></i></span>'
						} else if (data == 'Alive') {
							newdata = '<span title="Alive" style="font-size : 1.5em;"><i class="fas fa-check icon_green" aria-hidden="true"></i></span>'
						} else if (data == 'Awake') {
							newdata = '<span title="Awake" style="font-size : 1.5em;"><i class="fas fa-grin icon_green" aria-hidden="true"></i></span>'
						} else {
							newdata = '<span title="Other" style="font-size : 1.5em;"><i class="icon_orange" aria-hidden="true">' + data + '</i></span>'
						}
						$('.getNodeInfo-' + key).empty().append(newdata)
					} else {
						$('.getNodeInfo-' + key).empty().append(data.toString())
					}
				}
			}
			if ($('.modalNodeInformations').is(":visible")) {
				readnodes = setTimeout(function() { read_nodes() }, 2000)
			}
		}
	})
}


$('#md_modal').bind('dialogclose', function(event, ui) {
	clearTimeout(readnodes)
	clearTimeout(infoloadnodes)
})

$('#div_nodeInformationsZwaveJsAlert').showAlert({ message: '{{Chargement des informations en cours}}...', level: 'warning' })
network_load_nodes()
read_nodes()
