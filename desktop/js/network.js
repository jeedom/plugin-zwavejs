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

$("#tab_graph").off("click").on("click", function () {
  network_load_data();
});

$("#tab_route").off("click").on("click", function () {
  network_load_dataTable();
});

function network_load_dataTable(){
    devicesRouting = networkTree.data
    var skipPortableAndVirtual = true;
    var routingTable = '';
    var routingTableHeader = '';
    const queryStageNeighbors = 13;
    $.each(devicesRouting, function (nodeId, node) {
      if (nodeId == 255) {
        return;
      }
      if (skipPortableAndVirtual && node.deviceClass.basic == 1) {
        return;
      }
      var routesCount = getRoutesCount(nodeId);
      
      var link = 'index.php?v=d&p=zwavejs&m=zwavejs&logical_id=' + nodeId;
      routingTableHeader += '<th title="' + node.productDescription + '">' + nodeId + '</th>';
      if (isset(node.eqName)) {
        var name = '<span class="nodeConfiguration cursor" data-node-id="' + nodeId + '"><img src="'+ node.img + '" height="40"/> <a href="'+link+'">' + node.eqName +  '</a></span>';
      } else {
        var name = '<span class="nodeConfiguration cursor" data-node-id="' + nodeId + '"><span class="label label-primary">' + node.productLabel + '</span> ' + node.productDescription + '</span>';
      }
      routingTable += '<tr><td style="min-width: 300px">' + name;
      if (node.failed) {
        routingTable += '  <i class="fas fa-exclamation-triangle fa-lg" style="color:red; text-align:right"  title="{{Présumé mort}}"></i>';
      }
      routingTable += '</td><td style="width: 35px">' + nodeId + '</td>';
      $.each(devicesRouting, function (nnodeId, nnode) {
        if (nnodeId == 255)
        return;
        if (skipPortableAndVirtual && nnode.deviceClass.basic == 1)
        return;
        var rtClass;
        if (!routesCount[nnodeId])
        routesCount[nnodeId] = new Array();
        var routeHops = (routesCount[nnodeId][0] || '0') + "/";
        routeHops += (routesCount[nnodeId][1] || '0') + "/";
        routeHops += (routesCount[nnodeId][2] || '0');
        if (nodeId == nnodeId || node.deviceClass.basic == 1 || nnode.deviceClass.basic == 1) {
          rtClass = 'node-na-color';
          routeHops = '';
        } else if (nnode.interviewStage != 'Complete' || node.interviewStage != 'Complete') {
          rtClass = 'node-interview-not-completed-color';
        } else if ($.inArray(parseInt(nnodeId, 10), node.neighbors) != -1){
          rtClass = 'node-direct-link-color';
        }else if (routesCount[nnodeId] && routesCount[nnodeId][1] > 1){
          rtClass = 'node-remote-control-color';
        }else if (routesCount[nnodeId] && routesCount[nnodeId][1] == 1){
          rtClass = 'node-more-of-one-up-color';
        }else{
          rtClass = 'node-more-of-two-up-color';
        }
        routingTable += '<td class=' + rtClass + ' style="width: 35px"><i class="fas fa-square fa-2x" title="' + routeHops + '"></i></td>';
      });
      routingTable += '</td></tr>';
    });
    $('#div_routingTable').html('<table class="table table-bordered table-condensed"><thead><tr><th>{{Nom}}</th><th>ID</th>' + routingTableHeader + '<th></tr></thead><tbody>' + routingTable + '</tbody></table>');
}

