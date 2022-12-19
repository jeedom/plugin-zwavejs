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

$('.confirmS2').on('click', function() {
	var list = [];
	var auth = false;
	if ($('.clientauth').is(':checked')){
		auth = true;
	}
	$('.classes').each(function () {
		if (this.checked) {
			list.push($(this).data('key'));
		}
	});
	jeedom.zwavejs.controller.grantSecurity({
           security: list,
           auth: auth,
           error: function(error) {
             $.fn.showAlert({ message: error.message, level: 'danger' })
           },
           success: function(data) {
             $.fn.showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
             $('#md_modal').dialog('close');
           }
         })
})

$('.cancelS2').on('click', function() {
	jeedom.zwavejs.controller.abortInclusion({
           error: function(error) {
             $.fn.showAlert({ message: error.message, level: 'danger' })
           },
           success: function(data) {
             $.fn.showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
             $('#md_modal').dialog('close');
           }
         })
})