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

/** @file
 * Lists all processes running on the system.
 */

require_once('include.php');

$GoingDown= FALSE;
if (count($_POST)) {
	if (filter_has_var(INPUT_POST, 'Restart')) {
		PrintHelpWindow(_NOTICE('System is restarting...'), 'auto', 'WARN');
		$View->Controller($Output, 'Restart');
	}
	else if (filter_has_var(INPUT_POST, 'Stop')) {
		PrintHelpWindow(_NOTICE('System is going down...'), 'auto', 'WARN');
		$View->Controller($Output, 'Shutdown');
	}
	$GoingDown= TRUE;
}

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');

if (!$GoingDown) {
	/// Only admin can start/stop the system
	if (in_array($_SESSION['USER'], $ADMIN)) {
		$RestartConfirm= _NOTICE('Are you sure you want to restart the <NAME>?');
		$RestartConfirm= preg_replace('/<NAME>/', $View->Caption, $RestartConfirm);
		$StopConfirm= _NOTICE('Are you sure you want to stop the <NAME>?');
		$StopConfirm= preg_replace('/<NAME>/', $View->Caption, $StopConfirm);
		?>
		<table>
			<tr>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<strong><?php echo _TITLE('System').' ' ?></strong>
						<input type="submit" name="Restart" value="<?php echo _CONTROL('Restart') ?>" onclick="return confirm('<?php echo $RestartConfirm ?>')"/>
						<input type="submit" name="Stop" value="<?php echo _CONTROL('Stop') ?>" onclick="return confirm('<?php echo $StopConfirm ?>')"/>
					</form>
				</td>
				<td style="width: 50%;">
					<?php
					PrintHelpBox(_HELPBOX('You can restart or stop the system using these buttons.'), 400);
					?>
				</td>
			</tr>
		</table>
		<?php
	}
	$View->Controller($Output, 'GetProcList');
	$View->PrintProcessTable(json_decode($Output[0], TRUE), TRUE);
}

require_once($VIEW_PATH.'/footer.php');
?>
