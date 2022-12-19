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
<div id="div_grantSecurity" style="display: none;"></div>
<div class="alert alert-warning">{{Vous ne pouvez que désactiver une classe de sécurité. Dans la majorité des cas, ne modifiez rien sur cette page.}}</div>
<div class="col-sm-4">
</div>
<div class="col-sm-6">
<?php
$classes = init('classes');
$auth = init('auth');
$classesList = explode('|',$classes);
if (in_array(0,$classesList)) {
	echo '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="0" checked>{{S2 Unauthenticated}}</label></br>';
} else {
	echo '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="0" disabled>{{S2 Unauthenticated}}</label></br>';
}
if (in_array(1,$classesList)) {
	echo '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="1" checked>{{S2 Authenticated}}</label></br>';
} else {
	echo '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="1" disabled>{{S2 Authenticated}}</label></br>';
}
if (in_array(2,$classesList)) {
	echo '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="2" checked>{{S2 Access Control}}</label></br>';
} else {
	echo '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="2" disabled>{{S2 Access Control}}</label></br>';
}
if (in_array(7,$classesList)) {
	echo '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="classes" data-key="7" checked>{{S0 Legacy}}</label></br>';
} else {
	echo '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="classes" data-key="7" disabled>{{S0 Legacy}}</label></br>';
}

if ($auth != 'false') {
	echo '<label class="checkbox-inline" style="color:green"><input type="checkbox" class="clientauth" checked>{{Client Authentification}}</label>';
} else {
	echo '<label class="checkbox-inline" style="color:red"><input type="checkbox" class="clientauth" disabled>{{Client Authentification}}</label>';
}
?>
<br><br>
<div class="input-group">
<a class="btn btn-danger roundedLeft cancelS2"><i class="fas fa-save"></i> {{Annuler l'inclusion S2}}</a>
<a class="btn btn-success roundedRight confirmS2"><i class="fas fa-save"></i> {{Valider}}</a>
</div>
</div>
<?php include_file('core', 'zwavejs', 'class.js', 'zwavejs');
include_file('desktop', 'grantSecurity', 'js', 'zwavejs'); ?>
