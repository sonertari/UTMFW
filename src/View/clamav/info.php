<?php
/*
 * Copyright (C) 2004-2020 Soner Tari
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

require_once('clamd.php');
$Clamd= $View;
$Clamd->ProcessStartStopRequests();

require_once('freshclam.php');
$Freshclam= $View;
$Freshclam->ProcessStartStopRequests();

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$Clamd->PrintStatusForm();
$Freshclam->PrintStatusForm();

PrintHelpWindow(_HELPWINDOW('UTMFW uses ClamAV for all virus scanning purposes. Freshclam checks and updates ClamAV virus database periodically. By default, the database is updated every hour. You may not be able to stop or restart freshclam instance once it starts the update process; wait for a minute and try again.'));
require_once($VIEW_PATH.'/footer.php');
?>
