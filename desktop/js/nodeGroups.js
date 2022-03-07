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
var selectGroup = '';
$("body").off("click", ".removeAssociation").on("click", ".removeAssociation", function (e) {
  jeedom.zwavejs.node.removeAssociation({
    nodeId : $(this).data('nodeid'),
    groupId : $(this).data('groupid'),
	sourceEndpoint: $(this).data('sourceendpoint'),
	targetEndpoint:$(this).data('targetendpoint'),
	assoNodeId:$(this).data('assonodeid'),
    error: function (error) {
      $('#div_nodeGroupsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      $('#div_nodeGroupsZwaveJsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});

$("body").off("click", ".removeAllAssociations").on("click", ".removeAllAssociations", function (e) {
  jeedom.zwavejs.node.removeAllAssociations({
    nodeId : nodeId,
    error: function (error) {
      $('#div_nodeGroupsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      $('#div_nodeGroupsZwaveJsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});

$("body").off("click", ".addAssociation").on("click", ".addAssociation", function (e) {
  if ($( ".selectGroup option:selected" ).value() == '' ){
	$('#div_nodeAddGroupsZwaveJsAlert').showAlert({message: '{{Vous devez choisir un groupe pour ajouter une association}}', level: 'danger'});
	return;
  }
  if ($( ".selectTargetNode option:selected" ).value() == '' ){
	$('#div_nodeAddGroupsZwaveJsAlert').showAlert({message: '{{Vous devez choisir un noeud cible pour ajouter une association}}', level: 'danger'});
	return;
  }
  
  jeedom.zwavejs.node.addAssociation({
    nodeId : nodeId,
    group : $( ".selectGroup option:selected" ).value(),
    target : $( ".selectTargetNode option:selected" ).value(),
    error: function (error) {
      $('#div_nodeGroupsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      $('#div_nodeGroupsZwaveJsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});

function node_load_groups(){
  jeedom.zwavejs.node.getAssociations({
    nodeId : nodeId,
    global:false,
    error: function (error) {
      $('#div_nodeGroupsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
	  if ($('.modalNodeGroups').is(":visible")) {
		setTimeout(function(){ node_load_groups(); }, 2000);
	  }
    },
    success: function () {
		if ($('.modalNodeGroups').is(":visible")) {
			setTimeout(function(){ node_load_groups(); }, 2000);
		}
	}
  })
}

function network_load_AllNodes(){
  jeedom.zwavejs.network.getNodes({
    info : 'getNodes',
    mode : 'group',
    global:false,
    error: function (error) {
      $('#div_networkOpenzwaveAlert').showAlert({message: error.message, level: 'danger'});
    }
  })
}

$('body').off('zwavejs::getNodeGroup').on('zwavejs::getNodeGroup', function (_event, _options) {
	nodes = _options;
	node_load_groups();
});

$('body').off('zwavejs::getNodeAssociations').on('zwavejs::getNodeAssociations', function (_event, _options) {
	$('#div_nodeGroupsZwaveJsAlert').hideAlert();
	if (_options['id'] == nodeId){
		var body ='';
		var bodylist ='';
		for (group in nodes[nodeId]['groups']){
			groupInfo = nodes[nodeId]['groups'][group]
			bodylist +='<tr><td>'+groupInfo['value']+' - '+groupInfo['text']+'</td><td>'+groupInfo['endpoint']+'</td><td>'+groupInfo['isLifeline']+'</td><td>'+groupInfo['maxNodes']+'</td></tr>';
		}
		for (item in _options['data']){
			asso = _options['data'][item]
			groupId = asso['groupId']
			if (nodeId in nodes) {
				nodeGroups = nodes[nodeId]['groups']
				for (groupNode in nodeGroups){
					if (nodeGroups[groupNode]['value'] == asso['groupId'] && nodeGroups[groupNode]['endpoint'] == asso['endpoint']) {
						groupId += ' - ' +nodeGroups[groupNode]['text']
						break;
					}
				}
			}
			nodeAsso = asso['nodeId']
			if (nodeAsso in nodes) {
				if (nodeAsso in eqLogic_human_name) {
					nodeAsso += ' - ' +eqLogic_human_name[nodeAsso]
				} else {
					nodeAsso += ' - ' +nodes[nodeAsso]['label']
				}
			}
			if (typeof asso['targetEndpoint'] !== 'undefined'){
				targetEndpoint = asso['targetEndpoint']
			} else {
				targetEndpoint = 'Root'
			}
			body += '<tr><td>' + asso['endpoint'] + '</td><td>'+groupId+'</td><td>'+nodeAsso+'</td><td>'+targetEndpoint+'</td>'
			body += '<td><a class="btn btn-xs btn-danger removeAssociation" data-nodeid="'+nodeId+'" data-groupid="'+asso['groupId']+'" data-sourceendpoint="'+asso['endpoint']+'"'
			body += ' data-targetendpoint="'+targetEndpoint+'" data-assonodeid="'+asso['nodeId']+'" style="text-align: right;display:inline-block"><i class="fas fa-trash"></i></a></td></tr>';
		}
		$('.tableAssociations tbody').empty().append(body);
		$('.tableGroups tbody').empty().append(bodylist);
		listGroups = nodes[nodeId]['groups'];
		if (selectGroup == ''){
			selectGroup = '<option value="">{{Aucun}}</option>';
			for (group in listGroups){
				selectGroup += '<option value='+listGroups[group]['endpoint']+'-'+listGroups[group]['value']+'>'+listGroups[group]['value']+' - ' + listGroups[group]['text'] + ' (' + +listGroups[group]['endpoint']+')</option>'
			}
			$('.selectGroup').empty().append(selectGroup);
			var selectTargetNode = '<option value="">{{Aucun}}</option>';
			for (node in nodes){
				if (node in eqLogic_human_name) {
					nameselect = eqLogic_human_name[node]
				} else {
					nameselect = node
				}
				selectTargetNode += '<option value=root-'+node+'>'+node+' - '+nameselect+' (Root)</option>'
				selectTargetNode += '<option value=0-'+node+'>'+node+' - '+nameselect+' (0)</option>'
				for (endpoint in nodes[node]['endpoints']){
					selectTargetNode += '<option value='+nodes[node]['endpoints'][endpoint]+'-'+node+'>'+node+' - '+nameselect+' ('+nodes[node]['endpoints'][endpoint]+')</option>'
				}
			}
			$('.selectTargetNode').empty().append(selectTargetNode);
		}
	}
});

$('#div_nodeGroupsZwaveJsAlert').showAlert({message: '{{Chargement des infos en cours ...}}', level: 'warning'});
network_load_AllNodes();
