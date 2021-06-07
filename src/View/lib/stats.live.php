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
 * All live statistics pages include this file.
 * Statistics configuration is in $StatsConf.
 */

require_once('../lib/vars.php');

$Reload= TRUE;
SetRefreshInterval();

$View->Controller($Output, 'GetDefaultLogFile');
$LogFile= $Output[0];

$View->Controller($Output, 'GetDateTime');
$DateArray= json_decode($Output[0], TRUE);

$GraphType= 'Horizontal';

if (count($_POST)) {
	$GraphType= filter_input(INPUT_POST, 'GraphType');
	$_SESSION[$View->Model]['GraphType']= $GraphType;
}
else if (isset($_SESSION[$View->Model]['GraphType'])) {
	$GraphType= $_SESSION[$View->Model]['GraphType'];
}

$Hour= $DateArray['Hour'];
$Date= $View->FormatDate($DateArray);

$ViewStatsConf= $StatsConf[$View->Model];

if (!isset($ViewStatsConf['Total']['SearchRegexpPrefix'])) {
	$ViewStatsConf['Total']['SearchRegexpPrefix']= '';
}
if (!isset($ViewStatsConf['Total']['SearchRegexpPostfix'])) {
	$ViewStatsConf['Total']['SearchRegexpPostfix']= '';
}

$DateStats= array();
if ($View->Controller($Output, 'GetStats', $LogFile, json_encode($DateArray), 'COLLECT')) {
	$Stats= json_decode($Output[0], TRUE);
	if (isset($Stats['Date'])) {
		$DateStats= $Stats['Date'];
	}
}

require_once($VIEW_PATH . '/header.php');
?>
<table>
	<tr>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<?php echo _TITLE('Refresh interval').':' ?>
				<input type="text" name="RefreshInterval" style="width: 20px;" maxlength="2" value="<?php echo $_SESSION[$View->Model][$TopMenu]['ReloadRate'] ?>" />
				<?php echo _TITLE('secs') ?>
				<select name="GraphType">
					<option <?php echo ($GraphType == 'Vertical') ? 'selected' : '' ?> value="<?php echo 'Vertical' ?>"><?php echo _CONTROL('Vertical') ?></option>
					<option <?php echo ($GraphType == 'Horizontal') ? 'selected' : '' ?> value="<?php echo 'Horizontal' ?>"><?php echo _CONTROL('Horizontal') ?></option>
				</select>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td>
			<strong><?php echo _TITLE('Date').': '.$Date.', '.$Hour.':'.date('i') ?></strong>
		</td>
	</tr>
</table>
<?php
PrintModalPieChart();

foreach ($ViewStatsConf as $Name => $Conf) {
	if (isset($Conf['Color']) && isset($DateStats[$Date]['Hours'][$Hour])) {
		PrintMinutesGraphNVPSet($DateStats[$Date]['Hours'][$Hour], $Name, $Conf, $GraphType, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], $DateArray, $LogFile);
	}
}

foreach ($ViewStatsConf as $Name => $CurConf) {
	if (isset($CurConf['Counters']) && isset($DateStats[$Date]['Hours'][$Hour])) {
		foreach ($CurConf['Counters'] as $Name => $Conf) {
			PrintMinutesGraphNVPSet($DateStats[$Date]['Hours'][$Hour], $Name, $Conf, $GraphType, $ViewStatsConf['Total']['SearchRegexpPrefix'], $ViewStatsConf['Total']['SearchRegexpPostfix'], $DateArray, $LogFile);
		}
	}
}

DisplayChartTriggers();
require_once($VIEW_PATH . '/footer.php');
?>
