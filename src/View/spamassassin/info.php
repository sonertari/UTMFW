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

require_once('include.php');

$View->ProcessStartStopRequests();

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm();

PrintHelpWindow(_HELPWINDOW('SpamAssassin is a bayesian content scanner and is used in scanning incoming POP3 and outgoing SMTP e-mails for spam content. If SpamAssassin identifies an e-mail as spam, it marks the subject line with a warning message and provides a list of reasons in the body of the e-mail.'));
require_once($VIEW_PATH.'/footer.php');
?>
