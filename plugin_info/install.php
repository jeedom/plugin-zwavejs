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

function zwavejs_install() {
  $plugin = plugin::byId('zwavejs');
  if (config::byKey('zwavejs::mode', 'zwavejs', 'local') == 'local') {
    $plugin->dependancy_changeAutoMode(1);
    $plugin->deamon_info(1);
  } else {
    $plugin->dependancy_changeAutoMode(0);
    $plugin->deamon_info(0);
  }
}

function zwavejs_update() {
  $plugin = plugin::byId('zwavejs');
  if (config::byKey('zwavejs::mode', 'zwavejs', 'local') == 'local') {
    $plugin->dependancy_changeAutoMode(1);
    $plugin->deamon_info(1);
  } else {
    $plugin->dependancy_changeAutoMode(0);
    $plugin->deamon_info(0);
  }
}

function zwavejs_remove() {

}

?>
