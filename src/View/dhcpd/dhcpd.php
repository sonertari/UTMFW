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
 * Info, arp output, and list of leases.
 */

$generate_status= $View->ProcessStartStopRequests();

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');

$View->PrintStatusForm($generate_status);
?>
<strong><?php echo _TITLE2('Leases').':' ?></strong>
<?php
ProcessStartLine($StartLine);
UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp, $SearchNeedle);

$View->Controller($Output, 'GetLeasesLineCount', $SearchRegExp);
$LeasesCount= $Output[0];

ProcessNavigationButtons($LinesPerPage, $LeasesCount, $StartLine, $HeadStart);

PrintLogHeaderForm($StartLine, $LeasesCount, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
?>
<table id="logline" class="center">
	<?php
	PrintTableHeaders('lease');

	$View->Controller($Output, 'GetLeases', $SearchRegExp, $HeadStart, $LinesPerPage);
	$Leases= json_decode($Output[0], TRUE);

	$LineCount= $StartLine + 1;
	$LastLineNum= $StartLine + min(array(count($Leases), $LinesPerPage));
	foreach ($Leases as $Logline) {
		PrintLogCols($LineCount++, $Logline, $LastLineNum, '', 'lease');
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('DHCP server supports both dynamic dhcp and bootp protocols. Dynamic leases can be monitored on this page.'));
require_once($VIEW_PATH.'/footer.php');
?>
