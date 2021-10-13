<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class zwavejs extends eqLogic {
   /*     * *************************Attributs****************************** */



   /*     * ***********************Methode static*************************** */

   public static function callZwavejs($_url) {
      if (strpos($_url, '?') !== false) {
         $url = 'http://127.0.0.1:' . config::byKey('socketport', 'zwavejs') . '/' . trim($_url, '/') . '&apikey=' . jeedom::getApiKey('zwavejs');
      } else {
         $url = 'http://127.0.0.1:' . config::byKey('socketport', 'zwavejs') . '/' . trim($_url, '/') . '?apikey=' . jeedom::getApiKey('zwavejs');
      }
      $ch = curl_init();
      curl_setopt_array($ch, array(
         CURLOPT_URL => $url,
         CURLOPT_HEADER => false,
         CURLOPT_RETURNTRANSFER => true,
      ));
      $result = curl_exec($ch);
      if (curl_errno($ch)) {
         $curl_error = curl_error($ch);
         curl_close($ch);
         throw new Exception(__('Echec de la requête http : ', __FILE__) . $url . ' Curl error : ' . $curl_error, 404);
      }
      curl_close($ch);
      return is_json($result, $result);
   }

   public static function deamon_info() {
      $return = array();
      $return['log'] = 'zwavejs';
      $return['state'] = 'nok';
      $pid_file = jeedom::getTmpFolder('zwavejs') . '/deamon.pid';
      if (file_exists($pid_file)) {
         if (@posix_getsid(trim(file_get_contents($pid_file)))) {
            $return['state'] = 'ok';
         } else {
            shell_exec(system::getCmdSudo() . 'rm -rf ' . $pid_file . ' 2>&1 > /dev/null');
         }
      }
      $return['launchable'] = 'ok';
      return $return;
   }

   public static function deamon_start() {
      log::remove(__CLASS__ . '_update');
      self::deamon_stop();
      $deamon_info = self::deamon_info();
      if ($deamon_info['launchable'] != 'ok') {
         throw new Exception(__('Veuillez vérifier la configuration', __FILE__));
      }
      $zwavejs_path = realpath(dirname(__FILE__) . '/../../resources/zwavejsd');
      chdir($zwavejs_path);
      $cmd = 'sudo /usr/bin/node ' . $zwavejs_path . '/zwavejs.js';
      $cmd .= ' --loglevel ' . log::convertLogLevel(log::getLogLevel('zwavejs'));
      $cmd .= ' --socketport ' . config::byKey('socketport', 'zwavejs');
      $cmd .= ' --callback ' . network::getNetworkAccess('internal', 'proto:127.0.0.1:port:comp') . '/plugins/zwavejs/core/php/jeeZwavejs.php';
      $cmd .= ' --apikey ' . jeedom::getApiKey('zwavejs');
      $cmd .= ' --cycle ' . config::byKey('cycle', 'zwavejs');
      $cmd .= ' --port ' . config::byKey('port', 'zwavejs');
      $cmd .= ' --pid ' . jeedom::getTmpFolder('zwavejs') . '/deamon.pid';
      log::add('zwavejs', 'info', 'Lancement démon zwavejs : ' . $cmd);
      $result = exec($cmd . ' >> ' . log::getPathToLog('zwavejs') . ' 2>&1 &');
      $i = 0;
      while ($i < 30) {
         $deamon_info = self::deamon_info();
         if ($deamon_info['state'] == 'ok') {
            break;
         }
         sleep(1);
         $i++;
      }
      if ($i >= 30) {
         log::add('zwavejs', 'error', 'Impossible de lancer le démon zwavejsd, vérifiez le log', 'unableStartDeamon');
         return false;
      }
      message::removeAll('zwavejs', 'unableStartDeamon');
      return true;
   }


   public static function deamon_stop() {
      $pid_file = jeedom::getTmpFolder('zwavejs') . '/deamon.pid';
      if (file_exists($pid_file)) {
         $pid = intval(trim(file_get_contents($pid_file)));
         system::kill($pid);
      }
      system::kill('zwavejsd.js');
      system::fuserk(config::byKey('socketport', 'zwavejs'));
   }


   /*     * *********************Méthodes d'instance************************* */


   /*     * **********************Getteur Setteur*************************** */
}

class zwavejsCmd extends cmd {
   /*     * *************************Attributs****************************** */

   /*     * ***********************Methode static*************************** */


   /*     * *********************Methode d'instance************************* */


   public function execute($_options = array()) {
   }

   /*     * **********************Getteur Setteur*************************** */
}
