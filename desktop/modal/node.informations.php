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

include_file('3rdparty', 'jsonTree/jsonTree', 'css', 'zwavejs');
include_file('3rdparty', 'jsonTree/jsonTree', 'js', 'zwavejs');
?>


<div id="div_nodeInformationsZwaveJsAlert" style="display: none;"></div>


<div class="modalNodeInformations">
	<div class="container-fluid">
		<div id="content">
			<ul id="tabs" class="nav nav-tabs" data-tabs="tabs">
				<li id="tab-summary" class="active"><a href="#summary" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Résumé}}</a></li>
				<li id="tab-actions"><a href="#actions" data-toggle="tab"><i class="fas fa-sliders-h"></i> {{Actions}}</a></li>
				<li id="tab-stats"><a href="#statistics" data-toggle="tab"><i class="fas fa-chart-bar"></i> {{Statistiques}}</a></li>
				<li id="tab-stats"><a href="#tree" data-toggle="tab"><i class="fas fa-tree"></i> {{Arbre}}</a></li>
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
							<p>{{Modèle :}} <b><span class="getNodeInfo-productLabel label label-default" style="font-size : 1em;"></span></b></p>
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
					<div class="tab-pane" id="actions">
						<table class="table table-striped">
							<tr>
								<td><a data-action="pingNode" class="btn btn-primary nodeAction"><i class="fas fa-sitemap"></i> {{Ping du nœud}}</a></td>
								<td>{{Fait un ping du nœud.}}</td>
							</tr>
							<tr>
								<td><a data-action="healNode" class="btn btn-success nodeAction"><i class="fas fa-medkit"></i> {{Soigner le nœud}}</a></td>
								<td>{{Soigner le nœud au sein du réseau.}}</td>
							</tr>
							<tr>
								<td><a data-action="refreshValues" class="btn btn-success nodeAction"><i class="fas fa-sync-alt"></i> {{Rafraîchir les valeurs du nœud}}</a></td>
								<td>{{Demande l'actualisation de l'ensemble des valeurs du nœud.}}</td>
							</tr>
							<tr>
								<td><a class="btn btn-success namingAction"><i class="fas fa-bookmark"></i> {{Envoyer le nom d'équipement}}</a></td>
								<td>{{Enverra le noms et objet de l'équipements au réseau Zwave.}}</td>
							</tr>
							<tr>
								<td><a data-action="refreshInfo" class="btn btn-warning nodeAction"><i class="fas fa-retweet"></i> {{Rafraîchir le nœud}}</a></td>
								<td>{{Déclencher l'obtention des informations du nœud.}} <br>{{Les données du nœud sont obtenues du réseau Z-Wave de la même façon que s'il venait d'être ajouté.}}</td>
							</tr>
							<tr>
								<td><a data-action="isFailedNode" class="btn btn-primary nodeAction"><i class="fas fa-heartbeat"></i> {{Nœud en échec ?}}</a></td>
								<td>{{Vérifie si le nœud est dans la liste des nœuds en erreur.}}</td>
							</tr>
							<tr>
								<td><a data-action="removeFailedNode" class="btn btn-danger nodeAction"><i class="fas fa-times"></i> {{Supprimer le nœud du réseau}}</a></td>
								<td>{{Permet de supprimer un nœud du réseau.}}</td>
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
<?php include_file('desktop', 'nodeInformations', 'js', 'zwavejs');?>