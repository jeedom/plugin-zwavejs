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

$("body").off("click", ".refreshNodeCC").on("click", ".refreshNodeCC", function (e) {
  jeedom.zwavejs.node.refreshNodeCC({
    nodeId : $(this).attr('data-nodeId'),
    cc :  $(this).attr('data-cc'),
    error: function (error) {
      $('#div_nodeValuesZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      $('#div_nodeValuesZwaveJsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});

$("body").off("click", ".editValue").on("click", ".editValue", function (e) {
  var title = '{{Changer la valeur de}} '+ $(this).data('label')+ ' ? ';
  title += '{{La valeur actuelle est : }} ';
  var valueApplyOption={
    fullpath : $(this).data('path'),
  };
  if ($(this).data('list') == 1) {
    var options = [];
    var states = $(this).data('states').split(";");
    for (i in states) {
		var details = states[i].split("-");
		options.push({value : details[0],text : details[0]+'-'+details[1]})
	}
    bootbox.prompt({
      title: title,
      inputType: 'select',
      inputOptions : options,
      callback: function (result) {
        if(result === null){
          return;
        }
        valueApplyOption.value = result;
        jeedom.zwavejs.node.set(valueApplyOption);
      }
    });
  } else if ($(this).data('type') == "boolean") {
    bootbox.prompt({
      title: title,
      inputType: 'select',
      inputOptions: [
        {text: '{{On}}',value: 'true'},
        {text: '{{Off}}',value: 'false'}
      ],
      callback: function (result) {
        if(result === null){
          return;
        }
        valueApplyOption.value = result;
        jeedom.zwavejs.node.set(valueApplyOption);
      }
    });
  } else {
    var result = prompt(title);
    if(result === null){
      return;
    }
    valueApplyOption.value = result;
    jeedom.zwavejs.node.set(valueApplyOption);
  }
});

$("body").off("click", ".configPolling").on("click", ".configPolling", function (e) {
  var valueApplyOption={
    nodeId : $(this).data('nodeId'),
    cc : $(this).data('cc'),
    endpoint : $(this).data('endpoint'),
  };
  var title = '{{Configurer le Polling de }} '+ $(this).data('label')+ ' ? <br><b> {{Attention ne jamais utiliser de polling sauf cas nécessaire et obligatoire, un polling allourdi le réseau.}}</b>';
    var options = [];
	options.push({value : '',text : 'Aucun'})
	options.push({value : '1',text : '1 minute'})
	options.push({value : '5',text : '5 minutes'})
	options.push({value : '15',text : '15 minutes'})
	options.push({value : '30',text : '30 minutes'})
	options.push({value : '60',text : '60 minutes'})
    bootbox.prompt({
      title: title,
      inputType: 'select',
      inputOptions : options,
      callback: function (result) {
        if(result === null){
          return;
        }
        valueApplyOption.value = result;
        jeedom.zwavejs.node.setPolling(valueApplyOption);
      }
    }); 
});

$("body").off("click", ".createCommandInfo").on("click", ".createCommandInfo", function (e) {
	var obj = $(this);
  bootbox.confirm('{{Etes-vous sûr de vouloir créer cette commande info : }}' +obj.data('label')+ ' ('+obj.data('path')+'{{) ? Si elle existe déjà elle ne sera pas créée.}}', function (result) {
	if (result) {
		$.ajax({
		type: "POST",
		url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
		data: {
			action: "createCommandInfo",
			type: obj.data('type'),
			label: obj.data('label'),
			path: obj.data('path'),
			unit: obj.data('unit'),
			max: obj.data('max'),
			min: obj.data('min'),
			value: obj.data('value')
		},
		dataType: 'json',
		global: false,
		error: function (request, status, error) {
			handleAjaxError(request, status, error);
		},
		success: function (data) {
			if (data.state != 'ok') {
			$('#div_alert').showAlert({message: data.result, level: 'danger'});
			return;
			}
			$('#div_alert').showAlert({message: '{{Opération réalisée avec succès}}', level: 'success'});
			$('.eqLogicDisplayCard[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click();
		}
		});
	}
	});
});

function node_load_values(){
  jeedom.zwavejs.node.info({
    info : 'getNodeValues',
    node : nodeId,
    global:false,
    error: function (error) {
      $('#div_nodeValuesZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
	  if ($('.modalNodeValues').is(":visible")) {
		setTimeout(function(){ node_load_values(); }, 2000);
	  }
    },
    success: function () {
		if ($('.modalNodeValues').is(":visible")) {
			setTimeout(function(){ node_load_values(); }, 2000);
		}
	}
  })
}

function node_read_values(){
  jeedom.zwavejs.file.get({
    node : nodeId,
    type : 'nodeValues',
    global:false,
    error: function (error) {
      $('#div_nodeInformationsZwaveJsAlert').showAlert({message: error.message, level: 'danger'});
	if ($('.modalNodeValues').is(":visible")) {
		setTimeout(function(){ node_read_values(); }, 2000);
	  }
    },
    success: function (nodeValues) {
		if (nodeValues['id'] == nodeId){
			if (nodeValues['status']=='Dead'){
				$('.getNodeInfo-nodeValues').empty().append('<div class="alert alert-warning" role="alert">Le noeud est en statut Dead, il n\'y a donc pas de valeurs dynamiques à afficher ...</div>');
			}
			else if ($('.panel-group').is(":visible")) {
				for (value in nodeValues['nodeValues']['updates']){
					$('.'+ $.escapeSelector(value)).empty().append(nodeValues['nodeValues']['updates'][value]['value']);
					$('.'+ $.escapeSelector(value+'_lastUpdate')).empty().append(nodeValues['nodeValues']['updates'][value]['lastUpdate']);
				}
			} else {
				$('.getNodeInfo-nodeValues').empty().append(nodeValues['nodeValues']['init']);
			}
		}
		if ($('.modalNodeValues').is(":visible")) {
			setTimeout(function(){ node_read_values(); }, 2000);
		}
	}
  })
}

$('#div_nodeValuesZwaveJsAlert').showAlert({message: '{{Chargement des infos en cours ...}}', level: 'warning'});
node_load_values();
node_read_values();