function network_load_data(){
  $('#graph_network svg').remove();
  var graph = Viva.Graph.graph();
  var controllerId = parseInt(networkTree.controllerId);
  nodes = networkTree.data
  for (z in nodes) {
    if (nodes[z].eqName != '') {
      graph.addNode(z, {
          'eqname': nodes[z].eqName,
          'name': nodes[z].name,
          'status': nodes[z].status,
          'basic': nodes[z].deviceClass.basic,
          'neighbours': nodes[z].neighbors,
          'interview': nodes[z].interviewStage,
          'img': nodes[z].img
        });
    } else {
        graph.addNode(z, {
          'eqname': '<span class="label label-primary">' + nodes[z].productDescription + '</span> ',
          'name': nodes[z].productDescription,
          'neighbours': nodes[z].neighbors,
          'status': nodes[z].status,
          'basic': nodes[z].deviceClass.basic,
          'interview': nodes[z].interviewStage,
          'img': nodes[z].img,
        });
    }
    if (nodes[z].neighbors.length < 1) {
      if (typeof nodes[controllerId] != 'undefined') {
        graph.addLink(z, controllerId, {isdash: 1, lengthfactor: 0.6});
      }
    } else {
		if (nodes[z].neighbors.includes(controllerId)){
			if (typeof nodes[controllerId] != 'undefined') {
				graph.addLink(z, controllerId, {isdash: 0, lengthfactor: 0});
			}
		} else {
			for (neighbour in nodes[z].neighbors) {
				neighbourid = nodes[z].neighbors[neighbour];
				if (typeof nodes[neighbourid] != 'undefined') {
					graph.addLink(z, neighbourid, {isdash: 0, lengthfactor: 0});
				}
			}
		}
	}
  }
  var graphics = Viva.Graph.View.svgGraphics(),
  nodeSize = 24,
  highlightRelatedNodes = function (nodeId, isOn) {
    graph.forEachLinkedNode(nodeId, function (node, link) {
      var linkUI = graphics.getLinkUI(link.id);
      if (linkUI) {
        linkUI.attr('stroke', isOn ? '#FF0000' : '#B7B7B7');
      }
    });
  };
  graphics.node(function (node) {
    if (typeof node.data == 'undefined') {
      graph.removeNode(node.id);
    }
    nodecolor = '#979797';
    var nodesize = 10;
    const nodeshape = 'rect';
    if (node.id == controllerId) {
      nodecolor = '#a65ba6';
      nodesize = 16;
    } else if (node.data.basic == 1){
		nodecolor = '#A7C7E7';
	} else if (node.data.neighbours.length < 1 || node.data.status == 'Dead' ) {
      nodecolor = '#d20606';
    } else if (node.data.neighbours.includes(controllerId) && node.data.status != 'Dead') {
      nodecolor = '#7BCC7B';
    } else if (node.data.status != 'Dead') {
      nodecolor = '#E5E500';
    } else {
      nodecolor = '#979797';
    } 
    var ui = Viva.Graph.svg('g'),
     svgText = Viva.Graph.svg('text').text(node.data.name),
              img = Viva.Graph.svg('image')
              .attr('width', 48)
              .attr('height', 48)
              .link(node.data.img);
            ui.append(svgText);
            ui.append(img);
            circle = Viva.Graph.svg('circle')
              .attr('r', 7)
              .attr('cx', -10)
              .attr('cy', -4)
              .attr('stroke', '#fff')
              .attr('stroke-width', '1.5px')
              .attr('fill', nodecolor);
            ui.append(circle);
    $(ui).hover(function () {
      var link = 'index.php?v=d&p=zwavejs&m=zwavejs&logical_id=' + node.id;
      numneighbours = node.data.neighbours.length;
      interview = node.data.interview;
      sentenceneighbours = numneighbours + ' {{voisins}} [' + node.data.neighbours + ']';
      if (node.id != controllerId) {
        linkname = '<a href="' + link + '">' + node.data.eqname + '</a>'
      } else {
        linkname = node.data.eqname
      }
      $('#graph-node-name').html(linkname + ' : ' + sentenceneighbours);
      highlightRelatedNodes(node.id, true);
    }, function () {
      highlightRelatedNodes(node.id, false);
    });
    return ui;
  }).placeNode(function (nodeUI, pos) {
    nodeUI.attr('transform',
    'translate(' +
    (pos.x - nodeSize / 3) + ',' + (pos.y - nodeSize / 2.5) +
    ')');
  });
  var middle = graph.getNode(controllerId);
  if (typeof middle !== 'undefined') {
    middle.isPinned = true;
  }
  var idealLength = 200;
  var layout = Viva.Graph.Layout.forceDirected(graph, {
    springLength: idealLength,
    stableThreshold: 0.9,
    dragCoeff: 0.01,
    springCoeff: 0.0004,
    gravity: -20,
    springTransform: function (link, spring) {
      spring.length = idealLength * (1 - link.data.lengthfactor);
    }
  });
  graphics.link(function (link) {
    dashvalue = '5, 0';
    if (link.data.isdash == 1) {
      dashvalue = '5, 2';
    }
    return Viva.Graph.svg('line').attr('stroke', '#B7B7B7').attr('stroke-dasharray', dashvalue).attr('stroke-width', '0.4px');
  });
  $('#graph_network svg').remove();
  var renderer = Viva.Graph.View.renderer(graph, {
    layout: layout,
    graphics: graphics,
    prerender: 10,
    renderLinks: true,
    container: document.getElementById('graph_network')
  });
  renderer.run();
  setTimeout(function () {
    renderer.pause();
    renderer.reset();
  }, 200);
  
  
}

