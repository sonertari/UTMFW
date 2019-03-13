<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * Main configuration file included by all configuration pages.
 */

/**
 * Displays an NVP configuration form, usually a textbox and buttons.
 *
 * This function is used in all configuration pages to modify module parameters.
 * Params can be enabled/disabled, and values can be changed.
 *
 * @param array $values All of the NVPs in $Config struct at once
 */
function PrintNVPForm($values)
{
	global $View, $Row;

	foreach ($values as $name => $valueconf) {
		// Model config may be different from View, View displays only this many config
		if (isset($View->Config[$name])) {
			$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
			?>
			<tr class="<?php echo $class ?>">
				<td class="title">
					<?php
					if (isset($View->Config[$name]['title'])) {
						$title= $View->Config[$name]['title'];
					}
					else {
						$title= $name;
					}
					echo $title.':';
					?>
				</td>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<?php
						/// @todo Is there a way to get browser's current font width to use here?
						define('CHAR_WIDTH', '5.75');

						$found= preg_match("|^$name=(.*)$|", $valueconf['Value'], $match);
						if ($found) {
							// There are values like this: /usr/local/bin/send_sms 123456789 "VIRUS ALERT: %v"
							$value= htmlentities($match[1], ENT_QUOTES);
							
							$inputType= 'text';
							if (in_array($valueconf['Type'], array(STR_on_off, STR_On_Off, STR_yes_no))) {
								if (in_array($value, array('on', 'off'))) {
									$inputType= 'select';
									$onValue= 'on';
									$offValue= 'off';
								} elseif (in_array($value, array('yes', 'no'))) {
									$inputType= 'select';
									$onValue= 'yes';
									$offValue= 'no';
								} elseif (in_array($value, array('On', 'Off'))) {
									$inputType= 'select';
									$onValue= 'On';
									$offValue= 'Off';
								}
							}
							
							$width= strlen($value)*CHAR_WIDTH;
							if ($width < 50) {
								$width= 50;
							} elseif ($width > 300) {
								$width= 300;
							}

							$disabled= $valueconf['Enabled'] === FALSE ? 'disabled':'';

							if ($inputType == 'text') {
								?>
								<input type="text" <?php echo $disabled ?> name="ValueToChange" style="width: <?php echo $width ?>px;" maxlength="100" value="<?php echo $value ?>"/>
								<?php
							} else {
								?>
								<select <?php echo $disabled ?> name="ValueToChange">
									<option <?php echo $value == $onValue ? 'selected':''; ?> value="<?php echo $onValue ?>"><?php echo $onValue ?></option>
									<option <?php echo $value == $offValue ? 'selected':''; ?> value="<?php echo $offValue ?>"><?php echo $offValue ?></option>
								</select>
								<?php
							}
						}
						
						if ($valueconf['Enabled'] === TRUE) {
							if ($found) {
								?>
								<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
								<?php
							}
							?>
							<input class="disablebutton" type="submit" name="Disable" value="<?php echo _CONTROL('Disable') ?>"/>
							<?php
						}
						else if ($valueconf['Enabled'] === FALSE) {
							?>
							<input class="enablebutton" type="submit" name="Enable" value="<?php echo _CONTROL('Enable') ?>"/>
							<?php
						}
						?>
						<input type="hidden" name="KeyToChange" value="<?php echo $name ?>" />
					</form>
				</td>
				<td class="none">
					<?php
					if (isset($View->Config[$name]['info'])) {
						PrintHelpBox($View->Config[$name]['info']);
					}
					?>
				</td>
			</tr>
			<?php
		}
	}
}

function PrintReloadConfigForm()
{
	global $ReloadConfig, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Apply configuration').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Reload" value="<?php echo _CONTROL('Reload') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2('You can apply configuration changes without restarting currently running process.'));
			?>
		</td>
	</tr>
	<?php
}

// Reset to 0 for modules other than E2guardian and Snort, otherwise Controller complains about arg type of Group
$Group= $_SESSION[$View->Model]['ConfOpt'] ? $_SESSION[$View->Model]['ConfOpt'] : 0;

if (count($_POST)) {
	if (filter_has_var(INPUT_POST, 'Apply')) {
		/// Need to remove \'s for RE match; POST method escapes 's and "s
		$Value= preg_replace("/\\\\'/", "'", filter_input(INPUT_POST, 'ValueToChange'));
		$Value= preg_replace('/\\\\"/', '"', $Value);

		if ($View->Controller($Output, 'SetConfValue', filter_input(INPUT_POST, 'KeyToChange'), $Value, $ViewConfigName, $Group)) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration changed: '.filter_input(INPUT_POST, 'ConfFile').': '.filter_input(INPUT_POST, 'KeyToChange').' = '.filter_input(INPUT_POST, 'ValueToChange'));
		}
	}
	else if (filter_has_var(INPUT_POST, 'Disable')) {
		/// @warning PHP (?) escapes backslashes, remove first
		if ($View->Controller($Output, 'DisableConf', RemoveBackSlashes(filter_input(INPUT_POST, 'KeyToChange')), $ViewConfigName, $Group)) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration disabled: '.filter_input(INPUT_POST, 'ConfFile').': '.filter_input(INPUT_POST, 'KeyToChange'));
		}
	}
	else if (filter_has_var(INPUT_POST, 'Enable')) {
		/// @warning PHP (?) escapes backslashes, remove first
		if ($View->Controller($Output, 'EnableConf', RemoveBackSlashes(filter_input(INPUT_POST, 'KeyToChange')), $ViewConfigName, $Group)) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration enabled: '.filter_input(INPUT_POST, 'ConfFile').': '.filter_input(INPUT_POST, 'KeyToChange'));
		}
	}
	else if (filter_has_var(INPUT_POST, 'Reload')) {
		if ($View->Controller($Output, 'Reload')) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, 'Configuration reloaded');
		}
	}
}

$View->SetSessionConfOpt();

require_once($VIEW_PATH.'/header.php');

if (isset($PRINT_CONFOPT_FORM) && $PRINT_CONFOPT_FORM) {
	$View->PrintConfOptForm();
	$Group= $_SESSION[$View->Model]['ConfOpt'];
}
?>
<table id="nvp">
	<?php
	$Row= 1;
	if (isset($ReloadConfig) && $ReloadConfig) {
		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		PrintReloadConfigForm();
	}

	if (isset($CustomFunc)) {
		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		$CustomFunc($CustomFuncParam);
	}

	if ($View->Controller($output, 'GetConfigValues', $ViewConfigName, $Group)) {
		PrintNVPForm(json_decode($output[0], TRUE));
	}
	?>
</table>
<?php
PrintHelpWindow($View->ConfHelpMsg);
require_once($VIEW_PATH.'/footer.php');
?>
