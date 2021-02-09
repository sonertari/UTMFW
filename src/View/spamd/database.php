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

include('include.php');

$LogConf = array(
	'spamdwhitedb' => array(
		'Fields' => array(
			'IP' => _TITLE('IP'),
			'First' => _TITLE('First'),
			'Listed' => _TITLE('Listed'),
			'Expire' => _TITLE('Expire'),
			'#Blocked' => _TITLE('#Blocked'),
			'#Passed' => _TITLE('#Passed'),
			),
		),
	'spamdgreydb' => array(
		'Fields' => array(
			'IP' => _TITLE('IP'),
			'From' => _TITLE('From'),
			'To' => _TITLE('To'),
			'First' => _TITLE('First'),
			'Listed' => _TITLE('Listed'),
			'Expire' => _TITLE('Expire'),
			'#Blocked' => _TITLE('#Blocked'),
			'#Passed' => _TITLE('#Passed'),
			),
		),
	);

require_once($VIEW_PATH.'/header.php');
?>
<table id="logline" class="center">
	<?php

	echo '<strong>'._TITLE2('Spamd Grey DB').' : '.'</strong><br />';
	PrintTableHeaders('spamdgreydb');

	$View->Controller($Output, 'GetGreylist');
	$SpamdDB= explode("\n", $Output[0]);
	if ($Output[0] != '' && $SpamdDB != FALSE) {
		$View->Model= 'spamdgreydb';
		$LineCount= 1;
		foreach ($SpamdDB as $Line) {
			PrintSpamdDBLine($Line, $LineCount++);
		}
	}
	?>
</table>
<table id="logline" class="center">
	<?php
	echo '<br /><strong>'._TITLE2('Spamd White DB').' : '.'</strong><br />';
	PrintTableHeaders('spamdwhitedb');

	$View->Controller($Output, 'GetWhitelist');
	$SpamdDB= explode("\n", $Output[0]);
	if ($Output[0] != '' && $SpamdDB != FALSE) {
		$View->Model= 'spamdwhitedb';
		$LineCount= 1;
		foreach ($SpamdDB as $Line) {
			PrintSpamdDBLine($Line, $LineCount++);
		}
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('This page lists Spamd database entries. Definitions of the columns are as follows:
<ul class="nomargin"><li class="nomargin">Source IP: IP address the connection originated from</li><li class="nomargin">From: envelope-from address for GREY (empty for WHITE entries)</li><li class="nomargin">To: envelope-to address for GREY (empty for WHITE entries)</li><li class="nomargin">First: time the entry was first seen</li><li class="nomargin">Listed: time the entry passed from being GREY to being WHITE</li><li class="nomargin">Expire: time the entry will expire and be removed from the database</li><li class="nomargin">#Block: number of times a corresponding connection received a temporary failure from spamd(8)</li><li class="nomargin">#Passed: number of times a corresponding connection has been seen to pass to the real MTA by spamlogd(8)</li></ul>
E-mails originating from Whitelist entries are allowed until expiration date.

Greylist entries are currently active entries according to grey-listing mode of spamd.'));
require_once($VIEW_PATH.'/footer.php');
?>
