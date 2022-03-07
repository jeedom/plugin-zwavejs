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

$('#table_healthNetwork').off().delegate('.pingDevice', 'click', function () {
  jeedom.zwavejs.node.action({
    action : 'pingNode',
    nodeId: $(this).attr('data-id'),
    error: function (error) {
      $('#div_networkHealthAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function (data) {
      $('#div_networkHealthAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});

function get_health_info(){
  jeedom.zwavejs.network.getNodes({
    info : 'getHealth',
    mode : 'health',
    global:false,
    error: function (error) {
      $('#div_networkHealthAlert').showAlert({message: error.message, level: 'danger'});
	  if ($('.modalHealthValues').is(":visible")) {
		setTimeout(function(){ get_health_info(); }, 2000);
	  }
    },
    success: function (data) {
      if ($('.modalHealthValues').is(":visible")) {
		setTimeout(function(){ get_health_info(); }, 2000);
	  }
    }
  });
}

$('body').off('zwavejs::getHealthPage').on('zwavejs::getHealthPage', function (_event, _options) {
	$('#div_networkHealthAlert').hideAlert();
	$('.tableHealth tbody').empty().append(_options);
});

$('#div_networkHealthAlert').showAlert({message: '{{Chargement des infos en cours ...}}', level: 'warning'});
get_health_info();
