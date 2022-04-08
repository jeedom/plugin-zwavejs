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

var nodes = {};
var networkTree = {};

$('#bt_syncEqLogic').off('click').on('click', function () {
  jeedom.zwavejs.network.getNodes({
    info : 'getNodes',
    mode : 'sync',
    global:false
  })
});

$('.confRecommended').on('click', function () {
    bootbox.dialog({
      title: "{{Configuration recommandée}}",
      message: '<form class="form-horizontal"> ' +
      '<label class="control-label" > {{Voulez-vous appliquer le jeu de configuration recommandée par l\'équipe Jeedom ?}} </label> ' +
      '<br><br>' +
      '<ul>' +
      '<li class="active">{{Paramètres.}}</li>' +
      '<li class="active">{{Associations.}}</li>' +
      '<li class="active">{{Intervalle de réveil.}}</li>' +
      '</ul>' +
      '</form>',
      buttons: {
        main: {
          label: "{{Annuler}}",
          className: "btn-danger",
          callback: function () {
          }
        },
        success: {
          label: "{{Appliquer}}",
          className: "btn-success",
          callback: function () {
            $.ajax({
              type: "POST",
              url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
              data: {
                action: "applyRecommended",
                nodeId: $('.eqLogicAttr[data-l1key=logicalId]').value(),
              },
              global:false,
              dataType: 'json',
              error: function (request, status, error) {
                handleAjaxError(request, status, error);
              },
              success: function (data) {
                if (data.state != 'ok') {
                  $('#div_alert').showAlert({message: data.result, level: 'danger'});
                  return;
                }
                if (data.result == "wakeup") {
                  $('#div_alert').showAlert({
                    message: '{{Configuration appliquée. Cependant ce module nécessite un réveil pour que celle-ci soit effective.}}',
                    level: 'success'
                  });
                } else {
                  $('#div_alert').showAlert({
                    message: '{{Configuration appliquée et effective.}}',
                    level: 'success'
                  });
                }
              }
            });
          }
        }
      }
    }
  );
});

$('.changeIncludeState').off('click').on('click', function () {
  var dialog_title = '{{Inclusion/Exclusion}}';
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
  dialog_title = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Inclusion/Exclusion}}';
  dialog_message += '<label class="control-label" > {{Sélectionnez le mode}} </label> ' +
  '<div> <div class="radio"> <label > ' +
  '<input type="radio" name="inclusion" method="include" id="0" options="2" checked="checked"> <i class="fas fa-plus" style="color:green"></i> {{Inclusion par défaut}} </label> ' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="inclusion" method="include" id="1" options="3"> <i class="fas fa-lock" style="color:orange"></i> {{Inclusion sécurisée forcée S0}}</label> ' +
  '</div> ' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="inclusion" method="include" id="2" options="1"> <i class="fas fa-qrcode" style="color:blue"></i> {{Inclusion sécurisée forcée S2}}</label> ' +
  '</div>' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="inclusion" method="exclude" id="3" options="0"> <i class="fas fa-minus" style="color:red"></i>  {{Exclusion}}</label> ' +
  '</div>' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="inclusion" method="stop" id="4" options="Inclusion"> <i class="fas fa-stop" style="color:green"></i> {{Arrêter Inclusion}}</label> ' +
  '</div>' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="inclusion" method="stop" id="5" options="Exclusion"> <i class="fas fa-stop" style="color:red"></i>  {{Arrêter Exclusion}}</label> ' +
  '</div>';
  dialog_message += '</form>';
  bootbox.dialog({
    title: dialog_title,
    message: dialog_message,
    buttons: {
      "{{Annuler}}": {
        className: "btn-danger",
        callback: function () {
        }
      },
      success: {
        label: "{{Démarrer}}",
        className: "btn-success",
        callback: function () {
          jeedom.zwavejs.controller.include({
            method : $("input[name=inclusion]:checked").attr('method'),
            options : $("input[name=inclusion]:checked").attr('options'),
            error: function (error) {
              $('#div_alert').showAlert({message: error.message, level: 'danger'});
            },
            success: function (data) {
              $('#div_alert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
            }
          });
        }
      },
    }
  });
});

$('body').delegate('.nodeInformations', 'click', function () {
  $('#md_modal').dialog({title: "{{Informations du noeud}}"});
  $('#md_modal').load('index.php?v=d&plugin=zwavejs&modal=node.informations&id=' + $('.eqLogicAttr[data-l1key=logicalId]').value()).dialog('open');
});

$('body').delegate('.nodeValues', 'click', function () {
  $('#md_modal').dialog({title: "{{Valeurs du noeud}}"});
  $('#md_modal').load('index.php?v=d&plugin=zwavejs&modal=node.values&id='+ $('.eqLogicAttr[data-l1key=logicalId]').value()).dialog('open');
});

