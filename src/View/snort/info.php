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

require_once('snort.php');

if (filter_has_var(INPUT_POST, 'Start')) {
	if (filter_has_var(INPUT_POST, 'Interfaces')) {
		foreach ($_POST['Interfaces'] as $If) {
			$View->Controller($Output, 'StopProcess', $If);
			$View->Controller($Output, 'Start', $If);
		}
	}
	else {
		PrintHelpWindow(_NOTICE('FAILED').': '._NOTICE('You should select at least one interface to start IDS for'), 'auto', 'ERROR');
	}
}
else if (filter_has_var(INPUT_POST, 'Stop')) {
	if (filter_has_var(INPUT_POST, 'Interfaces')) {
		foreach ($_POST['Interfaces'] as $If) {
			$View->Controller($Output, 'StopProcess', $If);
		}
	}
	else {
		$View->Stop();
	}
}

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');
		
$View->PrintStatusForm(FALSE, FALSE);
$View->PrintInterfaceSelectForm();

PrintHelpWindow(_HELPWINDOW('You can run multiple Intrusion Detection processes, one for each network interface.'));
require_once($VIEW_PATH.'/footer.php');
?>
