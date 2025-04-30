<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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
 * Wrapper for ConfStartStopForm().
 */
function PrintConfStartStopForm()
{
	global $View, $ADMIN;
	
	/// Only admin can start/stop the processes
	if (in_array($_SESSION['USER'], $ADMIN)) {
		?>
		<table id="ifselect">
			<tr>
				<td class="title">
					<?php echo _TITLE2('Configuration').':' ?>
				</td>
				<td>
					<?php
					$View->ConfStartStopForm();
					?>
				</td>
				<td class="help">
					<?php PrintHelpBox(_HELPBOX2('Here you should select configuration file(s) to start or stop the process with. Instance with the selected configuration is restarted if it is already running. Only the files with .conf extention are displayed.')) ?>
				</td>
			</tr>
		</table>
		<?php
	}
}

$generate_status= 0;

if (filter_has_var(INPUT_POST, 'Start')) {
	if (filter_has_var(INPUT_POST, 'ConfFiles')) {
		foreach ($_POST['ConfFiles'] as $file) {
			$View->Controller($Output, 'RestartInstance', $file);
		}
		$generate_status= 1;
	}
	else {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('You should select at least one conf file to start the process'), 'auto', 'ERROR');
	}
}
else if (filter_has_var(INPUT_POST, 'Stop')) {
	if (filter_has_var(INPUT_POST, 'ConfFiles')) {
		foreach ($_POST['ConfFiles'] as $file) {
			$View->Controller($Output, 'StopInstance', $file);
		}
	}
	else {
		// Snort inline IPS
		$View->Stop();
	}
	$generate_status= 1;
}

$View->Controller($Output, 'GetStatus');

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm($generate_status, FALSE, FALSE);
PrintConfStartStopForm();

PrintHelpWindow(_HELPWINDOW('OpenVPN is a virtual private networking solution based on OpenSSL. You should create different configuration for servers and clients, and start OpenVPN accordingly.'));
require_once($VIEW_PATH.'/footer.php');
?>
