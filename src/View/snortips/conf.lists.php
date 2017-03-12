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

/** @file
 * Snort IPS white and black lists.
 */

if (filter_has_var(INPUT_POST, 'Delete') && filter_has_var(INPUT_POST, 'IPs')) {
	$View->Controller($Output, 'DelIPFromList', filter_input(INPUT_POST, 'List'), json_encode($_POST['IPs']));
}
else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'IPToAdd')) {
	$View->Controller($Output, 'AddIPToList', filter_input(INPUT_POST, 'List'), filter_input(INPUT_POST, 'IPToAdd'));
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<?php
	$Row= 1;
	$View->PrintListedIPsForm('whitelist', _TITLE2('Whitelisted'), _HELPBOX2('Whitelisted IPs are never blocked, even if IDS produces alerts for them. Make sure you have internal and external IP addresses of the system whitelisted here. Otherwise, false positives may block access to the system from the network.'));
	$View->PrintListedIPsForm('blacklist', _TITLE2('Blacklisted'), _HELPBOX2('Blacklisted IPs are always blocked.'));
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Intrusion alerts produced by the IDS are guesses, hence there may be false positives or wrong alarms. Since the IPS depends on alerts produced by the IDS, you may want to make sure some IP addresses are never blocked accidentally, such as the internal and external IP addresses of the system, or the IP address of the computer you use to access this web administration interface.

You can enter individual IPs or network addresses. IP and network addresses can overlap. For example, you can blacklist 10.0.0.0/24, but whitelist 10.0.0.1.'));
require_once($VIEW_PATH.'/footer.php');
?>
