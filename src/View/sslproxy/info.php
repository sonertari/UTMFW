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

require_once('include.php');

$View->ProcessStartStopRequests();

$ReportInterval= 60;
if (isset($_SESSION[$View->Model]['ReportInterval'])) {
	$ReportInterval= $_SESSION[$View->Model]['ReportInterval'];
}

if (filter_has_var(INPUT_POST, 'ReportInterval')) {
	$ReportInterval= filter_input(INPUT_POST, 'ReportInterval');
}

if ($ReportInterval < 10) {
	$ReportInterval= 10;
}

$_SESSION[$View->Model]['ReportInterval']= $ReportInterval;

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm();
?>
<br />
<strong><?php echo _('Statistics') ?></strong>
<br />
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
	<?php echo _TITLE2('Report Interval').':' ?>
	<input type="text" name="ReportInterval" style="width: 50px;" maxlength="3" value="<?php echo $ReportInterval ?>"/>
	<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
</form>
<br />
<?php
$View->PrintStatsMaxValues($ReportInterval);
?>
<br />
<?php echo _TITLE2('Idle connections').':' ?>
<?php
$View->PrintIdleConns($ReportInterval);

PrintHelpWindow(_HELPWINDOW('The SSL proxy decrypts the SSL/TLS encrypted traffic and feeds it into the UTM services. The inline IPS inspects the decrypted traffic for intrusion detection and prevention as well.

The max statistics on this page represent the status of the SSL proxy process as a whole within the last report interval in seconds. The numbers here may give you an idea on the current load of the proxy. For example, if the max number of file descriptors is too high, you might want to increase the open files limit of the daemon class in login.conf.

This page also reports certain connections as idle, because they were idle longer than the expired connection check interval of the SSL proxy. This does not mean that these connections have stalled; they may be just slow. Note that the SSL proxy does not allow for persistent HTTP connections, hence tries to close them as soon as possible. Therefore, it is desirable that the idle connections table be empty.'));
require_once($VIEW_PATH.'/footer.php');
?>
