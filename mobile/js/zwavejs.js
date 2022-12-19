"use strict"

$('body').attr('data-page', 'zwavejs')

$('#searchContainer').hide()

var $bottompanel_actionInclude
var $bottompanel_actionNetwork

function initZwavejsZwavejs() {
  $bottompanel_actionInclude = $('#bottompanel_actionInclude')
  $bottompanel_actionInclude.empty()
  $bottompanel_actionInclude.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button incluAct" data-method="include" data-options="0"><i class="fas fa-plus" style="color:green"></i> {{Inclusion par défaut}}</a>')
  $bottompanel_actionInclude.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button incluAct" data-method="include" data-options="3"><i class="fas fa-lock" style="color:orange"></i> {{Inclusion sécurisée forcée S0}}</a>')
  $bottompanel_actionInclude.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button incluAct" data-method="include" data-options="2"><i class="fas fa-qrcode" style="color:blue"></i> {{Inclusion non sécurisée}}</a>')
  $bottompanel_actionInclude.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button incluAct" data-method="exclude" data-options="0"><i class="fas fa-minus" style="color:red"></i> {{Exclusion}}</a>')
  $bottompanel_actionInclude.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button incluAct" data-method="stop" data-options="Inclusion"><i class="fas fa-stop" style="color:green"></i> {{Arrêter Inclusion}}</a>')
  $bottompanel_actionInclude.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button incluAct" data-method="stop" data-options="Exclusion"><i class="fas fa-stop" style="color:red"></i> {{Arrêter Exclusion}}</a>')
  $bottompanel_actionInclude.append('<br>')

  $bottompanel_actionNetwork = $('#bottompanel_actionNetwork')
  $bottompanel_actionNetwork.empty()
  $bottompanel_actionNetwork.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button controller_action" data-action="refreshNeighbors"><i class="fas fa-project-diagram"></i> {{Demander les voisins de tout le réseau}}</a>')
  $bottompanel_actionNetwork.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button controller_action" data-action="beginHealingNetwork"><i class="fas fa-first-aid"></i> {{Lancer un soin du réseau}}</a>')
  $bottompanel_actionNetwork.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button controller_action" data-action="stopHealingNetwork"><i class="fas fa-stop"></i> {{Arrêter un soin du réseau}}</a>')
  $bottompanel_actionNetwork.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button namingAction"><i class="fas fa-bookmark"></i> {{Envoyer les noms d\'équipements}}</a>')
  $bottompanel_actionNetwork.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button controller_action" data-action="softReset"><i class="fas fa-times"></i> {{Soft Reset}}</a>')
  $bottompanel_actionNetwork.append('<a class="ui-bottom-sheet-link ui-btn ui-btn-inline waves-effect waves-button zwaveSync"><i class="fas fa-sync"></i> {{Synchroniser}}</a>')
  $bottompanel_actionNetwork.append('<br>')
}

$('#bottompanel_actionInclude').off("click", ".incluAct").on('click', '.incluAct', function() {
  jeedom.zwavejs.controller.include({
    method: $(this).attr('data-method'),
    options: $(this).attr('data-options'),
    error: function(error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function(data) {
      $('#div_alert').showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
      $('#bottompanel_actionInclude').hide()
    }
  })
})

$("#bottompanel_actionNetwork").off("click", ".controller_action").on("click", ".controller_action", function(e) {
  jeedom.zwavejs.controller.action({
    action: $(this).data('action'),
    error: function(error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function() {
      $('#div_alert').showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
      $('#bottompanel_actionNetwork').hide()
    }
  })
})

$("#bottompanel_actionNetwork").off("click", ".namingAction").on("click", ".namingAction", function(e) {
  jeedom.zwavejs.controller.namingAction({
    action: 'namingAction',
    nodeId: 'all',
    error: function(error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function() {
      $('#div_alert').showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
      $('#bottompanel_actionNetwork').hide()
    }
  })
})

$("#bottompanel_actionNetwork").off("click", ".zwaveSync").on("click", ".zwaveSync", function(e) {
  jeedom.zwavejs.network.getNodes({
    info: 'getNodes',
    mode: 'sync',
    global: false,
    error: function(error) {
      $('#div_alert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function() {
      $('#div_alert').showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
      $('#bottompanel_actionNetwork').hide()
    }
  })
})

$('body').off('zwavejs::inclusion').on('zwavejs::inclusion', function(_event, _options) {
  $('#div_alert').showAlert({
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

$('body').off('zwavejs::includeDevice').on('zwavejs::includeDevice', function(_event, _options) {
  $('#div_alert').showAlert({
    message: '{{Un périphérique vient d\'être inclu/exclu.}}',
    level: 'warning'
  })
})

$('body').off('zwavejs::sync').on('zwavejs::sync', function(_event, _options) {
  $('#div_alert').showAlert({
    message: _options.message,
    level: 'warning'
  })
})
