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

sendVarToJs('battery_warning', config::byKey('battery::warning'));
sendVarToJs('battery_danger', config::byKey('battery::danger'));
?>
<div id="div_networkHealthAlert" style="display: none;"></div>
<div class="modalHealthValues">
	<table class="table table-condensed tableHealth" id="table_healthNetwork">
		<thead>
			<tr>
				<th>{{Id}}</th>
				<th>{{Equipement}}</th>
				<th>{{Endpoints}}</th>
				<th>{{Sécurité}}</th>
				<th>{{Flirs}}</th>
				<th>{{Z-Wave+}}</th>
				<th>{{Routing}}</th>
				<th>{{Polling}}</th>
				<th>{{Refresh}}</th>
				<th>{{Initié}}</th>
				<th>{{Statut}}</th>
				<th>{{Interview}}</th>
				<th>{{Dernière activité}}</th>
				<th>{{Ping}}</th>
			</tr>
		</thead>
		<tbody>

		</tbody>
	</table>
</div>
<?php include_file('core', 'zwavejs', 'class.js', 'zwavejs');
include_file('desktop', 'health', 'js', 'zwavejs'); ?>
