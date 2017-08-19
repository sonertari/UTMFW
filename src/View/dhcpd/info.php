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

/** @file
 * Info, arp output, and list of leases.
 */

require_once('include.php');

$View->ProcessStartStopRequests();

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');

$View->PrintStatusForm();
?>
<br />
<strong><?php echo _TITLE2('Active IPs (arp table)').':' ?></strong>
<table id="logline" class="center">
	<?php
	PrintTableHeaders('arp');

	if ($View->Controller($Output, 'GetArpTable')) {
		$ArpTable= json_decode($Output[0], TRUE);
		$LineCount= 1;
		foreach ($ArpTable as $Cols) {
			?>
			<tr>
			<?php
			PrintLogCols($LineCount++, $Cols, 'arp');
			?>
			</tr>
			<?php
		}
	}
	?>
</table>
<br />
<strong><?php echo _TITLE2('Leases').':' ?></strong>
<table id="logline" class="center">
	<?php
	PrintTableHeaders('lease');

	if ($View->Controller($Output, 'GetLeases')) {
		$Leases= json_decode($Output[0], TRUE);
		$LineCount= 1;
		foreach ($Leases as $Cols) {
			?>
			<tr>
			<?php
			PrintLogCols($LineCount++, $Cols, 'lease');
			?>
			</tr>
			<?php
		}
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('DHCP server supports both dynamic dhcp and bootp protocols. Dynamic leases can be monitored on this page.'));
require_once($VIEW_PATH.'/footer.php');
?>
