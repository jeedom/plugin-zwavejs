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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';
include_file('core', 'authentification', 'php');
if (!isConnect('admin')) {
	throw new Exception('{{401 - Accès non autorisé}}');
}
?>
<form class="form-horizontal">
	<fieldset>
		<div class="col-lg-6">
			<?php if (class_exists('jMQTT')) {
				echo '<div class="alert alert-warning">{{Le plugin jMQTT est installé, veuillez vérifier la configuration du broker dans le plugin jMQTT et la reporter, si nécessaire, dans le plugin MQTT Manager.}}</div>';
			}
			?>
			<div class="form-group">
				<label class="col-lg-4 control-label">{{Mode ZWave}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Mode du deamon ZWaveJS. défaut: local}}"></i></sup>
				</label>
				<div class="col-md-7">
					<select class="configKey form-control" data-l1key="zwavejs_mode">
						<option value="local">{{local (défaut)}}</option>
						<option value="remote">{{distant}}</option>
					</select>
				</div>
			</div>
			<p/>
			<div class="form-group zwaveMode remote">
				<label class="col-lg-4 control-label">{{IP ZWaveJS}}
					 <sup><i class="fas fa-question-circle tooltips" title="{{Adresse IP du serveur ZWaveJS}}"></i></sup>
				</label>
				<div class="col-md-7">
					 <input class="configKey form-control" data-l1key="zwavejs_adminip" />
				</div>
			</div>
			<div class="form-group zwaveMode remote">
				<label class="col-lg-4 control-label">{{Port ZWaveJS}}
					 <sup><i class="fas fa-question-circle tooltips" title="{{Port d'administration du serveur ZWaveJS, défaut 8091}}"></i></sup>
				</label>
				<div class="col-md-7">
					<input class="configKey form-control" data-l1key="zwavejs_adminport" />
				</div>
			</div>


			<div class="form-group zwaveMode local">
				<label class="col-md-4 control-label">{{Port du contrôleur Z-Wave}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Renseigner le port utilisé par le contrôleur Z-Wave}}"></i></sup>
				</label>
				<div class="col-md-7">
					<select class="configKey form-control" data-l1key="port">
						<option value="none">{{Aucun}}</option>
						<?php
						foreach (jeedom::getUsbMapping('', true) as $name => $value) {
							echo '<option value="' . $name . '">' . $name . ' (' . $value . ')</option>';
						}
						foreach (ls('/dev/', 'tty*') as $value) {
							echo '<option value="/dev/' . $value . '">/dev/' . $value . '</option>';
						}
						?>
						<option value="/dev/serial/by-id/usb-0658_0200-if00">{{Utile pour certains Raspberry (/dev/serial/by-id/usb-0658_0200-if00)}}</option>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Préfixe MQTT}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Préfixe à utiliser dans MQTT. Défaut: zwave}}"></i></sup>
				</label>
				<div class="col-md-7">
					<input type="text" class="configKey form-control" data-l1key="prefix" placeholder="{{}}">
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Configuration recommandée}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour appliquer le jeu de configuration spécialement recommandé par l'équipe Jeedom lors de l'inclusion d'un nouveau module}}"></i></sup>
				</label>
				<div class="col-md-1">
					<input type="checkbox" class="configKey" data-l1key="auto_applyRecommended" checked>
				</div>
				<label class="col-md-5 control-label">{{Supprimer périphériques exclus}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour supprimer automatiquement les équipements Jeedom correspondant à des périphériques exclus du contrôleur}}"></i></sup>
				</label>
				<div class="col-md-1">
					<input type="checkbox" class="configKey" data-l1key="autoRemoveExcludeDevice">
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Alertes de noeuds morts}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour être notifié dans le centre de message Jeedom des noeuds morts et du retour à l'état Alive}}"></i></sup>
 				</label>
				<div class="col-md-1">
					<input type="checkbox" class="configKey" data-l1key="notifyDead" checked>
				</div>
				<label class="col-md-5 control-label">{{Alertes de réveils manqués}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Cocher la case pour être notifié dans le centre de message Jeedom des réveils manqués et du retour à la normal}}"></i></sup>
				</label>
				<div class="col-md-1">
					<input type="checkbox" class="configKey" data-l1key="notifyMissWakeup" checked>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Soft Reset}}
					<sup><i class="fas fa-question-circle tooltips" title="{{Ne pas toucher si vous ne savez pas ce qu'est cette option}}"></i></sup>
				</label>
				<div class="col-md-1">
					<input type="checkbox" class="configKey" data-l1key="softReset" checked>
				</div>
			</div>
                        <div class="form-group zwaveMode local">
                                <label class="col-md-4 control-label tooltips">{{Setup}}
                                        <sup><i class="fas fa-question-circle tooltips" title="{{Setup du deamon local}}"></i></sup>
                                </label>
                                <div class="col-md-6">
                                        <a class="btn btn-xs btn-warning" id="bt_installZWaveJS"><i class="fas fa-plus-square"></i> {{Installer ZWaveJS}}</a>
                                        <a class="btn btn-xs btn-danger" id="bt_uninstallZWaveJS"><i class="fas fa-minus-square"></i> {{Désinstaller ZWaveJS}}</a>
					<a class="btn btn-xs btn-btn-primary" id="bt_refreshZWaveJS"><i class="fas fa-sync"></i></a>
                                </div>
                                <?php
					$mode = config::byKey('zwavejs_mode', 'zwavejs', '');
					if (($mode == 'local') && (config::byKey('zwavejsVersion', 'zwavejs') == 'N/A'))
						echo '<span class="label label-danger">NOK</span>';
					else
						echo '<span class="label label-success">OK</span>';
                                ?>
                        </div>
                        <div class="form-group zwaveMode remote">
                                <label class="col-md-4 control-label tooltips" style="position:relative;top:-5px;">{{Communication ZWaveJS}}
                                        <sup><i class="fas fa-question-circle tooltips" title="{{Communication avec service ZWaveJS }}"></i></sup>
                                </label>
                                <div class="col-md-8">
                                   <?php
					$mode = config::byKey('zwavejs_mode', 'zwavejs', '');
					if ($mode == 'remote') {
						if (!zwavejs::checkZWaveJSSvc())
							echo '<span class="col-sm-1 label label-danger">NOK</span>';
						else
							echo '<span class="col-sm-1 label label-success">OK</span>';
					}
                                   ?>
				   <span class="col-sm-1"></span>
                                   <a class="btn btn-xs btn-primary" id="bt_testZWave" style="position:relative;top:-5px;">
                                        <i class="fas fa-sync"></i> {{Tester}}</a>
                                </div>
                        </div>

                        <div class="form-group">
                                <label class="col-md-4 control-label">{{Version ZwaveJS}}
                                        <sup><i class="fas fa-question-circle tooltips" title="{{Version de la librairie ZwaveJS. Si mode distant: mis à jour après le démarrage du deamon}}"></i></sup>
                                </label>
                                <div class="col-md-7">
                                <?php
                                $localVersion =config::byKey('zwavejsVersion', 'zwavejs', 'N/A');
				if (empty($localVersion))
					config::save('zwavejsVersion', 'N/A','zwavejs');
                                $wantedVersion = config::byKey('wantedVersion', 'zwavejs', '');

                                if ($localVersion != $wantedVersion) {
                                        echo '<span class="label label-warning">' . $localVersion . '</span><br>';
                                        echo "<div class='alert alert-danger text-center'>{{ ZwaveJS UI n'est pas détecté ou votre version n'est pas celle recommandée par le plugin.
                                                Vous utilisez actuellement la version }}<code>". $localVersion .'</code>. {{ Le plugin nécessite la version }}<code>' . $wantedVersion .'</code>.
                                              </div>';
                                } else
                                        echo '<span class="label label-success">' . $localVersion . '</span><br>';
                                ?>
                                </div>
                        </div>
			<br>
		</div>
		<div class="col-lg-6">
			<div class="alert alert-info text-center">{{Les clés de sécurités sont à conserver précieusement. Si vous perdez vos clés les périphériques inclus en sécurisés devront être réappairés. Les clés peuvent être spécifiées, si les champs sont vides ou invalides le plugin en générera et vous pourrez les voir ensuite.}}
				<br>{{Si votre contrôleur a été utilisé avec le plugin Openzwave et que vous aviez inclus des modules en sécurisés la clé S0 est}} :
				<code>0102030405060708090A0B0C0D0E0F10</code>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Clé de Sécurité S0}}</label>
				<div class="input-group col-md-7">
					<input class="configKey roundedLeft form-control" data-l1key="s0key" placeholder="{{Clé de sécurité S0}}">
					<span class="input-group-btn">
						<a class="btn btn-default form-control randomKey roundedRight" data-key="s0key"><i class="fas fa-sync-alt"></i></a>
					</span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Clé de Sécurité S2 Authenticated}}</label>
				<div class="input-group col-md-7">
					<input class="configKey roundedLeft form-control" data-l1key="s2key_auth" placeholder="{{Clé de sécurité S2 Authenticated}}">
					<span class="input-group-btn">
						<a class="btn btn-default form-control randomKey roundedRight" data-key="s2key_auth"><i class="fas fa-sync-alt"></i></a>
					</span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Clé de Sécurité S2 Access Control}}</label>
				<div class="input-group col-md-7">
					<input class="configKey roundedLeft form-control" data-l1key="s2key_access" placeholder="{{Clé de sécurité S2 Access Control}}">
					<span class="input-group-btn">
						<a class="btn btn-default form-control randomKey roundedRight" data-key="s2key_access"><i class="fas fa-sync-alt"></i></a>
					</span>
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 control-label">{{Clé de Sécurité S2 Unauthenticated}}</label>
				<div class="input-group col-md-7">
					<input class="configKey roundedLeft form-control" data-l1key="s2key_unauth" placeholder="{{Clé de sécurité S2 Unauthenticated}}">
					<span class="input-group-btn">
						<a class="btn btn-default form-control randomKey roundedRight" data-key="s2key_unauth"><i class="fas fa-sync-alt"></i></a>
					</span>
				</div>
			</div>
                        <div class="form-group">
                                <label class="col-md-4 control-label">{{Clé de Sécurité S2 Authenticated Long Range}}</label>
                                <div class="input-group col-md-7">
                                        <input class="configKey roundedLeft form-control" data-l1key="s2key_auth_long" placeholder="{{Clé de sécurité S2 Authenticated Long Range}}">
                                        <span class="input-group-btn">
                                                <a class="btn btn-default form-control randomKey roundedRight" data-key="s2key_auth_long"><i class="fas fa-sync-alt"></i></a>
                                        </span>
                                </div>
                        </div>
                        <div class="form-group">
                                <label class="col-md-4 control-label">{{Clé de Sécurité S2 Access Control Long Range}}</label>
                                <div class="input-group col-md-7">
                                        <input class="configKey roundedLeft form-control" data-l1key="s2key_access_long" placeholder="{{Clé de Sécurité S2 Access Control Long Range}}">
                                        <span class="input-group-btn">
                                                <a class="btn btn-default form-control randomKey roundedRight" data-key="s2key_access_long"><i class="fas fa-sync-alt"></i></a>
                                        </span>
                                </div>
                        </div>
		</div>
	</fieldset>
