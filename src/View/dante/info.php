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

require_once('include.php');

$generate_status= $View->ProcessStartStopRequests();

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm($generate_status, PRINT_COUNT);

PrintHelpWindow(_HELPWINDOW('SOCKS proxy is used by clients which support SOCKS protocol, such as file sharing applications. If you do not plan on providing support for SOCKS-enabled applications, you can stop this proxy. Some client applications fail to use the SOCKS proxy fully, even if they are configured correctly. Check the packet filter logs for blocked packets.'));
require_once($VIEW_PATH.'/footer.php');
?>
