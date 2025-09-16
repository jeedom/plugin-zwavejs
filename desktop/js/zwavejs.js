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
* along with Plugin zwavejs for jeedom. If not, see <http://www.gnu.org/licenses>.
*/

var nodes = {}
var networkTree = {}




$('#bt_syncEqLogic').off('click').on('click', function() {
  jeedom.zwavejs.network.getNodes({
    info: 'getNodes',
    mode: 'sync',
    global: false
  })
})

$('#bt_addRefresh').off('click').on('click', function() {
  addRefresh({})
})

$('.confRecommended').on('click', function() {
  bootbox.dialog({
    title: "{{Configuration recommandée}}",
    message: '<form class="form-horizontal"> ' +
      '<label class="control-label"> {{Voulez-vous appliquer le jeu de configuration recommandé par l\'équipe Jeedom}} ?</label> ' +
      '<br><br>' +
      '<ul>' +
      '<li class="active">{{Paramètres}}</li>' +
      '<li class="active">{{Associations}}</li>' +
      '<li class="active">{{Intervalle de réveil}}</li>' +
      '</ul>' +
      '</form>',
    buttons: {
      main: {
        label: "{{Annuler}}",
        className: "btn-danger",
        callback: function() {
        }
      },
      success: {
        label: "{{Appliquer}}",
        className: "btn-success",
        callback: function() {
          $.ajax({
            type: "POST",
            url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
            data: {
              action: "applyRecommended",
              nodeId: $('.eqLogicAttr[data-l1key=logicalId]').value(),
            },
            global: false,
            dataType: 'json',
            error: function(error) {
              $.fn.showAlert({ message: error.message, level: 'danger' })
            },
            success: function(data) {
              if (data.state != 'ok') {
                $.fn.showAlert({ message: data.result, level: 'danger' })
                return
              }
              if (data.result == "wakeup") {
                $.fn.showAlert({
                  message: '{{Configuration appliquée. Cependant ce module nécessite un réveil pour que celle-ci soit effective.}}',
                  level: 'success'
                })
              } else {
                $.fn.showAlert({
                  message: '{{Configuration appliquée et effective.}}',
                  level: 'success'
                })
              }
            }
          })
        }
      }
    }
  }
  )
})

$('.changeIncludeState').off('click').on('click', function() {
  var dialog_title = '<i class="fas fa-sign-in-alt fa-rotate-90"></i> {{Inclusion/Exclusion}}'
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> '
  dialog_message += '<label class="control-label"> {{Sélectionnez le mode}} </label> ' +
    '<div> <div class="radio"><label> ' +
    '<input type="radio" name="inclusion" method="include" id="0" options="0" checked="checked"> <i class="fas fa-plus" style="color:green"></i> {{Inclusion par défaut (utilisera le meilleur mode pour le module)}} </label> ' +
    '</div><div class="radio"><label> ' +
    '<input type="radio" name="inclusion" method="include" id="1" options="3"> <i class="fas fa-lock" style="color:orange"></i> {{Inclusion sécurisée forcée S0}}</label> ' +
    '</div> ' +
    '</div><div class="radio"> <label> ' +
    '<input type="radio" name="inclusion" method="include" id="2" options="2"> <i class="fas fa-unlock" style="color:blue"></i> {{Inclusion non sécurisée}}</label> ' +
    '</div>' +
    '</div><div class="radio"><label> ' +
    '<input type="radio" name="inclusion" method="exclude" id="3" options="0"> <i class="fas fa-minus" style="color:red"></i>  {{Exclusion}}</label> ' +
    '</div>' +
    '</div><div class="radio"><label> ' +
    '<input type="radio" name="inclusion" method="stop" id="4" options="Inclusion"> <i class="fas fa-stop" style="color:green"></i> {{Arrêter Inclusion}}</label> ' +
    '</div>' +
    '</div><div class="radio"><label> ' +
    '<input type="radio" name="inclusion" method="stop" id="5" options="Exclusion"> <i class="fas fa-stop" style="color:red"></i> {{Arrêter Exclusion}}</label> ' +
    '</div>'
  dialog_message += '</form>'
  bootbox.dialog({
    title: dialog_title,
    message: dialog_message,
    buttons: {
      "{{Annuler}}": {
        className: "btn-danger",
        callback: function() {
        }
      },
      success: {
        label: "{{Démarrer}}",
        className: "btn-success",
        callback: function() {
          jeedom.zwavejs.controller.include({
            method: $("input[name=inclusion]:checked").attr('method'),
            options: $("input[name=inclusion]:checked").attr('options'),
            error: function(error) {
              $.fn.showAlert({ message: error.message, level: 'danger' })
            },
            success: function(data) {
              $.fn.showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
            }
          })
        }
      },
    }
  })
})

