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
?>
<div class="modalStatsValues">
<div id="div_networkStatAlert" style="display: none;"></div>
<table class="table table-condensed table-bordered tablesorter tablesorter-bootstrap table-striped hasFilters tableStat" id="table_Stat">
	<thead>
		<tr>
			<th>{{Id}}</th>
			<th>{{Equipement}}</th>
			<th>{{RX}}</th>
			<th>{{TX}}</th>
			<th>{{Timeout}}</th>
			<th>{{RTT}}</th>
		</tr>
	</thead>
	<tbody>
		<?php
			$eqLogics = eqLogic::byType('zwavejs');
			foreach ($eqLogics as $eqLogic){
				$image = 'plugins/zwavejs/core/config/devices/'.$eqLogic->getImgFilePath();
				if (!is_file(dirname(__FILE__) . '/../../core/config/devices/'.$eqLogic->getImgFilePath())){
					$image = 'plugins/zwavejs/plugin_info/zwavejs_icon.png';
				}
				$nameTd = '<td><img src="'.$image.'" height="40"/> <a href="index.php?v=d&p=zwavejs&m=zwavejs&id=' . $eqLogic->getId() . '">' . $eqLogic->getHumanName(true).  '</a></td>';
				$healthPage .= '<td><span class="label label-info" style="font-size : 1em;">'.$values['endpointsCount'].'</span></td>';
				$nodeId =$eqLogic->getLogicalId();
				echo '<tr>';
				echo '<td>'.$nodeId.'</td>';
				echo $nameTd;
				echo '<td><span class="label label-info rx'.$nodeId.'" style="font-size : 1em;">0</span></td>';
				echo '<td><span class="label label-info tx'.$nodeId.'" style="font-size : 1em;">0</span></td>';
				echo '<td><span class="label label-info timeout'.$nodeId.' style="font-size : 1em;">0</span></td>';
				echo '<td><span class="label label-info rtt'.$nodeId.' style="font-size : 1em;">-</span></td>';
				echo '</tr>';
			}
		?>
	</tbody>
</table>
</div>
<?php include_file('core', 'zwavejs', 'class.js', 'zwavejs');?>
<?php include_file('desktop', 'stats', 'js', 'zwavejs');?>