</form>

<script>
	$('.configKey[data-l1key=zwavejs_mode]').off('change').on('change', function() {
		$('.zwaveMode').hide()
		if ($(this).value() == 'local')
			$('.local').show()
		else
			$('.remote').show()
	})

        $('#bt_installZWaveJS').off('click').on('click', function() {
                $.ajax({ type: "POST", url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
                        data: {
                                action: "installZWaveJS"
                        },
                        dataType: 'json',
                        error: function(error) {
                                $.fn.showAlert({ message: error.message, level: 'danger'
                                })
                        },
                        success: function(data) {
                                if (data.state != 'ok') {
                                        $.fn.showAlert({ message: data.result, level: 'danger'
                                        })
                                        return
                                } else {
                                        $('.pluginDisplayCard[data-plugin_id=' + $('#span_plugin_id').text() + ']').click()
                                }
                        }
                })
        })

        $('#bt_uninstallZWaveJS').off('click').on('click', function() {
                $.ajax({ type: "POST", url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
                        data: {
                                action: "uninstallZWaveJS"
                        },
                        dataType: 'json',
                        error: function(error) {
                                $.fn.showAlert({ message: error.message, level: 'danger'
                                })
                        },
                        success: function(data) {
                                if (data.state != 'ok') {
                                        $.fn.showAlert({ message: data.result, level: 'danger'
                                        })
                                        return
                                } else {
                                        $('.pluginDisplayCard[data-plugin_id=' + $('#span_plugin_id').text() + ']').click()
                                }
                        }
                })
        })

	$('#bt_refreshZWaveJS').off('click').on('click', function() {
                $.ajax({ type: "POST", url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
                        data: {
                                action: "checkZWaveJSVersion"
                        },
                        dataType: 'json',
                        error: function(error) {
                                $.fn.showAlert({ message: error.message, level: 'danger'
                                })
                        },
                        success: function(data) {
                                if (data.state != 'ok') {
                                        $.fn.showAlert({ message: data.result, level: 'danger'
                                        })
                                        return
                                } else {
                                        $('.pluginDisplayCard[data-plugin_id=' + $('#span_plugin_id').text() + ']').click()
                                }
                        }
                })
        })

        $('#bt_testZWave').off('click').on('click', function() {
                $.ajax({ type: "POST", url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
                        data: {
                                action: "checkZWaveJSSvc"
                        },
                        dataType: 'json',
                        error: function(error) {
                                $.fn.showAlert({ message: error.message, level: 'danger'
                                })
                        },
                        success: function(data) {
                                if (data.state != 'ok') {
                                        $.fn.showAlert({ message: data.result, level: 'danger'
                                        })
                                        return
                                } else {
                                        $('.pluginDisplayCard[data-plugin_id=' + $('#span_plugin_id').text() + ']').click()
                                }
                        }
                })
        })

	$('.randomKey').off('click').on('click', function() {
		var el = $(this)
		bootbox.confirm('{{Êtes-vous sûr de vouloir réinitialiser la clé}}' + ' ' + el.attr('data-key') + ' ? {{La prise en compte sera effective après sauvegarde et relance du démon.}}', function(result) {
			if (result) {
				$.ajax({
					type: "POST",
					url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
					data: {
						action: "generateRandomKey"
					},
					dataType: 'json',
					error: function(request, status, error) {
						handleAjaxError(request, status, error)
					},
					success: function(data) {
						el.closest('.input-group').find('.configKey').value(data.result)
					}
				})
			}
		})
	})

	$('body').off('zwavejs::install_terminated').on('zwavejs::install_terminated', function(_event, _options) {
                $.ajax({ type: "POST", url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
                        data: {
                                action: "checkZWaveJSVersion"
                        },
                        dataType: 'json',
                        error: function(error) {
                                $.fn.showAlert({ message: error.message, level: 'danger'
                                })
                        },
                        success: function(data) {
                                if (data.state != 'ok') {
                                        $.fn.showAlert({ message: data.result, level: 'danger'
                                        })
                                        return
                                } else {
                                        $('.pluginDisplayCard[data-plugin_id=' + $('#span_plugin_id').text() + ']').click()
                                }
                        }
                })
        })

        $('body').off('zwavejs::version_updated').on('zwavejs::version_updated', function(_event, _options) {
                $.ajax({ type: "POST", url: "plugins/zwavejs/core/ajax/zwavejs.ajax.php",
                        data: {
                                action: "checkZWaveJSSvc"
                        },
                        dataType: 'json',
                        error: function(error) {
                                $.fn.showAlert({ message: error.message, level: 'danger'
                                })
                        },
                        success: function(data) {
                                if (data.state != 'ok') {
                                        $.fn.showAlert({ message: data.result, level: 'danger'
                                        })
                                        return
                                } else {
                                        $('.pluginDisplayCard[data-plugin_id=' + $('#span_plugin_id').text() + ']').click()
                                }
                        }
                })
        })



$('body').off('zwavejs::dependancy_end').on('zwavejs::dependancy_end', function(_event, _options) {
  window.location.reload()
})
</script>