$('.eqLogic').on('click', '.nodeInformations', function() {
  $('#md_modal2').dialog({ title: "{{Informations du nœud}}" })
  $('#md_modal2').load('index.php?v=d&plugin=zwavejs&modal=node.informations&id=' + $('.eqLogicAttr[data-l1key=logicalId]').value()).dialog('open')
})

$('.eqLogic').on('click', '.nodeValues', function() {
  $('#md_modal2').dialog({ title: "{{Valeurs du nœud}}" })
  $('#md_modal2').load('index.php?v=d&plugin=zwavejs&modal=node.values&id=' + $('.eqLogicAttr[data-l1key=logicalId]').value()).dialog('open')
})

$('.eqLogic').on('click', '.nodeGroups', function() {
  $('#md_modal2').dialog({ title: "{{Groupes du nœud}}" })
  $('#md_modal2').load('index.php?v=d&plugin=zwavejs&modal=node.groups&id=' + $('.eqLogicAttr[data-l1key=logicalId]').value()).dialog('open')
})

$('#bt_zwaveNetwork').off('click').on('click', function() {
  $('#md_modal2').dialog({ title: "{{Réseau Z-Wave}}" })
  $('#md_modal2').load('index.php?v=d&plugin=zwavejs&modal=network').dialog('open')
})

$('#bt_zwaveStats').off('click').on('click', function() {
  $('#md_modal2').dialog({ title: "{{Statistiques Z-Wave}}" })
  $('#md_modal2').load('index.php?v=d&plugin=zwavejs&modal=stats').dialog('open')
})

$('#bt_zwaveHealth').on('click', function() {
  $('#md_modal2').dialog({ title: "{{Santé Z-Wave}}" })
  $('#md_modal2').load('index.php?v=d&plugin=zwavejs&modal=health').dialog('open')
})

$('#bt_zwaveWaiting').on('click', function() {
  $('#md_modal3').dialog({ title: "{{Paramètres en attente}}" })
  $('#md_modal3').load('index.php?v=d&plugin=zwavejs&modal=waiting').dialog('open')
})

if (is_numeric(getUrlVars('logical_id'))) {
  if ($('.eqLogicDisplayCard[data-logical-id=' + getUrlVars('logical_id') + ']').length != 0) {
    setTimeout(function() {
      $('.eqLogicDisplayCard[data-logical-id=' + getUrlVars('logical_id') + ']').click()
    }, 10)
  }
}

function printEqLogic(_eqLogic) {
  if (_eqLogic.id != '') {
    $('#img_device').attr("src", $('.eqLogicDisplayCard[data-eqLogic_id=' + _eqLogic.id + '] img').attr('src'))
  } else {
    $('#img_device').attr("src", 'plugins/zwavejs/plugin_info/zwavejs_icon.png')
  }
  $('#table_zwaveRefresh').find('tbody').empty()
  if (isset(_eqLogic.configuration)) {
    if (isset(_eqLogic.configuration.refreshes)) {
      for (var i in _eqLogic.configuration.refreshes) {
        addRefresh(_eqLogic.configuration.refreshes[i])
      }
    }
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
    error: function(error) {
      $.fn.showAlert({ message: error.message, level: 'danger' })
    },
    success: function(data) {
      if (data['result']['interview'] != 'complete') {
        $('.incompleteInfo').show()
      } else {
        $('.incompleteInfo').hide()
      }
      if ('assistant' in data['result']) {
        $('.assistant').show()
        $('.assistantText').empty().append(data['result']['assistant'])
      } else {
        $('.assistantText').empty()
        $('.assistant').hide()
      }
      if (data['result']['confType']) {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=product_name]').prop('title', data['result']['confType'])
      } else {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=product_name]').prop('title', '{{Aucune Commande/Propriété Jeedom}}')
      }
      if (data['result']['recommended']) {
        $('.confRecommended').show()
      } else {
        $('.confRecommended').hide()
      }
      if (data['result']['modes'] && data['result']['modes'] != 'aucun') {
        $('.confModes').show()
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=confMode]').empty()
        option = ''
        for (var i in data['result']['modes']) {
          if (i == data['result']['actualMode']) {
            option += '<option value="' + i + '" selected>' + data['result']['modes'][i] + '</option>'
          } else {
            option += '<option value="' + i + '">' + data['result']['modes'][i] + '</option>'
          }
        }
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=confMode]').append(option)
      } else {
        $('.eqLogicAttr[data-l1key=configuration][data-l2key=confMode]').empty()
        $('.confModes').hide()
      }
      if (data['result']['command_counter']) {
        $('.command_number').empty().append(data['result']['command_counter'])
        if (data['result']['command_counter'] == '0' && data['result']['interview'] == 'complete') {
            $('.nocommand').show()
        } else {
            $('.nocommand').hide()
        }
      }
      modifyWithoutSave = false
    }
  })
}


