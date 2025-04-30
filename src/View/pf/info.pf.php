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

require_once('pf.php');

$generate_status= $View->ProcessStartStopRequests();

$View->Controller($Output, 'GetPfInfo');
$PfInfo= implode("\n", $Output);

$View->Controller($Output, 'GetPfMemInfo');
$PfMem= implode("\n", $Output);

$View->Controller($Output, 'GetPfTimeoutInfo');
$PfTimeout= implode("\n", $Output);

$Reload= TRUE;
require_once($VIEW_PATH . '/header.php');

$View->PrintStatusForm($generate_status, FALSE, TRUE, FALSE);
?>
<table class="shadowbox" style="padding-right: 12px;">
	<tr>
		<td>
<pre>
<?php echo $PfInfo ?>
<br>
<?php echo $PfMem ?>
<br>
<?php echo $PfTimeout ?>
</pre>
		</td>
	</tr>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Here you can enable or disable the Packet Filter. Note that most services depend on the packet filter being enabled.'));
require_once($VIEW_PATH . '/footer.php');
?>