$('body').delegate('.nodeGroups', 'click', function () {
  $('#md_modal').dialog({title: "{{Groupes du noeud}}"});
  $('#md_modal').load('index.php?v=d&plugin=zwavejs&modal=node.groups&id='+ $('.eqLogicAttr[data-l1key=logicalId]').value()).dialog('open');
});

$('#bt_zwaveNetwork').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Réseaux zwave}}"});
  $('#md_modal').load('index.php?v=d&plugin=zwavejs&modal=network').dialog('open');
});

$('#bt_zwaveStats').off('click').on('click', function () {
  $('#md_modal').dialog({title: "{{Statistiques zwave}}"});
  $('#md_modal').load('index.php?v=d&plugin=zwavejs&modal=stats').dialog('open');
});

$('#bt_zwaveHealth').on('click', function () {
  $('#md_modal').dialog({title: "{{Santé zwave}}"});
  $('#md_modal').load('index.php?v=d&plugin=zwavejs&modal=health').dialog('open');
});

if (is_numeric(getUrlVars('logical_id'))) {
  if ($('.eqLogicDisplayCard[data-logical-id=' + getUrlVars('logical_id') + ']').length != 0) {
    setTimeout(function () {
      $('.eqLogicDisplayCard[data-logical-id=' + getUrlVars('logical_id') + ']').click();
    }, 10);
  }
}

function printEqLogic(_eqLogic) {
  if (_eqLogic.id != '') {
    $('#img_device').attr("src", $('.eqLogicDisplayCard[data-eqLogic_id=' + _eqLogic.id + '] img').attr('src'));
  } else {
    $('#img_device').attr("src", 'plugins/zwavejs/plugin_info/zwavejs_icon.png');
  }
$.ajax({
    type: "POST",
    url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
    data: {
      action: "getEqLogicInfos",
      logicalId: _eqLogic.logicalId,
    },
    dataType: 'json',
    global: false,
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data['result']['interview'] != 'complete'){
		$('.incompleteInfo').show();
	  } else {
		$('.incompleteInfo').hide();  
	  }
	  if (data['result']['confType']){
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=product_name]').prop('title',data['result']['confType']);
	  } else {
		  $('.eqLogicAttr[data-l1key=configuration][data-l2key=product_name]').prop('title','Aucune Commandes/Propriétés Jeedom');
	  }
	  if (data['result']['recommended']){
		$('.confRecommended').show();
	  } else {
		$('.confRecommended').hide();
	  }
	  if (data['result']['modes'] && data['result']['modes'] != 'aucun'){
		$('.confModes').show();
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=confMode]').empty();
		option = '';
		for (var i in data['result']['modes']) {
			if (i == data['result']['actualMode']){
				option += '<option value="' + i + '" selected>' + data['result']['modes'][i] + '</option>';
			} else {
				option += '<option value="' + i + '">' + data['result']['modes'][i] + '</option>';
			}
		}
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=confMode]').append(option);
	  } else {
		$('.eqLogicAttr[data-l1key=configuration][data-l2key=confMode]').empty();
		$('.confModes').hide();
	  }
    modifyWithoutSave = false;
  }
});
}


/**********************Envent js requests *****************************/
$('body').off('zwavejs::inclusion').on('zwavejs::inclusion', function (_event, _options) {
  $.hideAlert();
  $('#div_inclusionAlert').showAlert({
      message: _options.message,
      level: 'warning'
   });
   if (_options.type == 'inclusion'){
		$('#div_inclusionAlert').empty().append('<div class="alert alert-warning" role="alert"> {{Le mode inclusion est actif ...}}</div>');
   } else if (_options.type == 'exclusion'){
		$('#div_inclusionAlert').empty().append('<div class="alert alert-warning" role="alert"> {{Le mode exclusion est actif ...}}</div>');
   } else {
		$('#div_inclusionAlert').empty();
   }
});

$('body').off('zwavejs::recommended').on('zwavejs::recommended', function (_event, _options) {
  $('#div_alert').showAlert({
      message: _options.message,
      level: 'warning'
   });
});

$('body').off('zwavejs::driverStatus').on('zwavejs::driverStatus', function (_event, _options) {
   if (_options.status == '1'){
		$('#div_driverStatus').empty();
   } else if (_options.status == '0'){
		$('#div_driverStatus').empty().append('<div class="alert alert-warning" role="alert"> {{Le driver Zwave n\'est pas initialisé, veuillez patienter. Si le message reste trop longtemps, veuillez vérifier la configuration du démon}}</div>');
   }
});

$('body').off('zwavejs::sync').on('zwavejs::sync', function (_event, _options) {
  $.hideAlert();
  $('#div_inclusionAlert').showAlert({
      message: _options.message,
      level: 'warning'
   });
   if (_options.type == 'finished'){
		window.location.href = 'index.php?v=d&p=zwavejs&m=zwavejs';
   }
});

