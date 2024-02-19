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
 * All general statistics pages include this file.
 * Statistics configuration is in $StatsConf.
 */

require_once('../lib/vars.php');

$View->UploadLogFile();

$LogFile= GetLogFile();

$DateArray= array('Month' => '', 'Day' => '');

$GraphStyle= 'Daily';
$GraphType= 'Horizontal';

if (count($_POST)) {
	$GraphStyle= filter_input(INPUT_POST, 'GraphStyle');

	if (filter_has_var(INPUT_POST, 'GraphType')) {
		$GraphType= filter_input(INPUT_POST, 'GraphType');
	}
	else {
		if (isset($_SESSION[$View->Model][$Submenu]['GraphType'])) {
			$GraphType= $_SESSION[$View->Model][$Submenu]['GraphType'];
		}
	}
}
else if (isset($_SESSION[$View->Model][$Submenu]['GraphStyle'])) {
	/// @attention Daily style does not set GraphType, do not check it in if condition
	$GraphStyle= $_SESSION[$View->Model][$Submenu]['GraphStyle'];
	if (isset($_SESSION[$View->Model][$Submenu]['GraphType'])) {
		$GraphType= $_SESSION[$View->Model][$Submenu]['GraphType'];
	}
}

if ($GraphStyle == 'Daily') {
	$GraphType= 'Vertical';
}

$_SESSION[$View->Model][$Submenu]['GraphStyle']= $GraphStyle;
if ($GraphStyle == 'Hourly') {
	$_SESSION[$View->Model][$Submenu]['GraphType']= $GraphType;
}

$ViewStatsConf= $StatsConf[$View->Model];

if (!isset($ViewStatsConf['Total']['Needle'])) {
	$ViewStatsConf['Total']['Needle']= '';
}
if (!isset($ViewStatsConf['Total']['SearchRegexpPrefix'])) {
	$ViewStatsConf['Total']['SearchRegexpPrefix']= '';
}
if (!isset($ViewStatsConf['Total']['SearchRegexpPostfix'])) {
	$ViewStatsConf['Total']['SearchRegexpPostfix']= '';
}

$BriefStats= array();
$DateStats= array();
if ($LogFile !== FALSE && $View->Controller($Output, 'GetAllStats', $LogFile, $GraphStyle == 'Hourly' ? 'COLLECT' : '')) {
	$AllStats= json_decode($Output[0], TRUE);
	$Stats= json_decode($AllStats['stats'], TRUE);
	$BriefStats= json_decode($AllStats['briefstats'], TRUE);
	if (isset($Stats['Date'])) {
		$DateStats= $Stats['Date'];
	}
}

require_once($VIEW_PATH . '/header.php');

PrintLogFileChooser($LogFile);
PrintModalPieChart();
?>
<table>
	<tr>
		<td class="top">
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="GraphStyle">
					<option <?php echo ($GraphStyle == 'Hourly') ? 'selected' : '' ?> value="<?php echo 'Hourly' ?>"><?php echo _CONTROL('Hourly') ?></option>
					<option <?php echo ($GraphStyle == 'Daily') ? 'selected' : '' ?> value="<?php echo 'Daily' ?>"><?php echo _CONTROL('Daily') ?></option>
				</select>
				<?php
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
			</form>
			<?php
			foreach ($ViewStatsConf as $Name => $Conf) {
				if (isset($Conf['Color'])) {
					PrintGraphNVPSet($DateStats, $DateArray, $Name, $Conf, $GraphType, $GraphStyle, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], 'general');
				}
			}

			foreach ($ViewStatsConf as $Name => $CurConf) {
				if (isset($CurConf['Counters'])) {
					foreach ($CurConf['Counters'] as $Name => $Conf) {
						PrintGraphNVPSet($DateStats, $DateArray, $Name, $Conf, $GraphType, $GraphStyle, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], 'general');
					}
				}
			}
			?>
		</td>
		<td class="top">
			<?php
			$View->PrintStats($LogFile);

			if (isset($ViewStatsConf['Total']['BriefStats'])) {
				foreach ($ViewStatsConf['Total']['BriefStats'] as $Field => $Name) {
					if (isset($BriefStats[$Field])) {
						PrintNVPs($BriefStats[$Field], _($Name), 50, TRUE, $ViewStatsConf['Total']['Needle'], $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix']);
					}
				}
			}
			?>
		</td>
	</tr>
</table>
<?php
DisplayChartTriggers();
PrintHelpWindow(_($StatsWarningMsg), 'auto', 'WARN');
PrintHelpWindow(_($StatsHelpMsg));
require_once($VIEW_PATH . '/footer.php');
?>
