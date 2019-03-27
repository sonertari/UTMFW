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

require_once('sslproxy.php');

$View->ProcessStartStopRequests();

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm();
?>
<strong><?php echo _('Statistics') ?></strong>
<?php
$View->PrintStatsMaxValues($StatusCheckInterval);

PrintHelpWindow(_HELPWINDOW('The SSL proxy decrypts SSL/TLS encrypted traffic and feeds it into the UTM services. The inline IPS inspects the decrypted traffic for intrusion detection and prevention as well.

The max statistics displayed on this page represent the status of the SSLproxy process as a whole, within the last report interval in seconds. The numbers here may give you an idea on the current load of the proxy. For example, if the max number of file descriptors is too high, you might want to increase the open files limit, either in the SSLproxy configuration or in login.conf file (daemon class).'));
require_once($VIEW_PATH.'/footer.php');
?>
