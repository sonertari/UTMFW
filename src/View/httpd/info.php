<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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

require_once('httpd.php');

$generate_status= $View->ProcessStartStopRequests();
		
$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm($generate_status, PRINT_COUNT);
PrintHelpWindow(_HELPWINDOW('This web administration interface is served by OpenBSD/httpd. If you stop the web server, you will lose your connection to this web interface. You can restart it on the command line.'));
require_once($VIEW_PATH.'/footer.php');
?>
