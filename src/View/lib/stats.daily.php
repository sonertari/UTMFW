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

/** @file
 * All date statistics pages include this file.
 * Statistics configuration is in $StatsConf.
 */

require_once('../lib/vars.php');

$View->UploadLogFile();

$LogFile= GetLogFile();

$GraphStyle= 'Hourly';
$GraphType= 'Horizontal';

$ApplyDefaults= TRUE;

// Will apply defaults if log file changed
if (!isset($_SESSION[$View->Model][$Submenu]['PrevLogFile']) || $LogFile === $_SESSION[$View->Model][$Submenu]['PrevLogFile'] ||
		// Sender input indicates that the user has clicked on an item on a Stats page, so we should process what that page posts
		filter_has_var(INPUT_POST, 'Sender')) {
	if (count($_POST)) {
		// The existence of Month POST var means that the user has clicked the Apply button of the datetime selection, not log file chooser form
		if (filter_has_var(INPUT_POST, 'Apply') && filter_has_var(INPUT_POST, 'Month')) {
			$DateArray['Month']= filter_input(INPUT_POST, 'Month');
			if ($DateArray['Month'] == '') {
				$DateArray['Day']= '';
			}
			else {
				$DateArray['Day']= filter_input(INPUT_POST, 'Day');
			}

			if ($View->IsDateRange($DateArray)) {
				$GraphStyle= 'Daily';
			}

			if (filter_has_var(INPUT_POST, 'GraphType')) {
				$GraphType= filter_input(INPUT_POST, 'GraphType');
			}

			if (filter_has_var(INPUT_POST, 'GraphStyle')) {
				$GraphStyle= filter_input(INPUT_POST, 'GraphStyle');
			}

			$ApplyDefaults= FALSE;
		}
	}
	// Use isset here, Month and Day may be empty string
	else if (isset($_SESSION[$View->Model][$Submenu]['Month'],
		$_SESSION[$View->Model][$Submenu]['Day'],
		$_SESSION[$View->Model][$Submenu]['GraphType'],
		$_SESSION[$View->Model][$Submenu]['GraphStyle'])) {
		
		$DateArray['Month']= $_SESSION[$View->Model][$Submenu]['Month'];
		$DateArray['Day']= $_SESSION[$View->Model][$Submenu]['Day'];
		$GraphType= $_SESSION[$View->Model][$Submenu]['GraphType'];
		$GraphStyle= $_SESSION[$View->Model][$Submenu]['GraphStyle'];
		
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
	}
	else {
		$DateArray['Month']= '';
		$DateArray['Day']= '';
		$GraphStyle= 'Daily';
	}
}

// Default to hourly style for single dates, $DateRange is used below too
if (!$DateRange= $View->IsDateRange($DateArray)) {
	$GraphStyle= 'Hourly';
}

// Cannot display date ranges horizontally, dates can be on the Y axis only
if ($GraphStyle == 'Daily') {
	$GraphType= 'Vertical';
}

$_SESSION[$View->Model][$Submenu]['Month']= $DateArray['Month'];
$_SESSION[$View->Model][$Submenu]['Day']= $DateArray['Day'];
$_SESSION[$View->Model][$Submenu]['GraphType']= $GraphType;
$_SESSION[$View->Model][$Submenu]['GraphStyle']= $GraphStyle;

$ViewStatsConf= $StatsConf[$View->Model];

if (!isset($ViewStatsConf['Total']['SearchRegexpPrefix'])) {
	$ViewStatsConf['Total']['SearchRegexpPrefix']= '';
}
if (!isset($ViewStatsConf['Total']['SearchRegexpPostfix'])) {
	$ViewStatsConf['Total']['SearchRegexpPostfix']= '';
}

$DateStats= array();
if ($LogFile !== FALSE && $View->Controller($Output, 'GetStats', $LogFile, json_encode($DateArray), $GraphStyle == 'Hourly' ? 'COLLECT' : '')) {
	$Stats= json_decode($Output[0], TRUE);
	if (isset($Stats['Date'])) {
		$DateStats= $Stats['Date'];
	}
}
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
					$selected= ($DateArray['Month'] == '') ? 'selected' : '';
					?>
					<option <?php echo $selected ?> value="<?php echo '' ?>"><?php echo '' ?></option>
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
					$selected= ($DateArray['Day'] == '') ? 'selected' : '';
					?>
					<option <?php echo $selected ?> value="<?php echo '' ?>"><?php echo '' ?></option>
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
				<?php
				if ($DateRange) {
					?>
					<select name="GraphStyle">
						<option <?php echo ($GraphStyle == 'Hourly') ? 'selected' : '' ?> value="<?php echo 'Hourly' ?>"><?php echo _CONTROL('Hourly') ?></option>
						<option <?php echo ($GraphStyle == 'Daily') ? 'selected' : '' ?> value="<?php echo 'Daily' ?>"><?php echo _CONTROL('Daily') ?></option>
					</select>
					<?php
				}
				if ($GraphStyle == 'Hourly') {
					?>
					<select name="GraphType">
						<option <?php echo ($GraphType == 'Vertical') ? 'selected' : '' ?> value="<?php echo 'Vertical' ?>"><?php echo _CONTROL('Vertical') ?></option>
						<option <?php echo ($GraphType == 'Horizontal') ? 'selected' : '' ?> value="<?php echo 'Horizontal' ?>"><?php echo _CONTROL('Horizontal') ?></option>
					</select>
					<?php
				}
				?>
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
		PrintGraphNVPSet($DateStats, $DateArray, $Name, $Conf, $GraphType, $GraphStyle, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], 'daily');
	}
}

foreach ($ViewStatsConf as $Name => $CurConf) {
	if (isset($CurConf['Counters'])) {
		foreach ($CurConf['Counters'] as $Name => $Conf) {
			PrintGraphNVPSet($DateStats, $DateArray, $Name, $Conf, $GraphType, $GraphStyle, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], 'daily');
		}
	}
}

DisplayChartTriggers();
PrintHelpWindow(_($StatsWarningMsg), 'auto', 'WARN');
PrintHelpWindow(_($StatsHelpMsg));
require_once($VIEW_PATH . '/footer.php');
?>
