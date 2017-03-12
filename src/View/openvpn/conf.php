<?php
/*
 * Copyright (C) 2004-2017 Soner Tari
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

require_once('include.php');

/**
 * Wrapper for ConfSelectForm().
 *
 * @param string $module Module name passed to ConfSelectForm()
 * @param string $helpmsg Extra help msg to append
 */
function PrintConfSelectForm($module, $helpmsg= '')
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Configuration').':' ?>
		</td>
		<td>
			<?php
			$View->ConfSelectForm($module);
			?>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('Here you can select the configuration file to view or modify. Also, you can delete the selected configuration file or copy it under another name. Only file names with .conf extention are displayed.').' '.$helpmsg) ?>
		</td>
	</tr>
	<?php
}

if ($_SESSION[$View->Model][basename($_SERVER['PHP_SELF'])]['ConfFile']) {
	$ConfigFile= $_SESSION[$View->Model][basename($_SERVER['PHP_SELF'])]['ConfFile'];
}

if (count($_POST)) {
	if (filter_has_var(INPUT_POST, 'Select')) {
		$ConfigFile= filter_input(INPUT_POST, 'ConfFile');
	}
	else if (filter_has_var(INPUT_POST, 'Delete')) {
		if ($View->Controller($ConfFiles, 'DeleteConf', filter_input(INPUT_POST, 'ConfFile'))) {
			if ($View->Controller($ConfFiles, 'GetConfs')) {
				if (count($ConfFiles) > 0) {
					$ConfigFile= basename($ConfFiles[0]);
				}
			}
		}
	}
	else if (filter_has_var(INPUT_POST, 'Copy')) {
		// File names should always have .conf ext, otherwise they are not displayed on the webif
		$NewFileName= filter_input(INPUT_POST, 'CopyTo');
		if (!preg_match('/^.*\.conf$/', filter_input(INPUT_POST, 'CopyTo'), $Fields)) {
			$NewFileName= filter_input(INPUT_POST, 'CopyTo').'.conf';
		}

		$View->Controller($Output, 'CopyConf', $ConfigFile, $NewFileName);
	}
}

$View->SetConfig($ConfigFile);

$CustomFunc= 'PrintConfSelectForm';
$CustomFuncParam= $View->Model;

require_once('../lib/conf.php');
?>
