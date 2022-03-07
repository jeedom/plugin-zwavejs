<?php
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

if (!isConnect('admin')) {
	throw new Exception('401 Unauthorized');
}
sendVarToJs('nodeId', init('id'));

include_file('3rdparty', 'jsonTree/jsonTree', 'css', 'openzwave');
include_file('3rdparty', 'jsonTree/jsonTree', 'js', 'openzwave');
?>
<style media="screen" type="text/css">
.noscrolling {
	width: 99%;
	overflow: hidden;
}
.node-item {
	border: 1px solid;
}
.greeniconcolor {
	color: green;
}
.yellowiconcolor {
	color: #FFD700;
}
.rediconcolor {
	color: red;
}
.pendingcolor {
	color: #FFD700
}
.rejectcolor {
	color: #e74c3c
}
.table-striped > tbody > tr.yellowrow > td {
	background-color: #FFD700;
}
.table-striped > tbody > tr.redrow > td {
	background-color: #e74c3c;
}
</style>
<div id='div_nodeConfigureOpenzwaveAlert' style="display: none;"></div>
<div class='node' nid='' id="div_nodeConfigure">
	<div class="container-fluid">
		<h3>
			<span class="node-name fixed">{{inconnu}}</span> - {{Node Id:}} <span class="node-id">{{inconnu}}</span>
		</h3>
		<div id="content">
			<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
				<li id="tab-summary" class="active"><a href="#summary" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Résumé}}</a></li>
				<li id="tab-values"><a href="#values" data-toggle="tab"><i class="fas fa-tag"></i> {{Valeurs}}</a></li>
				<li id="tab-groups"><a href="#groups" data-toggle="tab"><i class="fas fa-users"></i> {{Associations}}</a></li>
				<li id="tab-actions"><a href="#actions" data-toggle="tab"><i class="fas fa-sliders-h"></i> {{Actions}}</a></li>
				<li id="tab-stats"><a href="#statistics" data-toggle="tab"><i class="fas fa-chart-bar"></i> {{Statistiques}}</a></li>
				<li id="tab-stats"><a href="#tree" data-toggle="tab"><i class="fas fa-chart-bar"></i> {{Arbre}}</a></li>
			</ul>
			<div id="my-tab-content" class="tab-content">
				<div class="tab-pane active" id="summary">
					<br>
					<div class="panel panel-primary template">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fas fa-info-circle"></i> {{Informations Noeud}}</h4>
						</div>
						<div class="panel-body">
							<p>{{Node id : }} <b><span class="getNodeInfo-id label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Modèle :}}<b><span class="getNodeInfo-productLabel label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Fabricant : }}<b><span class="getNodeInfo-manufacturer label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Description : }}<b><span class="getNodeInfo-productDescription label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Identifiant : }}<b><span class="getNodeInfo-deviceId label label-default" style="font-size : 1em;"></span> <span class="getNodeInfo-hexId label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Statut : }}<b><span class="getNodeInfo-status label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Initié : }}<b><span class="getNodeInfo-inited label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Stage : }}<b><span class="getNodeInfo-interviewStage label label-default" style="font-size : 1em;"></span></b></p>
							<p>{{Voisins : }} <span class="getNodeInfo-neighbors label label-default" style="font-size : 1em;"></span></p>
							<p>{{Prêt : }} <span class="getNodeInfo-ready label label-default" style="font-size : 1em;"></span></p>
							<p>{{Disponible : }} <span class="getNodeInfo-available label label-default" style="font-size : 1em;"></span></p>
							<p>{{En échec : }} <span class="getNodeInfo-failed label label-default" style="font-size : 1em;"></span></p>
							<p>{{FLIRS : }} <span class="getNodeInfo-isFrequentListening label label-default" style="font-size : 1em;"></span></p>
							<p>{{Contrôleur : }} <span class="getNodeInfo-isControllerNode label label-default" style="font-size : 1em;"></span></p>
							<p>{{Routing : }} <span class="getNodeInfo-isRouting label label-default" style="font-size : 1em;"></span></p>
							<p>
								<span class="zwaveNodeAttr" data-l1key="zwave_id"></span></p>
								<p>{{Etat des demandes :}}
									<b><span class="node-queryStage label label-default" data-l1key="queryStage" style="font-size : 1em;"></span></b>
								</p>
								<p>{{Etat :}} <b><span class="node-sleep label label-default" data-l1key="location" data-l2key="value" style="font-size : 1em;"></span></b>
									<span class="node-battery-span">{{Batterie : }} <b><span class="zwaveNodeAttr label label-default" data-l1key="battery_level" data-l2key="value" style="font-size : 1em;"></span>%</b></span>
								</p>
								<p>{{Dernier message :}}
									<b><span class="zwaveNodeAttr label label-default" data-l1key="lastReceived" data-l2key="updateTime" style="font-size : 1em;"></span></b>
									<span class="node-next-wakeup-span">{{Prochain réveil (prévu): }}
										<b><span class="zwaveNodeAttr label label-default" data-l1key="wakeup_interval" data-l2key="next_wakeup" style="font-size : 1em;"></span></b>
									</span>
								</p>
							</div>
						</div>
						<div class="panel panel-primary template">
							<div class="panel-heading">
								<h4 class="panel-title"><i class="fas fa-info-circle"></i> {{Classe du module}}</h4>
							</div>
							<div class="panel-body">
								{{Basique :}} <b><span class="zwaveNodeAttr label label-default" data-l1key="basicDeviceClassDescription"></span></b><br/>
								{{Générique :}} <b><span class="zwaveNodeAttr label label-default" data-l1key="genericDeviceClassDescription"></span></b><br/>
								{{Spécifique :}} <b><span class="zwaveNodeAttr label label-default" data-l1key="type" data-l2key="value"></span></b></p>
							</div>
						</div>
						<div class="panel panel-primary template">
							<div class="panel-heading">
								<h4 class="panel-title"><i class="fas fa-info-circle"></i> {{Informations Protocole}}
								</h4>
							</div>
							<div class="panel-body">
								<p>{{Vitesse maximale de communication du module : }}<b><span class="getNodeInfo-maxDataRate"></span></b> {{bit/sec}}</p>
								<b><span class="node-routing" data-l1key="location" data-l2key="value"></span></b>
								<b><span class="node-isSecurity" data-l1key="location" data-l2key="value"></span></b>
								<b><span class="node-listening" data-l1key="location" data-l2key="value"></span></b>
								<b><span class="node-isFrequentListening" data-l1key="location" data-l2key="value"></span></b>
								<b><span class="node-isBeaming" data-l1key="location" data-l2key="value"></span></b>
								<br/>
								<p><b><span class="node-security"></span></b></p>
							</div>
						</div>
					</div>
					<div class="tab-pane" id="values">
						<div class="getNodeInfo-nodeValues"></div>
					</div>
					<div class="tab-pane" id="groups">
						<a id="addGroup" class="btn btn-primary addGroup"><i class="fas fa-plus"></i>
							{{Ajouter une association}}
						</a>
						<br>
						<table class="table table-striped">
							<tr>
								<th>{{ID du groupe}}</th>
								<th>{{ID de noeud}}</th>
								<th></th>
							</tr>
							<tr id="template-group" style="display:none">
								<td key="group-groupeindex"></td>
								<td key="group-nodeindex"></td>
								<td key="group-delete"></td>
							</tr>
							<tbody class="groups"></tbody>
						</table>
					</div>
					<div class="tab-pane" id="actions">
						<table class="table table-striped">
							<tr>
								<td><a data-action="requestNodeNeighboursUpdate" class="btn btn-primary node_action"><i class="fas fa-sitemap"></i> {{Mise à jour des nœuds voisins}}</a></td>
								<td>{{Force la mise à jour de la liste des nœuds voisins.}}</td>
							</tr>
							<tr>
								<td><a data-action="healNode" class="btn btn-success node_action"><i class="fas fa-medkit"></i> {{Soigner le nœud}}</a></td>
								<td>{{Soigner le nœud au sein du réseau.}}</td>
							</tr>
							<tr>
								<td><a data-action="assignReturnRoute" class="btn btn-success node_action"><i class="fas fa-road"></i> {{Mise à jour de la route de retour au contrôleur}}</a></td>
								<td>{{Demandez la mise à jour de la route de retour au contrôleur.}}</td>
							</tr>
							<tr>
								<td><a data-action="testNode" class="btn btn-info node_action"><i class="fas fa-check-square"></i> {{Tester le nœud}}</a></td>
								<td>{{Envoyer une série de message à un noeud pour le tester s'il répond.}}</td>
							</tr>
							<tr>
								<td><a data-action="refreshNodeValues" class="btn btn-success node_action"><i class="fas fa-sync-alt"></i> {{Rafraîchir les valeurs du nœud}}</a></td>
								<td>{{Demande l'actualisation de l'ensemble des valeurs du nœud.}}</td>
							</tr>
							<tr>
								<td><a data-action="requestNodeDynamic" class="btn btn-success node_action"><i class="fas fa-sync-alt"></i> {{Récupère les CC dynamiques}}</a></td>
								<td>{{Récupère les données de commande classe dynamiques du nœud.}}</td>
							</tr>
							<tr>
								<td><a data-action="refreshNodeInfo" class="btn btn-success node_action"><i class="fas fa-retweet"></i> {{Rafraîchir infos du nœud}}</a></td>
								<td>{{Déclencher l'obtention des informations du nœud.}} <br>{{Les données du nœud sont obtenues du réseau Z-Wave de la même façon que s'il venait d'être ajouté.}}</td>
							</tr>
							<tr>
								<td><a data-action="hasNodeFailed" class="btn btn-primary node_action"><i class="fas fa-heartbeat"></i> {{Nœud en échec ?}}</a></td>
								<td>{{Vérifie si le nœud est dans la liste des nœuds en erreur.}}</td>
							</tr>
							<tr>
								<td><a data-action="removeFailedNode" class="btn btn-danger node_action"><i class="fas fa-times"></i> {{Supprimer le nœud en échec}}</a></td>
								<td>{{Permet de supprimer un nœud marqué comme défaillant par le contrôleur.}}<br>{{Le nœud doit être en échec.}}</td>
							</tr>
							<tr>
								<td><a id="replaceFailedNode" class="btn btn-warning"><i class="fas fa-unlink"></i> {{Remplacer nœud en échec}}</a></td>
								<td>{{Remplace un module en échec par un autre. Si le nœud n'est pas dans la liste des nœuds en échec sur le contrôleur, ou que le nœud répond, la commande va échouer.}}</td>
							</tr>
							<tr>
								<td><a data-action="sendNodeInformation" class="btn btn-info node_action"><i class="fas fa-info-circle"></i> {{Envoi infos du nœud}}</a></td>
								<td>{{Envoi une trame d'info au noeud (NIF).}}</td>
							</tr>
							<tr>
								<td><a id="regenerateNodeCfgFile" class="btn btn-warning node_action"><i class="fas fa-search"></i> {{Régénérer la détection du nœud}}</a></td>
								<td>{{Supprime les informations du noeud dans le fichier de config afin qu'il soit à nouveau détecté.}}<br>{{(Attention : Relance le réseau)}}</td>
							</tr>
							<tr>
								<td><a data-action="removeGhostNode" class="btn btn-warning node_action"><i class="fas fa-bug"></i> {{Suppression automatique du nœud fantôme}}</a></td>
								<td>{{Permet de supprimer un nœud sur pile qui n'est plus accessible sur le réseau.}}<br>{{Le nœud sera automatiquement supprimé dans les 5 à 15 minutes suivant le redémarrage du réseau}}<br>{{(Attention : Relance le réseau)}}</td>
							</tr>
						</table>
					</div>
					<div class="tab-pane" id="statistics">
						<table class="table table-condensed table-striped">
						<tr>
							<td><b>{{Messages transmis TX :}}</b></td>
							<td><span class="getNodeStats-commandsTX"></span></td>
						</tr>
						<tr>
							<td><b>{{Messages reçus RX :}}</b></td>
							<td><span class="getNodeStats-commandsRX"></span></td>
						</tr>
						<tr>
							<td><b>{{Messages RX abandonnés :}}</b></td>
							<td><span class="getNodeStats-commandsDroppedRX"></span></td>
						</tr>
						<tr>
							<td><b>{{Messages TX abandonnés :}}</b></td>
							<td><span class="getNodeStats-commandsDroppedTX"></span></td>
						</tr>
						<tr>
							<td><b>{{Réponses en délai dépassé :}}</b></td>
							<td><span class="getNodeStats-timeoutResponse"></span></td>
						</tr>
						<tr>
					</table>
					</div>
					<div class="tab-pane" id="tree">
						<div id="div_nodeDataTree"></div>
					</div>
				</div>
			</div>
		</div>
</div>
<?php include_file('desktop', 'nodes', 'js', 'zwavejs');?>