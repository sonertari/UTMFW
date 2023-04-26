<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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
 * System initialization.
 */

if (filter_has_var(INPUT_POST, 'Apply')) {
	if ($View->Controller($Output, 'AutoConfig')) {
		PrintHelpWindow(_NOTICE('Automatic configuration completed. You might want to check the details now.'), 'auto', 'INFO');
	}
	else {
		PrintHelpWindow(_NOTICE('Automatic configuration failed.'), 'auto', 'ERROR');
	}
}
else if (filter_has_var(INPUT_POST, 'Reinitialize')) {
	$View->Controller($Output, 'InitGraphs');
}
else if (filter_has_var(INPUT_POST, 'Delete')) {
	$View->Controller($Output, 'DeleteStats');
}
else if (filter_has_var(INPUT_POST, 'Generate')) {
	if ($View->Controller($Output, 'GenerateSSLKeyPairs', filter_input(INPUT_POST, 'SetSerial'))) {
		PrintHelpWindow(_NOTICE('SSL key pairs generated. You should restart the services or the system now.'), 'auto', 'INFO');
	}
	else {
		PrintHelpWindow(_NOTICE('SSL key pair generation failed.'), 'auto', 'ERROR');
	}
}
else if (filter_has_var(INPUT_POST, 'DisableMFS')) {
	if ($View->Controller($Output, 'SetMFS', 'no')) {
		wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Disable MFS');
	}
}
else if (filter_has_var(INPUT_POST, 'EnableMFS')) {
	if ($View->Controller($Output, 'SetMFS', 'yes')) {
		wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Enable MFS');
	}
}
else if (filter_has_var(INPUT_POST, 'SetMFSSize')) {
	if ($View->Controller($Output, 'SetMFSSize', filter_input(INPUT_POST, 'MFSSize'))) {
		wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Enable sync MFS');
	}
}
else if (filter_has_var(INPUT_POST, 'DisableSyncMFS')) {
	if ($View->Controller($Output, 'SetSyncMFS', 'no')) {
		wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Disable sync MFS');
	}
}
else if (filter_has_var(INPUT_POST, 'EnableSyncMFS')) {
	if ($View->Controller($Output, 'SetSyncMFS', 'yes')) {
		wui_syslog(LOG_INFO, __FILE__, __FUNCTION__, __LINE__, 'Enable sync MFS');
	}
}

$MFSConfig= FALSE;
if ($View->Controller($Output, 'GetMFSConfig')) {
	$MFSConfig= json_decode($Output[0], TRUE);
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Automatic config').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Changes to system configuration should be applied system-wide.

<b>If you modify basic or network settings, you are advised to use this button.</b>

Internal and external physical interface names are obtained from packet filter configuration, i.e. int_if and ext_if macros.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Graph files').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Reinitialize" value="<?php echo _CONTROL('Reinitialize') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to reinitialize graph files?') ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Here you can reinitialize, hence erase all accumulated data in graph source files.

This may be necessary if these files are corrupted and you cannot see any graphs displayed, such as when the system clock is set to a very distant time.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Statistics and uncompressed log files').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to erase statistics files?') ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This button allows you to delete all the statistics and uncompressed log files created by this user interface. Note that deleting these files does not mean that accumulated statistics are lost forever. <b>Original log files under /var/log folder are not affected by this action</b> either. These files will be recreated, and statistics will be recollected, the next time you access Statistics or Logs pages.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Generate SSL key pairs').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php echo _TITLE('set serial to') ?>
				<input type="text" name="SetSerial" style="width: 50px;" maxlength="4" value="1" />
				<input type="submit" name="Generate" value="<?php echo _CONTROL('Generate') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to generate SSL key pairs?') ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This button allows you to generate or regenerate the SSL key pairs for httpd, openvpn, and sslproxy. You should restart these services or the system to use the new SSL key pairs. Make sure you increment the serial number on each new release.'));
			?>
		</td>
	</tr>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Memory-based file system').':' ?>
		</td>
		<td>
			<?php
			if ($MFSConfig) {
				$MFSEnabled= $MFSConfig['enable'] == 'yes';
				$MFSEnableButton= $MFSEnabled ? 'Disable' : 'Enable';
				$MFSEnableButtonValue= $MFSEnabled ? _CONTROL('Disable') : _CONTROL('Enable');
				$MFSEnableMessage= $MFSEnabled ? _NOTICE('Are you sure you want to disable MFS?') : _NOTICE('Are you sure you want to enable MFS?');

				$MFSSyncEnabled= $MFSConfig['sync'] == 'yes';
				$MFSSyncButton= $MFSSyncEnabled ? 'Disable' : 'Enable';
				$MFSSyncButtonValue= $MFSSyncEnabled ? _CONTROL('Disable') : _CONTROL('Enable');
				$MFSSyncMessage= $MFSSyncEnabled ? _NOTICE('Are you sure you want to disable persistent MFS?') : _NOTICE('Are you sure you want to enable persistent MFS?');
				?>
				<table>
					<tr>
						<td class="iftitle">
							<?php echo _TITLE('mount as mfs') ?>
						</td>
						<td class="ifs">
							<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
								<input type="submit" name="<?php echo $MFSEnableButton ?>MFS" value="<?php echo $MFSEnableButtonValue ?>" onclick="return confirm('<?php echo $MFSEnableMessage ?>')"/>
							</form>
						</td>
					</tr>
					<tr>
						<td class="iftitle">
							<?php echo _TITLE('set mfs size') ?>
						</td>
						<td class="ifs">
							<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
								<input type="text" name="MFSSize" style="width: 100px;" maxlength="20" value="<?php echo $MFSConfig['size'] ?>" />
								<input type="submit" name="SetMFSSize" value="<?php echo _CONTROL('Apply') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to set the size of MFS?') ?>')"/>
							</form>
						</td>
					</tr>
					<tr>
						<td class="iftitle">
							<?php echo _TITLE('persist mfs') ?>
						</td>
						<td class="ifs">
							<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
								<input type="submit" name="<?php echo $MFSSyncButton ?>SyncMFS" value="<?php echo $MFSSyncButtonValue ?>" onclick="return confirm('<?php echo $MFSSyncMessage ?>')"/>
							</form>
						</td>
					</tr>
				</table>
				<?php
			} else {
				echo _TITLE('Cannot get MFS configuration');
			}
			?>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('If there is enough memeory on the system, mounting /var/log on a memory-based file system can improve the performance of the system, especially if the disk is slow. These settings configure /var/log as MFS.

If you enable MFS, /var/log is mounted as MFS on the next boot.

If you enable persistent MFS, the system starts syncing /var/log to disk, periodically and during shutdown, so that its contents are not lost and can be used after reboot. Note that syncing /var/log to disk may take some time.

You are advised to choose an MFS size of at least 1024m.'));
			?>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Buttons on this page should help you apply the new configuration system-wide when you change certain system settings or hardware.

The web user interface stores statistics under /var/log/utmfw folder. The statistics are updated incrementally when new messages are appended to log files. When user selects a compressed log file for viewing or statistics, its uncompressed copy is saved under the same folder too. This strategy greatly improves the performance of Statistics and Logs pages, and enables other features of the web user interface. Since these statistics and uncompressed log files may take up a lot of disk space, you are advised to have a large partition holding /var/log.'));
require_once($VIEW_PATH.'/footer.php');
?>
