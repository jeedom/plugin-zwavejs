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

$("body").off("click", ".nodeAction").on("click", ".nodeAction", function (e) {
  jeedom.zwavejs.node.action({
    action : $(this).data('action'),
    nodeId : nodeId,
    error: function (error) {
      $('#div_nodeInformationsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      $('#div_nodeInformationsZwaveJsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});

$("body").off("click", ".namingAction").on("click", ".namingAction", function (e) {
  jeedom.zwavejs.controller.namingAction({
    action : 'namingAction',
    nodeId : nodeId,
    error: function (error) {
      $('#div_nodeInformationsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      $('#div_nodeInformationsZwaveJsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});

function network_load_nodes(){
  jeedom.zwavejs.node.info({
    info : 'getNodeInfo',
    node : nodeId,
    global:false,
    error: function (error) {
      $('#div_nodeInformationsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
	if ($('.modalNodeInformations').is(":visible")) {
		setTimeout(function(){ network_load_nodes(); }, 2000);
	  }
    },
    success: function () {
		if ($('.modalNodeInformations').is(":visible")) {
			setTimeout(function(){ network_load_nodes(); }, 2000);
		}
	}
  })
}


$('body').off('zwavejs::getNodeInfo').on('zwavejs::getNodeInfo', function (_event, _options) {
	$('#div_nodeInformationsZwaveJsAlert').hideAlert();
	if (_options['id'] == nodeId){
		$('#div_nodeDataTree').empty().html(JSONTree.create(_options));
		for (key in _options){
			data = _options[key];
			if (key == 'statistics') {
				for (stat in data) {
					valueStat = data[stat]
					$('.getNodeStats-'+stat).empty().append(valueStat); 
				}
			} else {
				if (key == 'dbLink'){
					html = '<a href="'+data.toString()+'" target="_blank"><i class="fas fa-book"></i></a>'
					$('.getNodeInfo-'+key).empty().append(html);
				} else if (key == 'lastActive'){
					$('.getNodeInfo-'+key).empty().append(jeedom.zwavejs.timestampConverter(data/1000));
				} else {
					$('.getNodeInfo-'+key).empty().append(data.toString());
				}
			}
		}
	}
});
$('#div_nodeInformationsZwaveJsAlert').showAlert({message: '{{Chargement des infos en cours ...}}', level: 'warning'});
network_load_nodes();