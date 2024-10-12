<?php

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

if (!isConnect('admin')) {
	throw new Exception('401 - {{Accès non autorisé}}');
}
$plugin = plugin::byId('zwavejs');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$controllerStatus = config::byKey('controllerStatus', 'zwavejs', 'none');
$mode = config::byKey('zwavejs_mode',  'zwavejs', '');
$driverStatus = config::byKey('driverStatus', 'zwavejs', 0);

if (!zwavejs::isRunning()) {
	echo "<div id='div_driverStatus'><div class='alert alert-danger' role='alert'> {{Le service ZWaveJS n&apos;est pas démarré.}}</div></div>";
} else if (($mode == 'local') && ($driverStatus != 1)) {
	echo "<div id='div_driverStatus'><div class='alert alert-warning' role='alert'> {{Le driver Z-Wave n&apos;est pas initialisé, veuillez patienter. 
	Si le message reste trop longtemps, veuillez vérifier la configuration du démon}}</div></div>";
} else {
	echo '<div id="div_driverStatus"></div>';
}
switch ($controllerStatus) {
	case 'none':
		echo '<div id="div_inclusionAlert"></div>';
		break;
	case 'inclusion':
		echo '<div id="div_inclusionAlert"><div class="alert alert-warning" role="alert"> {{Une inclusion est en cours}}</div></div>';
		break;
	case 'exclusion':
		echo '<div id="div_inclusionAlert"><div class="alert alert-warning" role="alert">{{Une exclusion est en cours}}</div></div>';
		break;
}
$tags = array();
if (is_array($eqLogics)) {
	foreach ($eqLogics as $eqLogic) {
		$tags[$eqLogic->getLogicalId()] = $eqLogic->getHumanName(true);
	}
}
sendVarTojs('eqLogic_human_name', $tags);
?>
<div class="row row-overflow">
	<div class="col-xs-12 eqLogicThumbnailDisplay">
		<legend><i class="fas fa-cog"></i> {{Gestion}}</legend>
		<div class="eqLogicThumbnailContainer">
			<?php
			echo '<div class="cursor changeIncludeState logoPrimary">';
			echo '<i class="fas fa-exchange-alt"></i>';
			echo '<br>';
			echo '<span>{{Inclusions}}</span>';
			echo '</div>';
			?>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br>
				<span>{{Configuration}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_syncEqLogic">
				<i class="fas fa-sync-alt"></i>
				<br>
				<span>{{Synchroniser}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_zwaveNetwork">
				<i class="fas fa-sitemap"></i>
				<br>
				<span>{{Réseau Z-Wave}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_zwaveHealth">
				<i class="fas fa-medkit"></i>
				<br>
				<span>{{Santé}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_zwaveStats">
				<i class="fas fa-chart-bar"></i>
				<br>
				<span>{{Statistiques}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_zwaveWaiting">
				<i class="fa fa-user-clock"></i>
				<br>
				<span>{{En attente}}</span>
			</div>
		</div>
		<legend><i class="fas fa-broadcast-tower"></i> {{Mes équipements Z-Wave}}</legend>
		<?php
		if (count($eqLogics) == 0) {
			echo '<br><div class="text-center" style="font-size:1.2em;font-weight:bold;">{{Aucun équipement trouvé}}</div>';
		} else {
			echo '<div class="input-group" style="margin:5px;">';
			echo '<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic">';
			echo '<div class="input-group-btn">';
			echo '<a id="bt_resetSearch" class="btn" style="width:30px"><i class="fas fa-times"></i></a>';
			echo '<a class="btn roundedRight hidden" id="bt_pluginDisplayAsTable" data-coreSupport="1" data-state="0"><i class="fas fa-grip-lines"></i></a>';
			echo '</div>';
			echo '</div>';
			echo '<div class="eqLogicThumbnailContainer">';
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor ' . $opacity . '" data-logical-id="' . $eqLogic->getLogicalId() . '" data-eqLogic_id="' . $eqLogic->getId() . '" title="Node ID : ' . $eqLogic->getLogicalId() . '">';
				if ($eqLogic->getImgFilePath() !== false) {
					echo '<img class="lazy" src="plugins/zwavejs/core/config/devices/' . $eqLogic->getImgFilePath() . '">';
				} else {
					echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				}
				echo '<br/>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '<span class="hidden hiddenAsCard displayTableRight">';
				echo '<span class="label label-xs label-primary">' . $eqLogic->getLogicalId() . '</span>';
				echo $eqLogic->getConfiguration('product_name');
				echo ' <span class="label label-xs label-info">' . $eqLogic->getConfiguration('firmwareVersion') . '</span>';
				echo ($eqLogic->getIsVisible() == 1) ? '<i class="fas fa-eye" title="{{Equipement visible}}"></i>' : '<i class="fas fa-eye-slash" title="{{Equipement non visible}}"></i>';
				echo '</span>';
				echo '</div>';
			}
			echo '</div>';
		}
		?>
	</div>
	<div class="col-xs-12 eqLogic" style="display: none;">
		<div class="input-group pull-right" style="display:inline-flex">
			<span class="input-group-btn">
				<a class="btn btn-default eqLogicAction btn-sm roundedLeft" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}
				</a><a class="btn btn-sm btn-success eqLogicAction" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}
				</a><a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
			</span>
		</div>

		<ul class="nav nav-tabs" role="tablist">
			<li role="presentation"><a class="eqLogicAction cursor" aria-controls="home" role="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
			<li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer-alt"></i> {{Equipement}}</a></li>
			<li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
			<li role="presentation"><a href="#optionstab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-pencil-ruler"></i> {{Options}}</a></li>
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Général}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;">
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Objet parent}}</label>
								<div class="col-sm-6">
									<select class="eqLogicAttr form-control" data-l1key="object_id">
										<option value="">{{Aucun}}</option>
										<?php
										$options = '';
										foreach ((jeeObject::buildTree(null, false)) as $object) {
											$options .= '<option value="' . $object->getId() . '">' . str_repeat('&nbsp;&nbsp;', $object->getConfiguration('parentNumber')) . $object->getName() . '</option>';
										}
										echo $options;
										?>
									</select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Catégorie}}</label>
								<div class="col-sm-6">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '">' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Options}}</label>
								<div class="col-sm-6">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked>{{Visible}}</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Id du nœud Z-Wave}}</label>
								<div class="col-sm-6">
									<input type="text" class="eqLogicAttr form-control" data-l1key="logicalId">
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Modèle}}</label>
								<div class="col-sm-6">
									<span class="label label-info">
										<span class="eqLogicAttr" data-l1key="configuration" data-l2key="product_name"></span>
									</span>
									<a class="confRecommended btn btn-xs btn-warning" title="{{Configuration recommandée}}" style="display:none"><i class="fas fa-flag"></i></a>
								</div>

							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Firmware}}</label>
								<div class="col-sm-6">
									<span class="label label-info">
										<span class="eqLogicAttr" data-l1key="configuration" data-l2key="firmwareVersion"></span>
									</span>
								</div>
							</div>
							<div class="form-group confModes" style="display:none">
								<label class="col-sm-4 control-label">{{Mode}} <sup><i class="fas fa-question-circle tooltips" title="{{Permet de recréer les commandes en fonction du mode de fonctionnement du module désiré. Sauver le mode puis sur la page de commande rechargez vos commandes}}"></i><sup></label>
								<div class="col-sm-6">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="confMode"></select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Commandes}}
								<sup><i class="fas fa-question-circle tooltips" title="{{Nombre de commandes actuelles en ignorant les 4 commandes techniques de chaque équipement du plugin}}"></i></sup></label>
								<div class="col-sm-6">
									<span class="label label-info">
										<span class="command_number"></span>
									</span>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label">{{Visuel}}</label>
								<div class="col-sm-6">
									<img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height:120px;">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-4 control-label"></label>
								<div class="input-group" style="display:inline-flex">
									<span class="input-group-btn">
										<a class="nodeInformations btn btn-primary roundedLeft" title="{{Informations du nœud}}"><i class="fas fa-fingerprint"></i> {{Nœud}}</a>
										<a class="nodeValues btn btn-primary" title="{{Valeurs du nœud}}"><i class="fas fa-list"></i> {{Valeurs}}</a>
										<a class="nodeGroups btn btn-primary roundedRight" title="{{Groupes du nœud}}"><i class="fas fa-layer-group"></i> {{Groupes}}</a>
									</span>
								</div>
							</div>
							<div class="assistant" style="display:none">
								<div class="assistantText alert alert-info" role="alert">
								</div>
							</div>
						</div>
					</fieldset>
				</form>
				<hr>
				<div class="incompleteInfo" style="display:none">
					<div class="alert alert-warning" role="alert"> {{Le nœud n'a pas encore été initié. Il sera mis à jour automatiquement lorsque l'initialisation sera terminée. Cela prendra quelques secondes. En cas d'inclusion sécurisée ou de module sur piles cela peut être plus long. Pour un nœud sur pile si le message persiste vous pouvez essayer de le réveiller manuellement.}}</div>
				</div>
				<div class="nocommand" style="display:none">
					<div class="alert alert-info" role="alert"> {{Le nœud n'a pas encore de}} <b>{{commande}}</b>. {{Cela peut arriver et peut avoir différentes causes :}}
						<br><br>{{Soit le module c'est initié trop tôt et dans ce cas, il vous suffit de cliquer sur}}<b> {{"Synchroniser"}} </b>{{sur la page précédente et ensuite de cliquer sur}} <b>{{"Recharger commandes"}} </b>{{sur la page du tableau de commandes.}}
						<br><br>{{Soit le module n'a pas encore de configuration Jeedom. Vous pouvez le vérifier en cliquant sur le bouton}} <b>{{"Nœud"}}</b> {{ sur la page d'équipement. Si vous ne voyez pas de configuration à côté de}} <b>{{"Configuration Jeedom"}}. </b>{{ Alors vous pouvez aller dans }} <b>{{"Valeurs"}}</b> {{ et créer les commandes dont vous avez besoin en cliquant sur les crayons.}}
						<br><br>{{Soit cela est normal, dans le cas d'un répéteur ou d'un controlleur par exemple}}
					</div>
				</div>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<div class="input-group pull-right" style="display:inline-flex">
					<span class="input-group-btn">
						<a class="btn btn-success btn-sm cmdAction roundedLeft" data-action="add" style="margin-top:5px;"> <i class="fas fa-plus-circle"></i> {{Commandes}}</a>
						<a id="bt_autoDetectModule" class="btn btn-danger btn-sm roundedRight" style="margin-top:5px;"><i class="fas fa-search"></i> {{Recharger commandes}}</a>
					</span>
				</div>
				<br><br>
				<div class="table-responsive">
					<table id="table_cmd" class="table table-bordered table-condensed tablesorter">
						<thead>
							<tr>
								<th class="hidden-xs" style="min-width:50px; max-width:50px;">{{Id}}</th>
								<th data-sortable="true" data-sorter="inputs" style="min-width:150px;width:250px;">{{Nom}}</th>
								<th data-sorter="select-text">{{Type}}</th>
								<th data-sortable="true" data-sorter="inputs">{{Classe}}</th>
								<th data-sortable="true" data-sorter="inputs">{{Endpoint}}</th>
								<th data-sortable="true" data-sorter="inputs" style="min-width:260px;width:400px;">{{Propriété}}</th>
								<th data-sorter="false" data-filter="false" style="min-width:130px;">{{Paramètres}}</th>
								<th data-sorter="false" data-filter="false">{{Etat}}</th>
								<th data-sorter="false" data-filter="false" style="min-width:260px;width:400px;">{{Options}}</th>
								<th data-sorter="false" data-filter="false" style="min-width:80px;">{{Actions}}</th>
							</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
			
			<div role="tabpanel" class="tab-pane" id="optionstab">
				<br>
				<legend><i class="fas fa-sync"></i> {{Rafraîchissement}}</legend>
				<form class="form-horizontal">
					<fieldset>
						<div class="alert alert-warning col-xs-10 col-xs-offset-1">
							{{Cette section permet de définir des règles de rafraîchissement automatique après action. Il est primordial de ne rien mettre ici sans raison valable sous peine de pénaliser votre réseau Z-Wave. Cette possibilité est disponible pour gérer certains très rares modules qui ont des bugs.}}
							<br>
							{{Si c'est nécessaire, cette section sera sûrement prérempli par la configuration Jeedom. La durée totale (nombre x attente) ne peut pas excéder 100s}}
							<br><br>
							<a class="btn btn-default col-xs-6 col-xs-offset-3" id="bt_addRefresh"><i class="fas fa-plus"></i> {{Ajouter une Règle}}</a>
						</div>
						<table class="table table-bordered table-condensed" id="table_zwaveRefresh">
							<thead>
								<tr>
									<th style="width:250px">{{Source}}
										<sup><i class="fas fa-question-circle tooltips" title="{{Commande action déclenchant le cycle de rafraîchissement (au format cc-endpoint-property-value(optionnel))}}"></i></sup>
									</th>
									<th style="width:180px;">{{Cible}}
										<sup><i class="fas fa-question-circle tooltips" title="{{Commande info devant être rafraîchie (au format cc-endpoint-property)}}"></i></sup>
									</th>
									<th style="width:100px;">{{Attente}}
										<sup><i class="fas fa-question-circle tooltips" title="{{Temps d'attente entre chaque demande (en s)}}"></i></sup>
									</th>
									<th style="width:100px;">{{Nombre}}
										<sup><i class="fas fa-question-circle tooltips" title="{{Nombre de demande}}"></i></sup>
									</th>
									<th style="min-width:50px;width:380px">{{Commentaire}}</th>
								</tr>
							</thead>
							<tbody>
							</tbody>
						</table>
					</fieldset>
				</form>
			</div>
		</div>
	</div>
</div>
<?php include_file('core', 'zwavejs', 'class.js', 'zwavejs');
include_file('desktop', 'zwavejs', 'js', 'zwavejs');
include_file('core', 'plugin.template', 'js'); ?>
