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
$('#table_waiting').off().on('click', '.removeHist', function() {
  jeedom.zwavejs.waiting.remove({
    logical: $(this).attr('data-id'),
    property: $(this).attr('data-property'),
    error: function(error) {
      $('#div_waitingAlert').showAlert({ message: error.message, level: 'danger' })
    },
    success: function(data) {
      $('#div_waitingAlert').showAlert({ message: '{{Action réalisée avec succès}}', level: 'success' })
    }
  })
})

function getWaiting() {
  jeedom.zwavejs.waiting.get({
    global: false,
    error: function(error) {
      $('#div_waitingAlert').showAlert({ message: error.message, level: 'danger' })
      if ($('.modalWaiting').is(":visible")) {
        get_waiting = setTimeout(function() { getWaiting() }, 2000)
      }
    },
    success: function(waiting) {
        if (waiting.length>0){
          tr = '';
          for (var i in waiting) {
            tr += '<tr data-id="' + waiting[i].id + '">';
            tr += '<td style="font-size:0.8em !important;">';
            tr += '<span class="label label-info" style="font-size : 1em;">' + waiting[i].id + '</span>';
            tr += '</td>';
            tr += '<td>';
            tr += '<img src="' + waiting[i].image + '" height="40"/> <a href="index.php?v=d&p=zwavejs&m=zwavejs&id=' + waiting[i].eqId + '">' + waiting[i].name + '</a>';
            tr += '</td>';
            tr += '<td>';
            tr += '<span class="label label-info" style="font-size : 1em;">' + waiting[i].property + '</span>';
            tr += '</td>';
            tr += '<td>';
            tr += '<span class="label label-info" style="font-size : 1em;">' + waiting[i].value + '</span>';
            tr += '</td>';
            tr += '<td>';
            tr += '<span class="label label-info" style="font-size : 1em;">' + waiting[i].date + '</span>';
            tr += '</td>';
            tr += '<td>';
            tr += '<td><a class="btn btn-danger btn-xs removeHist" data-id="' + waiting[i].id + '" data-property="' + waiting[i].property + '"><i class="fas fa-eraser"></i> Supprimer</a><sup><i class="fas fa-question-circle tooltips" title="{{Cela supprimera l\'élément de la liste d\'attente Jeedom mais le paramètre sera toujours envoyé. Utile pour des paramètres qui ne retournent pas la même valeur que celle envoyée (calibration par exemple)}}"></i></sup></td>';
            tr += '</td>';
            tr += '</tr>';
          }
            $('.noparam').hide();
            $('.tableWaiting tbody').empty().append(tr)
        } else {
            $('.tableWaiting tbody').empty()
            $('.noparam').show();
        }
        if ($('.modalWaiting').is(":visible")) {
          get_waiting = setTimeout(function() { getWaiting() }, 2000)
        }
    }
  })
}

$('#md_modal').bind('dialogclose', function(event, ui) {
	clearTimeout(get_waiting)
})


getWaiting();