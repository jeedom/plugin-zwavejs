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

function network_load_nodes(){
  jeedom.zwavejs.network.getNodes({
    info : 'getNodes',
    mode : 'stats',
    global:false,
    error: function (error) {
      $('#div_networkStatAlert').showAlert({message: error.message, level: 'danger'});
	  if ($('.modalStatsValues').is(":visible")) {
		getNodes = setTimeout(function(){ network_load_nodes(); }, 2000);
	  }
    },
      success: function () {
        if ($('.modalStatsValues').is(":visible")) {
		getNodes = setTimeout(function(){ network_load_nodes(); }, 2000);
	  }
      }
  })
}

function network_read_stats(){
  jeedom.zwavejs.file.get({
    node : '',
    type : 'nodeStats',
    global:false,
    error: function (error) {
    $('#div_networkStatAlert').showAlert({message: error.message, level: 'danger'});
		if ($('.modalStatsValues').is(":visible")) {
		getstats = setTimeout(function(){ network_read_stats(); }, 2000);
	  }
    },
    success: function (nodeStats) {
		if (typeof(nodeStats.networkTree)!= "undefined"){
		for (key in nodeStats.networkTree.data){
			value=nodeStats.networkTree.data[key]
			if (typeof(value.statistics)!= "undefined"){
				stats = value.statistics
				if (typeof(stats.messagesRX) != "undefined"){
					$('.rx'+key).empty().append(stats.messagesRX);
				}
				if (typeof(stats.commandsRX) != "undefined"){
					$('.rx'+key).empty().append(stats.commandsRX);
				}
				if (typeof(stats.messagesTX) != "undefined"){
					$('.tx'+key).empty().append(stats.messagesTX);
				}
				if (typeof(stats.commandsTX) != "undefined"){
					$('.tx'+key).empty().append(stats.commandsTX);
				}
				if (typeof(stats.timeoutResponse) != "undefined"){
					$('.timeout'+key).empty().append(stats.timeoutResponse);
				}
			}
		}
		}
		$('#table_Stat').tablesorter({
			theme: 'bootstrap',
			widgets: ['zebra', 'filter', 'uitheme', 'scroller']
		});
		$('#table_Stat').trigger('update');
		if ($('.modalStatsValues').is(":visible")) {
		getstats = setTimeout(function(){ network_read_stats(); }, 2000);
	  }
	}
  })
}

$('#md_modal').bind('dialogclose', function(event, ui) {
  clearTimeout(getstats);
  clearTimeout(getNodes);
});

$('#div_alert').showAlert({message: '{{Chargement des infos en cours ...}}', level: 'warning'});
network_load_nodes();
network_read_stats();