/**********************Envent js requests *****************************/
$('body').off('zwavejs::inclusion').on('zwavejs::inclusion', function(_event, _options) {
  $.hideAlert()
  $('#div_inclusionAlert').showAlert({
    message: _options.message,
    level: 'warning'
  })
  if (_options.type == 'inclusion') {
    $('#div_inclusionAlert').empty().append('<div class="alert alert-warning" role="alert"> {{Le mode inclusion est actif}}...</div>')
  } else if (_options.type == 'exclusion') {
    $('#div_inclusionAlert').empty().append('<div class="alert alert-warning" role="alert"> {{Le mode exclusion est actif}}...</div>')
  } else {
    $('#div_inclusionAlert').empty()
  }
})

$('body').off('zwavejs::grant_security_classes').on('zwavejs::grant_security_classes', function(_event, _options) {
  var dialog_title = '<i class="fas fa-user-lock"></i> {{Inclusion S2 - Classes de Sécurité}}'
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> '
  var classes = _options['classes']
  var auth = _options['auth']
  dialog_message+= '<label class="control-label"> {{Classes de sécurité :}} </label><br>' 
  if (classes.includes(1)){
    dialog_message += '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="1" checked>{{S2 Authenticated}}</label></br>';
  } else {
    dialog_message += '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="1" disabled>{{S2 Authenticated}}</label></br>';
  }
  if (classes.includes(2)){
    dialog_message += '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="2" checked>{{S2 Access Control}}</label></br>';
  } else {
    dialog_message += '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="2" disabled>{{S2 Access Control}}</label></br>';
  }
  if (classes.includes(0)){
    dialog_message += '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="0" checked>{{S2 Unauthenticated}}</label></br>'
  } else {
    dialog_message += '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="0" disabled>{{S2 Unauthenticated}}</label></br>'
  }
  if (classes.includes(7)){
    dialog_message += '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="7" checked>{{S0 Legacy}}</label></br>';
  } else {
    dialog_message += '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="7" disabled>{{S0 Legacy}}</label></br>';
  }
  if (auth){
    dialog_message += '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="clientauth" checked>{{Client Authentification}}</label></br>';
  } else {
    dialog_message += '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="clientauth" disabled>{{Client Authentification}}</label></br>';
  }
  dialog_message+="</br><div class='alert alert-info'>{{Vous ne pouvez pas activer une classe de sécurité non supporté. Dans la majorité des cas, ne modifiez rien. Si vous annulez l'inclusion S2, le module s'incluera en non sécurisé.}}</div>"
  dialog_message += '</form>'
  bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      closeButton: false,
      buttons: {
      "<i class='fas fa-times-circle'></i> {{Annuler Inclusion S2}}": {
        className: "btn-danger",
        callback: function() {
            jeedom.zwavejs.controller.abortInclusion({
              error: function(error) {
                $.fn.showAlert({ message: error.message, level: 'danger' })
              },
              success: function(data) {
                $.fn.showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
              }
            })
        }
      },
      success: {
        label: "<i class='fas fa-step-forward'></i> {{Continuer}}",
        className: "btn-success",
        callback: function() {
          var list = [];
          var auth = false;
          if ($('.clientauth').is(':checked')){
            auth = true;
          }
          $('.classes').each(function () {
            if (this.checked) {
                list.push($(this).data('key'));
            }
          });
          jeedom.zwavejs.controller.grantSecurity({
           security: list,
           auth: auth,
           error: function(error) {
             $.fn.showAlert({ message: error.message, level: 'danger' })
           },
           success: function(data) {
             $.fn.showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
           }
         })
        }
      },
    }
    })
})

