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

?>


<div id="div_nodeGroupsZwaveJsAlert" style="display: none;"></div>
<div id="div_nodeAddGroupsZwaveJsAlert" style="display: none;"></div>

<div class="modalNodeGroups">
<div id="div_StatusGroupAlert"></div>
<div class="col-sm-6">
<div class="panel panel-primary template">
	<div class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-layer-group"></i> {{Listes des groupes disponibles}}</h4>
	</div>
	<div class="panel-body listGroups">
		<table class="table table-striped tableGroups">
		<thead>
			<tr>
				<th>{{Groupe}}</th>
				<th>{{Endpoint}}</th>
				<th>{{Lifeline}}</th>
				<th>{{Max}}</th>
			</tr>
		</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
</div>
<div class="col-sm-6">
<div class="panel panel-primary template">
	<div class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-project-diagram"></i> {{Associations actives}}<a class="btn btn-sm btn-danger removeAllAssociations pull-right" style="margin-top:-1.2em"><i class="fas fa-trash"></i></a></h4>
	</div>
	<div class="panel-body associations">
		<table class="table table-striped tableAssociations">
		<thead>
			<tr>
				<th>{{Endpoint}}</th>
				<th>{{Groupe}}</th>
				<th>{{Node}}</th>
				<th>{{Endpoint Cible}}</th>
				<th>{{Actions}}</th>
			</tr>
		</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<div class="panel panel-primary template">
	<div class="panel-heading">
		<h4 class="panel-title"><i class="fas fa-plus-circle"></i> {{Ajouter une association}}<a class="btn btn-sm btn-success addAssociation pull-right" style="margin-top:-1.2em"><i class="fas fa-plus-circle"></i></a></h4>
	</div>
	<div class="panel-body addAssociations">
		<div class="form-group">
			<label class="col-sm-3 control-label">{{Groupe}}</label>
			<div class="col-sm-7">
				<select class="selectGroup form-control">
					<option value="">{{Aucun}}</option>
				</select>
			</div>
		</div>
		<br>
		<br>
		<div class="form-group">
			<label class="col-sm-3 control-label">{{Noeud Cible}}</label>
			<div class="col-sm-7">
				<select class="selectTargetNode form-control">
					<option value="">{{Aucun}}</option>
				</select>
			</div>
		</div>
	</div>
	<br>
</div>
</div>
</div>
<?php include_file('desktop', 'nodeGroups', 'js', 'zwavejs');?>