<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<?php
	if ($View->Controller($Interfaces, 'GetPhyIfs')) {
		?>
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
		<?php
	}
	?>
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
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Buttons on this page should help you apply the new configuration system-wide when you change certain system settings or hardware.

The web user interface stores statistics under /var/tmp/utmfw folder. The statistics are updated incrementally when new messages are appended to log files. When user selects a compressed log file for viewing or statistics, its uncompressed copy is saved under the same folder also. This strategy greatly improves the performance of Statistics and Logs pages, and enables other features of the web user interface. Since these statistics and uncompressed log files may take up a lot of disk space, you are advised to keep your /var partition as large as possible.'));
require_once($VIEW_PATH.'/footer.php');
?>