$('body').off('zwavejs::validate_dsk').on('zwavejs::validate_dsk', function(_event, _options) {
 var dialog_title = '<i class="fas fa-fingerprint"></i> {{Inclusion S2 - Validation DSK}}'
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> '
  var dsk = _options
  dialog_message+= '<label class="control-label"> {{Clé de sécurité DSK :}} </label><br>'
  dialog_message+= '<input class="col-sm-3 dskinput form-control" type="text">'
  dialog_message+= '<label class="col-sm-9">'+dsk+'</label>';
  dialog_message+="</br></br><div class='col-sm-12 alert alert-info'>{{Veuillez vérifier la clé de sécurité et compléter celle-ci. L'inclusion prendra quelques secondes à se terminer après cette étape. Une mauvaise clé est synonyme d'inclusion non sécurisée.}}</div>"
  dialog_message += '</form>'
  bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      closeButton: false,
      buttons: {
      "<i class='fas fa-times-circle'></i> {{Annuler Inclusion S2}}": {
        className: "btn-danger",
        callback: function() {
            jeedom.zwavejs.controller.abortInclusion({
              error: function(error) {
                $.fn.showAlert({ message: error.message, level: 'danger' })
              },
              success: function(data) {
                $.fn.showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
              }
            })
        }
      },
      success: {
        label: "<i class='fas fa-step-forward'></i> {{Continuer}}",
        className: "btn-success",
        callback: function() {
           var dskinput = $('.dskinput').value();
           jeedom.zwavejs.controller.validateDSK({
           dsk: dskinput,
           error: function(error) {
             $.fn.showAlert({ message: error.message, level: 'danger' })
           },
           success: function(data) {
             $.fn.showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
           }
         })
        }
      },
    }
    })
})

$('body').off('zwavejs::recommended').on('zwavejs::recommended', function(_event, _options) {
  $.fn.showAlert({
    message: _options.message,
    level: 'warning'
  })
})

$('body').off('zwavejs::driverStatus').on('zwavejs::driverStatus', function(_event, _options) {
  if (_options.status == '1') {
    $('#div_driverStatus').empty()
  } else if (_options.status == '0') {
    $('#div_driverStatus').empty().append('<div class="alert alert-warning" role="alert"> {{Le driver Z-Wave n\'est pas initialisé, veuillez patienter. Si le message reste trop longtemps, veuillez vérifier la configuration du démon.}}</div>')
  }
})

$('body').off('zwavejs::sync').on('zwavejs::sync', function(_event, _options) {
  $.hideAlert()
  $('#div_inclusionAlert').showAlert({
    message: _options.message,
    level: 'warning'
  })
  if (_options.type == 'finished') {
    window.location.href = 'index.php?v=d&p=zwavejs&m=zwavejs'
  }
})

$('body').off('zwavejs::includeDevice').on('zwavejs::includeDevice', function(_event, _options) {
  if (modifyWithoutSave) {
    $('#div_inclusionAlert').showAlert({
      message: '{{Un périphérique vient d\'être inclu/exclu. Veuillez réactualiser la page}}',
      level: 'warning'
    })
  } else {
    if (_options == '') {
      window.location.reload()
    } else {
      window.location.href = 'index.php?v=d&p=zwavejs&m=zwavejs&id=' + _options
    }
  }
})

