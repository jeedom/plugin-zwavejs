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
<div id="div_validateDSK" style="display: none;"></div>
<div class="col-sm-4">
</div>
<div class="form-group">
<div class="col-sm-2">
<input type="text" class="dsk form-control">
</div>
<?php
echo '<label class="col-sm-6 control-label">'.init('DSK').'</label>';
?>
<br><br>
<div class="input-group">
<a class="btn btn-danger roundedLeft cancelS2"><i class="fas fa-save"></i> {{Annuler l'inclusion S2}}</a>
<a class="btn btn-success roundedRight confirmDSK"><i class="fas fa-save"></i> {{Valider}}</a>
</div>
</div>
<?php include_file('core', 'zwavejs', 'class.js', 'zwavejs');
include_file('desktop', 'validateDSK', 'js', 'zwavejs'); ?>
