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

ProcessStartLine($StartLine);
UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp, $SearchNeedle);

$View->Controller($Output, 'GetArpTableLineCount', $SearchRegExp);
$ArpTableSize= $Output[0];

ProcessNavigationButtons($LinesPerPage, $ArpTableSize, $StartLine, $HeadStart);

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');

PrintLogHeaderForm($StartLine, $ArpTableSize, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
?>
<table id="logline" class="center">
	<?php
	PrintTableHeaders('arp');

	$View->Controller($Output, 'GetArpTable', $SearchRegExp, $HeadStart, $LinesPerPage);
	$ArpTable= json_decode($Output[0], TRUE);

	$LineCount= $StartLine + 1;
	$LastLineNum= $StartLine + min(array(count($ArpTable), $LinesPerPage));
	foreach ($ArpTable as $Logline) {
		PrintLogCols($LineCount++, $Logline, $LastLineNum, '', 'arp');
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Arp table lists the active IP addresses in the network.'));
require_once($VIEW_PATH.'/footer.php');
?>