$('#bt_autoDetectModule').off('click').on('click', function() {
  var dialog_title = '{{Recharger la configuration}}'
  var dialog_message = '<form class="form-horizontal onsubmit="return false;"> '
  dialog_message += '<label class="control-label"> {{Sélectionner le mode de rechargement de la configuration}}</label> ' +
    '<div> <div class="radio"><label> ' +
    '<input type="radio" name="command" id="command-0" value="0" checked="checked"> {{Sans recréer les commandes mais en créant les manquantes}} </label> ' +
    '</div><div class="radio"><label> ' +
    '<input type="radio" name="command" id="command-1" value="1"> {{En recréant toutes les commandes}}</label> ' +
    '</div><div class="radio"><label> ' +
    '<input type="radio" name="command" id="command-2" value="2"> {{En supprimant les commandes qui ne sont pas dans la configuration}} </label> ' +
    '</div></div><br>' +
    '<label class="label label-warning" for="name">{{Attention : "En recréant les commandes" va supprimer les commandes existantes.}}</label> '
  dialog_message += '</form>'
  bootbox.dialog({
    title: dialog_title,
    message: dialog_message,
    buttons: {
      "{{Annuler}}": {
        className: "btn-danger",
        callback: function() {
        }
      },
      success: {
        label: "{{Démarrer}}",
        className: "btn-success",
        callback: function() {
          if ($("input[name='command']:checked").val() == "1") {
            bootbox.confirm('{{Etes-vous sûr de vouloir récréer toutes les commandes ? Cela va supprimer les commandes existantes}}', function(result) {
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
                  error: function(error) {
                    $.fn.showAlert({ message: error.message, level: 'danger' })
                  },
                  success: function(data) {
                    if (data.state != 'ok') {
                      $.fn.showAlert({ message: data.result, level: 'danger' })
                      return
                    }
                    $.fn.showAlert({ message: '{{Opération réalisée avec succès}}', level: 'success' })
                    $('.eqLogicDisplayCard[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click()
                  }
                })
              }
            })
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
              error: function(error) {
                $.fn.showAlert({ message: error.message, level: 'danger' })
              },
              success: function(data) {
                if (data.state != 'ok') {
                  $.fn.showAlert({ message: data.result, level: 'danger' })
                  return
                }
                $.fn.showAlert({ message: '{{Opération réalisée avec succès}}', level: 'success' })
                $('.eqLogicDisplayCard[data-eqLogic_id=' + $('.eqLogicAttr[data-l1key=id]').value() + ']').click()
              }
            })
          }
        }
      },
    }
  })

})

function syncEqLogicWithzwavejs() {
  $.ajax({
    type: "POST",
    url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
    data: {
      action: "syncEqLogicWithzwavejs",
    },
    dataType: 'json',
    error: function(error) {
      $.fn.showAlert({ message: error.message, level: 'danger' })
    },
    success: function(data) {
      if (data.state != 'ok') {
        $.fn.showAlert({ message: data.result, level: 'danger' })
        return
      }
      window.location.reload()
    }
  })
}

$("#table_cmd").sortable({
  axis: "y",
  cursor: "move",
  items: ".cmd",
  placeholder: "ui-state-highlight",
  tolerance: "intersect",
  forcePlaceholderSize: true
})

