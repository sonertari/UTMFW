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
 * All hourly statistics pages include this file.
 * Statistics configuration is in $StatsConf.
 */

require_once('../lib/vars.php');

$View->UploadLogFile();

$LogFile= GetLogFile();

$ApplyDefaults= TRUE;

// Will apply defaults if log file changed
if (!isset($_SESSION[$View->Model][$Submenu]['PrevLogFile']) || $LogFile === $_SESSION[$View->Model][$Submenu]['PrevLogFile'] ||
		// Sender input indicates that the user has clicked on an item on a Stats page, so we should process what that page posts
		filter_has_var(INPUT_POST, 'Sender')) {
	if (count($_POST)) {
		// The existence of Month POST var means that the user has clicked the Apply button of the datetime selection, not log file chooser form
		// Otherwise, the datetime fields would not be set, causing empty bar charts and top lists
		if (filter_has_var(INPUT_POST, 'Apply') && filter_has_var(INPUT_POST, 'Month')) {
			$DateArray['Month']= filter_input(INPUT_POST, 'Month');
			$DateArray['Day']= filter_input(INPUT_POST, 'Day');
			$DateArray['Hour']= filter_input(INPUT_POST, 'Hour');
			if (filter_has_var(INPUT_POST, 'GraphType')) {
				$GraphType= filter_input(INPUT_POST, 'GraphType');
			} else {
				$GraphType= 'Horizontal';
			}
			
			$ApplyDefaults= FALSE;
		}
	}
	// Use isset here, Month and Day may be empty string
	else if (isset($_SESSION[$View->Model][$Submenu]['Month'],
		$_SESSION[$View->Model][$Submenu]['Day'],
		$_SESSION[$View->Model][$Submenu]['Hour'],
		$_SESSION[$View->Model][$Submenu]['GraphType'])) {
		
		$DateArray['Month']= $_SESSION[$View->Model][$Submenu]['Month'];
		$DateArray['Day']= $_SESSION[$View->Model][$Submenu]['Day'];
		$DateArray['Hour']= $_SESSION[$View->Model][$Submenu]['Hour'];
		$GraphType= $_SESSION[$View->Model][$Submenu]['GraphType'];
		
		$ApplyDefaults= FALSE;
	}
}
// Set the previous log file now, due to above if condition
$_SESSION[$View->Model][$Submenu]['PrevLogFile']= $LogFile;

if ($ApplyDefaults) {
	$View->Controller($Output, 'GetDefaultLogFile');
	$file= $Output[0];
	if (basename($LogFile) == basename($file)) {
		$DateArray['Month']= exec('/bin/date +%m');
		$DateArray['Day']= exec('/bin/date +%d');
		$DateArray['Hour']= exec('/bin/date +%H');
	}
	else {
		$View->Controller($Output, 'GetLogStartDate', $LogFile);
		$LogsStartDate= $Output[0];

		if (preg_match('/^(.*)\s(\d+):\d+:\d+$/', $LogsStartDate, $match)) {
			$Date= $match[1];
			$Hour= $match[2];
		}
		else {
			$Hour= 12;
		}
		$View->FormatDateArray($Date, $DateArray);
		$DateArray['Hour']= $Hour;
	}
	$GraphType= 'Horizontal';
}

$_SESSION[$View->Model][$Submenu]['Month']= $DateArray['Month'];
$_SESSION[$View->Model][$Submenu]['Day']= $DateArray['Day'];
$_SESSION[$View->Model][$Submenu]['Hour']= $DateArray['Hour'];
$_SESSION[$View->Model][$Submenu]['GraphType']= $GraphType;

$Hour= $DateArray['Hour'];
$Date= $View->FormatDate($DateArray);

$ViewStatsConf= $StatsConf[$View->Model];

$View->Controller($Output, 'GetStats', $LogFile, json_encode($DateArray), 'COLLECT');
$Stats= json_decode($Output[0], TRUE);
$DateStats= $Stats['Date'];

require_once($VIEW_PATH . '/header.php');

PrintLogFileChooser($LogFile);
?>
<table id="nvp">
	<tr class="evenline">
		<td colspan="2">
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php echo _TITLE('Month').':' ?>
				<select name="Month" style="width: 50px;">
					<?php
					for ($m= 1; $m <= 12; $m++) {
						$m= sprintf('%02d', $m);
						$selected= ($DateArray['Month'] == $m) ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $m ?>"><?php echo $m ?></option>
						<?php
					}
					?>
				</select>
				<?php echo _TITLE('Day').':' ?>
				<select name="Day" style="width: 50px;">
					<?php
					for ($d= 1; $d <= 31; $d++) {
						$d= sprintf('%02d', $d);
						$selected= ($DateArray['Day'] == $d) ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $d ?>"><?php echo $d ?></option>
						<?php
					}
					?>
				</select>
				<?php echo _TITLE('Hour').':' ?>
				<select name="Hour">
					<?php
					for ($h= 0; $h < 24; $h++) {
						$h= sprintf('%02d', $h);
						$selected= ($Hour == $h) ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $h ?>"><?php echo $h ?></option>
						<?php
					}
					?>
				</select>
				<select name="GraphType">
					<option <?php echo ($GraphType == 'Horizontal') ? 'selected' : '' ?> value="<?php echo 'Horizontal' ?>"><?php echo _CONTROL('Horizontal') ?></option>
					<option <?php echo ($GraphType == 'Vertical') ? 'selected' : '' ?> value="<?php echo 'Vertical' ?>"><?php echo _CONTROL('Vertical') ?></option>
				</select>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				<input type="submit" name="Defaults" value="<?php echo _CONTROL('Defaults') ?>"/>
			</form>
		</td>
	</tr>
</table>
<?php
PrintModalPieChart();

foreach ($ViewStatsConf as $Name => $Conf) {
	if (isset($Conf['Color'])) {
		PrintMinutesGraphNVPSet($DateStats[$Date]['Hours'][$Hour], $Name, $Conf, $GraphType, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], $DateArray);
	}
}

foreach ($ViewStatsConf as $Name => $CurConf) {
	if (isset($CurConf['Counters'])) {
		foreach ($CurConf['Counters'] as $Name => $Conf) {
			PrintMinutesGraphNVPSet($DateStats[$Date]['Hours'][$Hour], $Name, $Conf, $GraphType, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], $DateArray);
		}
	}
}

DisplayChartTriggers();
PrintHelpWindow(_($StatsWarningMsg), 'auto', 'WARN');
PrintHelpWindow(_($StatsHelpMsg));
require_once($VIEW_PATH . '/footer.php');
?>
