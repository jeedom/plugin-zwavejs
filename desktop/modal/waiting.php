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
<div id="div_waitingAlert" style="display: none;"></div>
<div class="alert alert-warning noparam" style="display: none;">{{Aucun paramètre en attente}}</div>
<div class="modalWaiting">
	<table class="table table-condensed tableWaiting" id="table_waiting">
		<thead>
			<tr>
				<th>{{Id}}</th>
				<th style="min-width:330px;">{{Equipement}}</th>
				<th>{{Paramètres}}</th>
				<th>{{Valeurs}}</th>
				<th>{{Date}}</th>
				<th>{{}}</th>
			</tr>
		</thead>
		<tbody>

		</tbody>
	</table>
</div>
<?php include_file('core', 'zwavejs', 'class.js', 'zwavejs');
include_file('desktop', 'waiting', 'js', 'zwavejs'); ?>