function addCmdToTable(_cmd) {
  if (!isset(_cmd)) {
    var _cmd = { configuration: {} }
  }
  var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">'
  tr += '<td class="hidden-xs">'
  tr += '<span class="cmdAttr" data-l1key="id"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<input class="cmdAttr form-control input-sm roundedLeft" data-l1key="name" placeholder="{{Nom de la commande}}">'
  tr += '<span class="input-group-btn">'
  tr += '<a class="cmdAction btn btn-sm btn-default" data-l1key="chooseIcon" title="{{Choisir une icône}}"><i class="fas fa-icons"></i></a>'
  tr += '</span>'
  tr += '<span class="cmdAttr input-group-addon roundedRight" data-l1key="display" data-l2key="icon" style="font-size:19px;padding:0 5px 0 0!important;"></span>'
  tr += '</div>'
  tr += '<select class="cmdAttr form-control input-sm" data-l1key="value" style="display:none;margin-top:5px;" title="{{Commande information liée}}">'
  tr += '<option value="">{{Aucune}}</option>'
  tr += '</select>'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>'
  tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>'
  tr += '</td>'
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="class"></td>'
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="endpoint" value="0"></td>'
  tr += '<td><input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="property"></td>'
  tr += '<td>'
  if (init(_cmd.type) == 'action') {
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="value" placeholder="{{Commande}}">'
  }
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateValue" placeholder="{{Valeur retour d\'état}}" style="margin-bottom:5px;">'
  tr += '<input class="cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="returnStateTime" placeholder="{{Durée avant retour d\'état (min)}}">'
  tr += '</td>'
  tr += '<td>'
  tr += '<span class="cmdAttr" data-l1key="htmlstate"></span>'
  tr += '</td>'
  tr += '<td>'
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isVisible" checked>{{Afficher}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="isHistorized" checked>{{Historiser}}</label> '
  tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="display" data-l2key="invertBinary">{{Inverser}}</label> '
  if (init(_cmd.subType) == 'numeric' && init(_cmd.configuration.property) == 'Air temperature') {
    tr += '<label class="checkbox-inline"><input type="checkbox" class="cmdAttr" data-l1key="configuration" data-l2key="convertFaren">{{Convertir °F-°C}}</label> '
  }
  tr += '<div style="margin-top:7px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="minValue" placeholder="{{Min}}" title="{{Min}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="maxValue" placeholder="{{Max}}" title="{{Max}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="unite" placeholder="Unité" title="{{Unité}}" style="width:30%;max-width:80px;display:inline-block;margin-right:2px;">'
  tr += '<input class="tooltips cmdAttr form-control input-sm" data-l1key="configuration" data-l2key="listValue" placeholder="{{Liste : valeur|texte (séparées par un point-virgule)}}" title="{{Liste : valeur|texte}}">'
  tr += '</div>'
  tr += '</td>'
  tr += '<td>'
  if (is_numeric(_cmd.id)) {
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fas fa-cogs"></i></a> '
    tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fas fa-rss"></i> {{Tester}}</a>'
  }
  tr += ' <i class="fas fa-minus-circle cmdAction cursor" data-action="remove"></i></td>'
  tr += '</tr>'
  $('#table_cmd tbody').append(tr)
  var tr = $('#table_cmd tbody tr').last()
  jeedom.eqLogic.buildSelectCmd({
    id: $('.eqLogicAttr[data-l1key=id]').value(),
    filter: { type: 'info' },
    error: function(error) {
      $.fn.showAlert({ message: error.message, level: 'danger' })
    },
    success: function(result) {
      tr.find('.cmdAttr[data-l1key=value]').append(result)
      tr.setValues(_cmd, '.cmdAttr')
      jeedom.cmd.changeType(tr, init(_cmd.subType))
    }
  })
}

function saveEqLogic(_eqLogic) {
  if (!isset(_eqLogic.configuration)) {
    _eqLogic.configuration = {}
  }
  _eqLogic.configuration.refreshes = $('#table_zwaveRefresh').find('tbody tr').getValues('.refreshAttr')
  return _eqLogic
}

$('#table_zwaveRefresh').off('click','.bt_removeRefresh').on('click','.bt_removeRefresh', function() {
  $(this).closest('tr').remove()
})

function addRefresh(_refresh) {
  var tr = '<tr class="refresh">'
  tr += '<td>'
  tr += '<div class="input-group"><input type="text" class="form-control refreshAttr" data-l1key="refresh::source"></div>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group"><input type="text" class="form-control refreshAttr" data-l1key="refresh::target"></div>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group"><input type="number" class="form-control refreshAttr roundedLeft" data-l1key="refresh::sleep"><span class="input-group-addon roundedRight">s</span></div>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group"><input type="number" class="form-control refreshAttr" data-l1key="refresh::number"></div>'
  tr += '</td>'
  tr += '<td>'
  tr += '<div class="input-group">'
  tr += '<textarea class="refreshAttr form-control roundedLeft" data-concat="1" data-l1key="refresh::comment" style="height:72px;"></textarea>'
  tr += '<span class="input-group-addon roundedRight cursor bt_removeRefresh" title="{{Supprimer la règle}}"><a class="btn btn-default"><i class="fas fa-minus-circle"></i></a></span>'
  tr += '</div></td>'
  tr += '</tr>'
  $('#table_zwaveRefresh').find('tbody').append(tr)
  $('#table_zwaveRefresh').find('tbody tr').last().setValues(_refresh, '.refreshAttr')
}
