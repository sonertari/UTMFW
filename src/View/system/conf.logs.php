<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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
 * Newsyslog configuration.
 */

if (count($_POST)) {
	$LogFile= RemoveBackSlashes(filter_input(INPUT_POST, 'LogFile'));
	if (filter_has_var(INPUT_POST, 'Apply')) {
		$View->Controller($Output, 'SetLogsConfig', filter_input(INPUT_POST, 'Model'), $LogFile, filter_input(INPUT_POST, 'Count'),
				filter_input(INPUT_POST, 'Size'), filter_input(INPUT_POST, 'When'));
	}
	else if (filter_has_var(INPUT_POST, 'Rotate')) {
		$View->Controller($Output, 'RotateLogFile', $LogFile);
		// httpd may be killed during rotation
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
	else if (filter_has_var(INPUT_POST, 'RotateAll')) {
		$View->Controller($Output, 'RotateAllLogFiles');
		// httpd is killed during rotation
		header('Location: '.$_SERVER['REQUEST_URI']);
		exit;
	}
}

require_once($VIEW_PATH.'/header.php');

if ($View->Controller($Output, 'GetLogsConfig')) {
	$LogsConfig= json_decode($Output[0], TRUE);

	$confirm= _NOTICE('Are you sure you want to rotate the logs?');
	?>
	<table id="nvp">
		<tr id="logline">
			<th><?php echo _TITLE('Logs') ?></th>
			<th class="lheader"><?php echo _TITLE('File') ?></th>
			<th class="lheader"><?php echo _TITLE('Count') ?></th>
			<th class="lheader"><?php echo _TITLE('Size (KB)') ?></th>
			<th class="lheader"><?php echo _TITLE('When (h)') ?></th>
		</tr>
		<?php
		$Row= 1;
		foreach ($LogsConfig as $LogFile => $Conf) {
			$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<tr class="<?php echo $Class ?>">
					<td class="title">
						<?php
							/// @todo Fix this
							//echo _($Modules[$Conf['Model']]['Name']).':';
							echo $Conf['Model'].':';
						?>
					</td>
					<td>
						<?php echo $LogFile ?>
					</td>
					<td class="logprop">
						<input type="text" name="Count" style="width: 50px;" maxlength="10" value="<?php echo $Conf['Count'] ?>"/>
					</td>
					<td class="logprop">
						<input type="text" name="Size" style="width: 50px;" maxlength="10" value="<?php echo $Conf['Size'] ?>"/>
					</td>
					<td class="logprop">
						<input type="text" name="When" style="width: 50px;" maxlength="10" value="<?php echo $Conf['When'] ?>"/>
					</td>
					<td class="logprop">
						<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
						<input type="submit" name="Rotate" value="<?php echo _CONTROL('Rotate') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
					</td>
				</tr>
				<input type="hidden" name="Model" value="<?php echo $Conf['Model'] ?>"/>
				<input type="hidden" name="LogFile" value="<?php echo $LogFile ?>"/>
			</form>
			<?php
		}
		
		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		?>
		<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
			<tr class="<?php echo $Class ?>">
				<td class="title">
					<?php echo _TITLE('Rotate all log files').':' ?>
				</td>
				<td>
					<input type="submit" name="RotateAll" value="<?php echo _CONTROL('Rotate All') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
				</td>
				<td class="none" colspan="4">
					<?php
					PrintHelpBox(_HELPBOX('Here you can rotate all log files manually. Note that this action forcefully rotates all log files irrespective of the configuration displayed on this page. Normally this should not be necessary.'));
					?>
				</td>
			</tr>
		</form>
	</table>
	<?php
}

PrintHelpWindow(_HELPWINDOW('Log files are rotated according to two main criteria: size and time, whichever is reached first. When a log file is rotated, it is renamed and compressed, and a new one is opened for writing.
<ul><li>Count is the maximum number of compressed log files to keep.</li><li>Size is the maximum file size. The log file will be rotated when its file size reaches this value. The unit is KB.</li><li>When defines the frequency interval of rotation. This is in hours. For example, 168 means once each week. This web user interface supports only interval format, not time format for this setting.</li></ul>Asterisk (*) means don\'t care.

Note that log files contain very important information about system and network activity. Statistics are generated over these log files.'));
require_once($VIEW_PATH.'/footer.php');
?>
