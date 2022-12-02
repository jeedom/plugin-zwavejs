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
	throw new Exception('401 - {{Accès non autorisé}}');
}
?>
<script type="text/javascript" src="plugins/zwavejs/3rdparty/vivagraph/vivagraph.min.js"></script>
<style>
	#route_network {
		overflow-x: auto;
	}

	#graph_network {
		height: 80%;
		width: 97%;
		position: absolute;
		background-color: rgba(var(--panel-bg-color), var(--opacity)) !important;
	}

	#graph_network>svg {
		height: 100%;
		width: 100%
	}

	.node-item {
		border: 1px solid;
	}

	.node-primary-controller-color {
		color: #a65ba6;
	}

	.node-direct-link-color {
		color: #7BCC7B;
	}

	.node-remote-control-color {
		color: #00a2e8;
	}

	.node-more-of-one-up-color {
		color: #E5E500;
	}

	.node-more-of-two-up-color {
		color: #FFAA00;
	}

	.node-interview-not-completed-color {
		color: #979797;
	}

	.node-no-neighbourhood-color {
		color: #d20606;
	}

	.node-na-color {
		color: white;
	}

	.node-controller {
		color: #A7C7E7;
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

	#graph_network svg g text {
		fill: var(--txt-color) !important;
	}
</style>
<div id="div_networkzwavejsAlert" style="display: none;"></div>
<div class="modalnetWork">
	<div class="network" id="div_templateNetwork">
		<div class="container-fluid">
			<div id="content">
				<ul id="tabs_network" class="nav nav-tabs" data-tabs="tabs">
					<li class="active"><a href="#summary_network" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Résumé}}</a></li>
					<li><a href="#actions_network" data-toggle="tab"><i class="fas fa-sliders-h"></i> {{Actions}}</a></li>
					<li id="tab_backup"><a href="#backup_network" data-toggle="tab"><i class="fas fa-save"></i> {{Sauvegardes NVM}}</a></li>
					<li><a href="#statistics_network" data-toggle="tab"><i class="far fa-chart-bar"></i> {{Statistiques}}</a></li>
					<li id="tab_graph"><a href="#graph_network" data-toggle="tab"><i class="far fa-image"></i> {{Graphique du réseau}}</a></li>
					<li id="tab_route"><a href="#route_network" data-toggle="tab"><i class="fas fa-table"></i> {{Table de routage}}</a></li>
				</ul>
				<div id="network-tab-content" class="tab-content">
					<div class="tab-pane active" id="summary_network">
						<br>
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h4 class="panel-title"><i class="fas fa-chess-king"></i> {{Contrôleur}}</h4>
							</div>
							<div class="panel-body">
								<p><b>{{Réseau démarré depuis}} : </b><span class="getInfo-uptime label label-info" style="font-size : 1em;"></span></p>
								<p><b>{{Id du contrôleur}} : </b><span class="getInfo-controllerId label label-primary" style="font-size : 1em;"></span></p>
								<p><b>{{Home id}} : </b><span class="getInfo-name label label-info" style="font-size : 1em;"></span> <span class="getInfo-homeid label label-info" style="font-size : 1em;"></span></p>
								<p><b>{{Voisins}} : </b><span class="getNodes-controllerNeighbors label label-info" style="font-size : 1em;"></span></p>
							</div>
						</div>
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h4 class="panel-title"><i class="fas fa-sitemap"></i> {{Réseau}}</h4>
							</div>
							<div class="panel-body">
								<p><b>{{Nombre de nœuds}} : </b><span class="getNodes-totalNodes label label-info"></span></p>
								<p><b>{{Nœuds endormis}} : </b><span class="getNodes-sleepingNodes label label-info"></span></p>
								<p><b>{{Etat actuel}} : </b><span class="getInfo-status label label-info" style="font-size : 1em;"></span> </p>
								<p><b>{{Dernière action réseau}} : </b><span class="getInfo-cntStatus label label-info" style="font-size : 1em;"></span> </p>
							</div>
						</div>
						<div class="panel panel-primary">
							<div class="panel-heading">
								<h4 class="panel-title"><i class="fas fa-cog"></i> {{Système}}</h4>
							</div>
							<div class="panel-body">
								<p><b>{{Version application}} : </b><span class="getInfo-appVersion label label-info" style="font-size : 1em;"></span></p>
								<p><b>{{Version serveur}} : </b><span class="getInfo-serverVersion label label-info" style="font-size : 1em;"></span></p>
								<p><b>{{Version Z-Wave}} : </b><span class="getInfo-zwaveVersion label label-info" style="font-size : 1em;"></span></p>
							</div>
						</div>
					</div>
					<div id="graph_network" class="tab-pane">
						<table class="table table-bordered table-condensed" style="width: 250px;position:fixed;margin-top : 25px;">
							<thead>
								<tr>
									<th colspan="2">{{Légende}}</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="node-primary-controller-color" style="width: 35px"><i class="fas fa-circle"></i></td>
									<td>{{Contrôleur Primaire}}</td>
								</tr>
								<tr>
									<td class="node-direct-link-color" style="width: 35px"><i class="fas fa-circle"></i></td>
									<td>{{Communication directe}}</td>
								</tr>
								<tr>
									<td class="node-more-of-one-up-color"><i class="fas fa-circle"></i></td>
									<td>{{Toutes les routes ont plus d'un saut}}</td>
								</tr>
								<tr>
									<td class="node-no-neighbourhood-color"><i class="fas fa-circle"></i></td>
									<td>{{Présumé mort ou Pas de voisin}}</td>
								</tr>
								<tr>
									<td class="node-controller"><i class="fas fa-circle"></i></td>
									<td>{{Contrôleur mobile}}</td>
								</tr>
								<tr>
									<td class="node-interview-not-completed-color"><i class="fas fa-circle"></i></td>
									<td>{{Interview non completé}}</td>
								</tr>
							</tbody>
						</table>
						<div id="graph-node-name"></div>
					</div>
					<div id="route_network" class="tab-pane">
						<div id="div_routingTable"></div>
						<table class="table table-bordered table-condensed" style="width: 500px;">
							<thead>
								<tr>
									<th colspan="2">{{Légende}}</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan="2">{{Nombre de [routes directes / avec 1 saut / 2 sauts]}}</td>
								</tr>
								<tr>
									<td class="node-direct-link-color" style="width: 35px"><i class="fas fa-square fa-2x"></i></td>
									<td>{{Communication directe}}</td>
								</tr>
								<tr>
									<td class="node-remote-control-color"><i class="fas fa-square fa-2x"></i></td>
									<td>{{Au moins 2 routes avec un saut}}</td>
								</tr>
								<tr>
									<td class="node-more-of-one-up-color"><i class="fas fa-square fa-2x"></i></td>
									<td>{{Moins de 2 routes avec un saut}}</td>
								</tr>
								<tr>
									<td class="node-more-of-two-up-color"><i class="fas fa-square fa-2x"></i></td>
									<td>{{Toutes les routes ont plus d'un saut}}</td>
								</tr>
								<tr>
									<td class="node-interview-not-completed-color"><i class="fas fa-square fa-2x"></i></td>
									<td>{{Interview non completé}}</td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="tab-pane" id="actions_network">
						<br>
						<table class="table">
							<tr>
								<td><a data-action="refreshNeighbors" class="btn btn-success controller_action"><i class="fas fa-project-diagram"></i> {{Demander les voisins de tout le réseau}}</a></td>
								<td>{{Demande au contrôleur les voisins de tous les nœuds y compris lui-même. Cette procédure coupe les transmissions radio le temps d'être effectuée.}}</td>
							</tr>
							<tr>
								<td><a data-action="beginHealingNetwork" class="btn btn-warning controller_action"><i class="fas fa-first-aid"></i> {{Lancer un soin du réseau}}</a></td>
								<td>{{Cette procédure lance un soin du réseau et sera longue. Elle n'est généralement pas utile.}}</td>
							</tr>
							<tr>
								<td><a data-action="stopHealingNetwork" class="btn btn-danger controller_action"><i class="fas fa-stop"></i> {{Arrêter un soin du réseau}}</a></td>
								<td>{{Arrête un soin du réseau en cours.}}</td>
							</tr>
							<tr>
								<td><a class="btn btn-success namingAction"><i class="fas fa-bookmark"></i> {{Envoyer les noms d'équipements}}</a></td>
								<td>{{Envoie les noms et objets des équipements au réseau Z-Wave.}}</td>
							</tr>
							<tr>
								<td><a data-action="softReset" class="btn btn-warning controller_action"><i class="fas fa-times"></i> {{Soft Reset}}</a></td>
								<td>{{Redémarre le contrôleur sans effacer ses paramètres de configuration réseau.}}</td>
							</tr>
							<tr>
								<td><a data-action="hardReset" class="btn btn-danger controller_action"><i class="fas fa-eraser"></i> {{Hard reset}}</a></td>
								<td>{{Remise à zéro du contrôleur.}} <b>{{Remet à zéro le contrôleur et efface ses paramètres de configuration réseau.}}</b></td>
							</tr>
							<tr>
								<td><a class="btn btn-danger controller_action" target="_blank" href="http://<?php echo network::getNetworkAccess('internal', 'ip') ?>:8091"><i class="fas fa-external-link-square-alt "></i> {{Interface ZwaveJs UI}}</a></td>
								<td><b>{{Aucun support}}</b> {{ne sera fait en cas de changement d'un réglage du}}<b> {{menu configuration de ZwaveJS UI}}</b>. {{Vous pouvez changez le mot de passe utilisateur si vous le souhaitez. Vous pouvez utiliser les fonctionnalités.}}<b> {{Mais ne changez aucun réglage.}}</b> {{Les identifiants par défaut sont}} : <span class="label label-info">admin/zwave</span></td>
							</tr>
						</table>
					</div>
					<div class="tab-pane" id="backup_network">
						<br>
						<div class="alert alert-warning">{{Les sauvegardes NVM ne sont pas intercompatibles d'un contrôleur en version de SDK Z-Wave inférieure à 6.61 à un contrôleur en version supérieure et inversement}}</div>
						<span class="getInfo-cntStatus label label-sm label-info pull-right"></span>
						<div class="input-group">
							<a data-action="backupNVMRaw" class="btn btn-success controller_action roundedLeft"><i class="fas fa-save"></i> {{Réaliser une sauvegarde NVM}}</a>
							<span class="btn btn-primary btn-file roundedRight">
								<i class="fas fa-cloud-upload-alt"></i> {{Envoyer une sauvegarde NVM}}<input id="uploadNVM" type="file" name="file" data-url="plugins/zwavejs/core/ajax/zwavejs.ajax.php?action=uploadNVMbackup">
							</span>
						</div>
						<table class="table table-striped tableBackups">
							<thead>
								<tr>
									<th>{{Sauvegardes disponibles}}</th>
									<th></th>
								</tr>
							</thead>
							<tbody></tbody>
						</table>
					</div>
					<div class="tab-pane" id="statistics_network">
						<br>
						<table class="table table-condensed table-striped">
							<tr>
								<td><b>{{Messages transmis TX}} :</b></td>
								<td><span class="getStats-messagesTX"></span></td>
							</tr>
							<tr>
								<td><b>{{Messages reçus RX}} :</b></td>
								<td><span class="getStats-messagesRX"></span></td>
							</tr>
							<tr>
								<td><b>{{Messages RX abandonnés}} :</b></td>
								<td><span class="getStats-messagesDroppedRX"></span></td>
							</tr>
							<tr>
								<td><b>{{Messages TX abandonnés}} :</b></td>
								<td><span class="getStats-messagesDroppedTX"></span></td>
							</tr>
							<tr>
								<td><b>{{NAK reçus}} :</b></td>
								<td><span class="getStats-NAK"></span></td>
							</tr>
							<tr>
								<td><b>{{CAN reçus}} :</b></td>
								<td><span class="getStats-CAN"></span></td>
							</tr>
							<tr>
								<td><b>{{ACK en délai dépassé}} :</b></td>
								<td><span class="getStats-timeoutACK"></span></td>
							</tr>
							<tr>
								<td><b>{{Réponses en délai dépassé}} :</b></td>
								<td><span class="getStats-timeoutResponse"></span></td>
							</tr>
							<tr>
								<td><b>{{Callback en délai dépassé}} :</b></td>
								<td><span class="getStats-timeoutCallback"></span></td>
							</tr>
							<tr>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<?php include_file('desktop', 'network', 'js', 'zwavejs'); ?>
