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

require_once('include.php');

if (filter_has_var(INPUT_POST, 'ListenOn')) {
	$View->Controller($Output, 'SetListenOn', filter_input(INPUT_POST, 'ListenOn'));
}
else if (filter_has_var(INPUT_POST, 'Forwarders')) {
	$View->Controller($Output, 'SetForwarders', filter_input(INPUT_POST, 'Forwarders'));
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<?php
	if ($View->Controller($Output, 'GetListenOn')) {
		$ListenOn= $Output[0];
		?>
		<tr class="oddline">
			<td class="title">
				<?php echo _TITLE2('Listen on').':' ?>
			</td>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="ListenOn" style="width: 100px;" maxlength="160" value="<?php echo $ListenOn ?>"/>
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX2('The IP address that the DNS server listens on.')) ?>
			</td>
		</tr>
		<?php
	}
	if ($View->Controller($Output, 'GetForwarders')) {
		$Forwarders= $Output[0];
		?>
		<tr class="evenline">
			<td class="title">
				<?php echo _TITLE2('Forwarders').':' ?>
			</td>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="Forwarders" style="width: 100px;" maxlength="160" value="<?php echo $Forwarders ?>"/>
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX2('Forwarder is the IP address of a DNS server that the system itself queries. In simple setups, this is usually the IP address of a DSL modem which functions as gateway to the Internet.')) ?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('By default, a simple DNS configuration is provided; only one forwarders is configured, which you can modify on this page. There are no zone records defined, and caching is disabled. However, you can obtain a full-featured DNS server by modifying configuration files.'));
require_once($VIEW_PATH.'/footer.php');
?>
