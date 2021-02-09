<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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
 * Basic system-wide configuration.
 */

/**
 * Displays a form to change system datetime.
 */
function PrintDateTimeForm($remotetime)
{
	global $TimeServer;

	if ($_SESSION['TimeServer']) {
		$TimeServer= $_SESSION['TimeServer'];
	}

	/// @attention PHP date() function uses the timezone defined in php ini file, hence we use /bin/date here.
	/// @todo Check how to get local date time using a PHP function.
	$day= exec('/bin/date +%d');
	$month= exec('/bin/date +%m');
	$year= exec('/bin/date +%y');
	$hour= exec('/bin/date +%H');
	$min= exec('/bin/date +%M');
	
	$confirm= _NOTICE('Are you sure you want to set the date?');
	?>
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Time server').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="text" name="TimeServer" style="width: 200px;" maxlength="50" value="<?php echo $TimeServer ?>" />
				<input type="submit" name="Display" value="<?php echo _CONTROL('Display') ?>"/>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
				<?php
				if (isset($remotetime)) {
					echo '<br />'.$remotetime;
				}
				?>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('Usually the network time daemon adjusts the system clock according to Internet time servers gradually. But if the clock is too out of sync with the time servers, you are advised to set the system clock to Internet time manually. You can use your own time server if you like.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Date').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="text" name="Day" style="width: 20px;" maxlength="2" value="<?php echo $day ?>" />.
				<input type="text" name="Month" style="width: 20px;" maxlength="2" value="<?php echo $month ?>" />.
				<input type="text" name="Year" style="width: 20px;" maxlength="2" value="<?php echo $year ?>" /> -
				<input type="text" name="Hour" style="width: 20px;" maxlength="2" value="<?php echo $hour ?>" />:
				<input type="text" name="Minute" style="width: 20px;" maxlength="2" value="<?php echo $min ?>" />
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>" onclick="return confirm('<?php echo $confirm ?>')"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('You can set the system clock here. Format is day.month.year-hour:minute.

<b>You must be very careful when setting the system clock, significant hops in system time may cause system malfunction.</b>'));
			?>
		</td>
	</tr>
	<?php
}


if (count($_POST)) {
	if (filter_has_var(INPUT_POST, 'Year') && filter_has_var(INPUT_POST, 'Month') && filter_has_var(INPUT_POST, 'Day') && filter_has_var(INPUT_POST, 'Hour') && filter_has_var(INPUT_POST, 'Minute')) {
		$NewDateTime= filter_input(INPUT_POST, 'Year').filter_input(INPUT_POST, 'Month').filter_input(INPUT_POST, 'Day').filter_input(INPUT_POST, 'Hour').filter_input(INPUT_POST, 'Minute');
		if (!$View->Controller($Output, 'SetDateTime', $NewDateTime)) {
			wui_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "Time set failed: $NewDateTime");
		}
	}
	else if (filter_has_var(INPUT_POST, 'TimeServer')) {
		$_SESSION['TimeServer']= filter_input(INPUT_POST, 'TimeServer');
		$TimeServer= $_SESSION['TimeServer'];

		if (filter_has_var(INPUT_POST, 'Apply')) {
			$View->Controller($Output, 'SetRemoteTime', $TimeServer);
		}
		else if (filter_has_var(INPUT_POST, 'Display')) {
			$View->Controller($Output, 'DisplayRemoteTime', $TimeServer);
		}
		$View->Controller($Output, 'GetRemoteTime');
		$RemoteTime= implode("\n", $Output);
	}
	else if (filter_has_var(INPUT_POST, 'MyName')) {
		$View->Controller($Output, 'SetMyName', trim(filter_input(INPUT_POST, 'MyName')));
	}
	else if (filter_has_var(INPUT_POST, 'RootEmail')) {
		if ($View->Controller($Output, 'SetRootEmail', filter_input(INPUT_POST, 'RootEmail'))) {
			$View->Controller($Output, 'UpdateMailAliases');
		}
	}
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<tr class="oddline">
		<td class="title">
			<?php echo _TITLE('Hostname').':' ?>
		</td>
		<td>
			<?php
			if ($View->Controller($Myname, 'GetMyName')) {
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="MyName" style="width: 200px;" maxlength="50" value="<?php echo $Myname[0] ?>" />
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('This is the system name. If you change the hostname, use automatic configuration button to apply it system-wide, and reboot the system.'));
			?>
		</td>
	</tr>
	<tr class="evenline">
		<td class="title">
			<?php echo _TITLE('Admin e-mail').':' ?>
		</td>
		<td>
			<?php
			if ($View->Controller($RootEmail, 'GetRootEmail')) {
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="RootEmail" style="width: 200px;" maxlength="50" value="<?php echo $RootEmail[0] ?>" />
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX('UTMFW sends warning and information messages to its system administrator. Here you can type in your own e-mail address.'));
			?>
		</td>
	</tr>
	<?php
	PrintDateTimeForm($RemoteTime);
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('New system hostname does not fully take effect until after reboot.

Messages to the system administrator are directly transfered to the mail server hosting the e-mail address you have provided above. The remote mail server may consider e-mails sent from this system as spam, and move them into your spam or junk folder.'));
require_once($VIEW_PATH.'/footer.php');
?>