$('body').off('zwavejs::includeDevice').on('zwavejs::includeDevice', function (_event, _options) {
  if (modifyWithoutSave) {
    $('#div_inclusionAlert').showAlert({
      message: '{{Un périphérique vient d\'être inclu/exclu. Veuillez réactualiser la page}}',
      level: 'warning'
    });
  } else {
    if (_options == '') {
      window.location.reload();
    } else {
      window.location.href = 'index.php?v=d&p=zwavejs&m=zwavejs&id=' + _options;
    }
  }
});

$('#bt_autoDetectModule').off('click').on('click', function () {
  var dialog_title = '{{Recharge configuration}}';
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
  dialog_title = '{{Recharger la configuration}}';
  dialog_message += '<label class="control-label" > {{Sélectionner le mode de rechargement de la configuration ?}} </label> ' +
  '<div> <div class="radio"> <label > ' +
  '<input type="radio" name="command" id="command-0" value="0" checked="checked"> {{Sans recréer les commandes mais en créeant les manquantes}} </label> ' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="command" id="command-1" value="1"> {{En recréant toutes les commandes}}</label> ' +
  '</div><div class="radio"> <label > ' +
  '<input type="radio" name="command" id="command-2" value="2"> {{En supprimant les commandes qui ne sont pas dans la configuration}} </label> ' +
  '</div></div><br>' +
  '<label class="lbl lbl-warning" for="name">{{Attention, "en recréant les commandes" va supprimer les commandes existantes.}}</label> ';
  dialog_message += '</form>';
  bootbox.dialog({
    title: dialog_title,
    message: dialog_message,
    buttons: {
      "{{Annuler}}": {
        className: "btn-danger",
        callback: function () {
        }
      },
      success: {
        label: "{{Démarrer}}",
        className: "btn-success",
        callback: function () {
          if ($("input[name='command']:checked").val() == "1"){
            bootbox.confirm('{{Etes-vous sûr de vouloir récréer toutes les commandes ? Cela va supprimer les commandes existantes}}', function (result) {
              if (result) {
                $.ajax({
                  type: "POST",
                  url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
                  data: {
                    action: "autoDetectModule",
                    id: $('.eqLogicAttr[data-l1key=id]').value(),
                    createcommand: 1,
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
          } else {
            $.ajax({
              type: "POST",
              url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
              data: {
                action: "autoDetectModule",
                id: $('.eqLogicAttr[data-l1key=id]').value(),
                createcommand: $("input[name='command']:checked").val(),
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
        }
      },
    }
  });
  
});

function syncEqLogicWithzwavejs() {
  $.ajax({
    type: "POST",
    url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
    data: {
      action: "syncEqLogicWithzwavejs",
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      window.location.reload();
    }
  });
}

$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
});

function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = {configuration: {}};
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
  tr += '<td>';
  tr += '<a class="cmdAction btn btn-default btn-sm" data-l1key="chooseIcon"><i class="fas fa-flag"></i> {{Icône}}</a>';
  tr += '<span class="cmdAttr" data-l1key="display" data-l2key="icon" style="margin-left:10px;"></span>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="margin-left:10px; margin-bottom:2px; width:75%; float:right">';
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display : none;" title="{{La valeur de la commande vaut par défaut la commande}}">';
  tr += '<option value="">Aucune</option>';
  tr += '</select>';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="id" style="display : none;">';
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
  tr += '</td>';
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="class" ></td>';
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="endpoint" value="0"></td>';
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="property" ></td>';
  tr += '<td>';
  if (init(_cmd.type) == 'action'){
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="value" placeholder="{{Commande}}" >';
  }
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateValue" placeholder="{{Valeur retour d\'état}}" style="width:48%;display:inline-block;">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateTime" placeholder="{{Durée avant retour d\'état (min)}}" style="width:48%;display:inline-block;margin-left:2px;">';
  tr += '</td>';
  tr += '<td>';
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;display:inline-block;">';
  tr += ' <input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;display:inline-block;">';
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;display:inline-block;margin-left:2px;">';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isVisible" checked/>{{Afficher}}</label></span> ';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr checkbox-inline" data-l1key="isHistorized" checked/>{{Historiser}}</label></span> ';
  tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary"/>{{Inverser}}</label></span> ';
  if (init(_cmd.subType) == 'numeric' && init(_cmd.configuration.property) == 'Air temperature'){
    tr += '<span><label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="convertFaren"/>{{Convertir °F-°C}}</label></span> ';
  }
  tr += '</td>';
  tr += '<td style="width:125px">';
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> ';
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>';
  }
  tr += ' <i class="fas fa-minus-circle cmdAction cursor" data-action="remove"></i></td>';
  tr += '</tr>';
  $('#table_cmd tbody').append(tr);
  var tr = $('#table_cmd tbody tr').last();
  jeedom.eqLogic.builSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: {type: 'info'},
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result);
      tr.setValues(_cmd, '.cmdAttr');
      jeedom.cmd.changeType(tr, init(_cmd.subType));
    }
  });
}