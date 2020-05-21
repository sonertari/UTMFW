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

require_once('snort.php');

if (filter_has_var(INPUT_POST, 'Model')) {
	if (filter_input(INPUT_POST, 'Model') == $View->Model) {
		if (filter_has_var(INPUT_POST, 'Start')) {
			if (filter_has_var(INPUT_POST, 'Interfaces')) {
				foreach ($_POST['Interfaces'] as $If) {
					$View->Controller($Output, 'StopInstance', $If);
					$View->Controller($Output, 'StartInstance', $If);
				}
			}
			else {
				PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('You should select at least one interface to start IDS for'), 'auto', 'ERROR');
			}
		}
		else if (filter_has_var(INPUT_POST, 'Stop')) {
			if (filter_has_var(INPUT_POST, 'Interfaces')) {
				foreach ($_POST['Interfaces'] as $If) {
					$View->Controller($Output, 'StopInstance', $If);
				}
			}
			else {
				$View->Stop();
			}
		}
	}
}

$View->Model= 'snortinline';
$View->ProcessStartStopRequests();

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->Model= 'snort';
$View->Caption= _('Intrusion Detection');
$View->PrintStatusForm(FALSE, FALSE);
$View->PrintInterfaceSelectForm();

$View->Model= 'snortinline';
$View->Caption= _TITLE('Inline Intrusion Prevention');
$View->PrintStatusForm();

PrintHelpWindow(_HELPWINDOW('You can run multiple Intrusion Detection processes, one for each network interface. The IDS listens to such interfaces in promiscuous mode.

The Inline IPS is an active inline Intrusion Prevention System (IPS) which inspects the traffic passing through it and may decide to block the traffic using drop rules. If the Inline IPS is stopped, the traffic it is supposed to inspect will effectively be blocked, such as plain and encrypted HTTP, POP3, and SMTP traffic. So if you choose to stop the Inline IPS, you should disable the related pf rule which diverts such traffic to it as well.'));
require_once($VIEW_PATH.'/footer.php');
?>
