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
	throw new Exception('{{401 - Accès non autorisé}}');
}
$plugin = plugin::byId('zwavejs');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
$controllerStatus = config::byKey('controllerStatus', 'zwavejs','none');
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
				echo '<div class="cursor changeIncludeState card success">';
				echo '<i class="fas fa-sign-in-alt fa-rotate-90"></i>';
				echo '<br/>';
				echo '<span>{{Inclusion/Exlusion}}</span>';
				echo '</div>';
			?>
			<div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
				<i class="fas fa-wrench"></i>
				<br/>
				<span>{{Configuration}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_syncEqLogic">
				<i class="fas fa-sync-alt"></i>
				<br/>
				<span>{{Synchroniser}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_zwaveNetwork">
				<i class="fas fa-sitemap"></i>
				<br/>
				<span>{{Réseau Zwave}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_zwaveHealth">
				<i class="fas fa-medkit"></i>
				<br/>
				<span>{{Santé}}</span>
			</div>
			<div class="cursor logoSecondary" id="bt_zwaveStats">
				<i class="fas fa-chart-bar"></i>
				<br/>
				<span>{{Statistiques}}</span>
			</div>
		</div>
		<legend><i class="fas fa-broadcast-tower"></i> {{Mes équipements Z-Wave}}</legend>
		<div class="input-group" style="margin:5px;">
			<input class="form-control roundedLeft" placeholder="{{Rechercher}}" id="in_searchEqlogic"/>
			<div class="input-group-btn">
				<a id="bt_resetSearch" class="btn roundedRight" style="width:30px"><i class="fas fa-times"></i></a>
			</div>
		</div>
		<div class="eqLogicThumbnailContainer">
			<?php
			foreach ($eqLogics as $eqLogic) {
				$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
				echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-logical-id="' . $eqLogic->getLogicalId() . '" data-eqLogic_id="' . $eqLogic->getId() . '" title="Node ID : '.$eqLogic->getLogicalId().'">';
				if ($eqLogic->getImgFilePath() !== false) {
					echo '<img class="lazy" src="plugins/zwavejs/core/config/devices/' . $eqLogic->getImgFilePath() . '" />';
				} else {
					echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
				}
				echo '<br/>';
				echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
				echo '</div>';
			}
			?>
		</div>
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
		</ul>
		<div class="tab-content">
			<div role="tabpanel" class="tab-pane active" id="eqlogictab">
				<form class="form-horizontal">
					<fieldset>
						<div class="col-lg-6">
							<legend><i class="fas fa-wrench"></i> {{Général}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;"/>
									<input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Objet parent}}</label>
								<div class="col-sm-7">
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
								<label class="col-sm-3 control-label">{{Catégorie}}</label>
								<div class="col-sm-7">
									<?php
									foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
										echo '<label class="checkbox-inline">';
										echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
										echo '</label>';
									}
									?>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Options}}</label>
								<div class="col-sm-7">
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
									<label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Identifiant du nœud Zwave}}</label>
								<div class="col-sm-7">
									<input type="text" class="eqLogicAttr form-control" data-l1key="logicalId"/>
								</div>
							</div>
						</div>

						<div class="col-lg-6">
							<legend><i class="fas fa-info"></i> {{Informations}}</legend>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Modèle}}</label>
								<div class="col-sm-7">
									<span class="label label-info">
										<span class="eqLogicAttr" data-l1key="configuration" data-l2key="product_name"></span>
									</span>
									<a class="confRecommended btn btn-xs btn-warning" title="{{Configuration recommandée}}" style="display:none"><i class="fas fa-flag"></i></a>
								</div>
								
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Firmware}}</label>
								<div class="col-sm-7">
									<span class="label label-info">
										<span class="eqLogicAttr" data-l1key="configuration" data-l2key="firmwareVersion"></span>
									</span>
								</div>
							</div>
							<div class="form-group confModes" style="display:none">
								<label class="col-sm-3 control-label">{{Mode}} <sup><i class="fas fa-question-circle tooltips" title="Permet de recréer les commandes en fonction du mode de fonctionnement du module désiré. Sauver le mode puis sur la page de commande rechargez vos commandes"></i><sup></label> 
								<div class="col-sm-7">
									<select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="confMode"></select>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label">{{Visuel}}</label>
								<div class="col-sm-7">
									<img src="core/img/no_image.gif" data-original=".jpg" id="img_device" class="img-responsive" style="max-height:120px;"/>
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 control-label"></label>
								<div class="input-group" style="display:inline-flex">
									<span class="input-group-btn">
										<a class="nodeInformations btn btn-primary roundedLeft" title="{{Informations du noeud}}"><i class="fas fa-fingerprint"></i> {{Noeud}}</a>
										<a class="nodeValues btn btn-primary" title="{{Valeurs du noeud}}"><i class="fas fa-list"></i> {{Valeurs}}</a>
										<a class="nodeGroups btn btn-primary roundedRight" title="{{Groupes du noeud}}"><i class="fas fa-layer-group"></i> {{Groupes}}</a>
									</span>
								</div>
							</div>
						</div>
					</fieldset>
				</form>
				<hr>
				<div class="incompleteInfo" style="display:none"><div class="alert alert-warning" role="alert"> {{Le noeud n'a pas encore été initié. Il sera mis à jour automatiquement lorsque l'initialisation sera terminée. Cela prendra quelques secondes. En cas d'inclusion sécurisée ou de module sur piles cela peut être plus long. Pour un noeud sur pile si le message persiste vous pouvez essayer de le réveiller manuellement.}}</div></div>
			</div>

			<div role="tabpanel" class="tab-pane" id="commandtab">
				<div class="input-group pull-right" style="display:inline-flex">
					<span class="input-group-btn">
						<a class="btn btn-success btn-sm cmdAction roundedLeft" data-action="add" style="margin-top:5px;"> <i class="fas fa-plus-circle"></i> {{Commandes}}</a>
						<a id="bt_autoDetectModule" class="btn btn-danger btn-sm roundedRight" style="margin-top:5px;"><i class="fas fa-search"></i> {{Recharger commandes}}</a>
					</span>
				</div>
				<br/><br/>
				<table id="table_cmd" class="table table-bordered table-condensed">
					<thead>
						<tr>
							<th style="width: 300px;">{{Nom}}</th>
							<th style="width: 130px;">{{Type}}</th>
							<th style="width: 65px;">{{Classe}}</th>
							
							<th style="width: 65px;">{{Endpoint}}</th>
							<th style="width: 250px;">{{Propriété}}</th>
							<th>{{Commande}}</th>
							<th style="width: 250px;">{{Paramètres}}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
<?php include_file('core', 'zwavejs', 'class.js', 'zwavejs');?>
<?php include_file('desktop', 'zwavejs', 'js', 'zwavejs');?>
<?php include_file('core', 'plugin.template', 'js');?>