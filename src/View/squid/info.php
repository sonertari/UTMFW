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

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm();

PrintHelpWindow(_HELPWINDOW('HTTP Proxy runs in front of the web filter and is responsible for fetching all web requests. Therefore, if this proxy is not running, the web filter cannot access the web, hence all web access is effectively blocked.'));
require_once($VIEW_PATH.'/footer.php');
?>