$('.controller_action').on('click',function(){
  if($(this).data('action') == 'hardReset'){
    $action = $(this).data('action');
    bootbox.confirm("Etes-vous sûr ? Cette opération est risquée", function (result) {
      if (result) {
        bootbox.confirm("Etes-vous certain ? Votre contrôleur sera remis d'usine.", function (result) {
            if (result) {
                jeedom.zwavejs.controller.action({
                  action : $action,
                  error: function (error) {
                    $('#div_networkzwavejsAlert').showAlert({message: error.message, level: 'danger'});
                  },
                  success: function () {
                    $('#div_networkzwavejsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
                  }
                });
            }
        });
      }
    });
  }else{
    jeedom.zwavejs.controller.action({
      action : $(this).data('action'),
      error: function (error) {
        $('#div_networkzwavejsAlert').showAlert({message: error.message, level: 'danger'});
      },
      success: function () {
        $('#div_networkzwavejsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
      }
    });
  }
});

$("body").off("click", ".namingAction").on("click", ".namingAction", function (e) {
  jeedom.zwavejs.controller.namingAction({
    action : 'namingAction',
    nodeId : 'all',
    error: function (error) {
      $('#div_networkzwavejsAlert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      $('#div_networkzwavejsAlert').showAlert({message: '{{Action réalisée avec succès}}', level: 'success'});
    }
  });
});


function network_load_info(){
  jeedom.zwavejs.network.info({
    info : 'getInfo',
    global:false,
    error: function (error) {
      $('#div_networkzwavejsAlert').showAlert({message: error.message, level: 'danger'});
    }
  })
}

function network_refresh_neighbors(){
  jeedom.zwavejs.controller.action({
      action : 'refreshNeighbors',
      error: function (error) {
        $('#div_networkzwavejsAlert').showAlert({message: error.message, level: 'danger'});
      },
      success: function () {
        $('#div_networkzwavejsAlert').showAlert({message: '{{Demande des voisins en cours ...}}', level: 'warning'});
      }
    });
}

function network_load_nodes(){
  jeedom.zwavejs.network.getNodes({
    info : 'getNodes',
    mode : 'stats',
    global:false,
    error: function (error) {
      $('#div_networkzwavejsAlert').showAlert({message: error.message, level: 'danger'});
    }
  })
}

function getRoutesCount(nodeId) {
  var routesCount = {};
  $.each(getFarNeighbours(nodeId), function (index, nnode) {
    if (nnode.nodeId in routesCount) {
      if (nnode.hops in routesCount[nnode.nodeId]){
        routesCount[nnode.nodeId][nnode.hops]++;
      } else{
        routesCount[nnode.nodeId][nnode.hops] = 1;
      }
    } else {
      routesCount[nnode.nodeId] = new Array();
      routesCount[nnode.nodeId][nnode.hops] = 1;
    }
  });
  return routesCount;
}

function getFarNeighbours(nodeId, exludeNodeIds, hops) {
  if (hops === undefined) {
    var hops = 0;
    var exludeNodeIds = [nodeId];
  }
  if (hops > 2)
  return [];
  var nodesList = [];
  $.each(devicesRouting[nodeId].neighbors, function (index, nnodeId) {
    if (!(nnodeId in devicesRouting)){
      return;
    }
    if (!in_array(nnodeId, exludeNodeIds)) {
      nodesList.push({nodeId: nnodeId, hops: hops});
      if (devicesRouting[nnodeId].isListening && devicesRouting[nnodeId].isRouting){
        $.merge(nodesList, getFarNeighbours(nnodeId, $.merge([nnodeId], exludeNodeIds), hops + 1));
      }
    }
  });
  return nodesList;
}

$('body').off('zwavejs::getNeighbors').on('zwavejs::getNeighbors', function (_event, _options) {
	$('.getNodes-controllerNeighbors').empty().append(_options['controllerNeighbors']);
	network_load_nodes();
});

$('body').off('zwavejs::getInfo').on('zwavejs::getInfo', function (_event, _options) {
	for (key in _options){
		value = _options[key];
		if (key == 'uptime') {
			value = jeedom.zwavejs.durationConvert(_options[key]);
		}
		$('.getInfo-'+key).empty().append(value);
	}
});

$('body').off('zwavejs::getNodeStats').on('zwavejs::getNodeStats', function (_event, _options) {
	for (key in _options){
		value = _options[key];
		if (key == 'stats') {
			for (stat in value) {
				valueStat = value[stat]
				$('.getStats-'+stat).empty().append(valueStat);
			}
		} else if (key == 'networkTree'){
			networkTree = value
		} else {
			if (key == 'controllerNeighbors') {
				if (_options[key] == ''){
					network_refresh_neighbors();
				}
			}
			$('.getNodes-'+key).empty().append(value);
		}
	}
});
network_load_info();
network_load_nodes();
