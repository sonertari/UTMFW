<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
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

$View->Model= 'collectd';

if (filter_has_var(INPUT_POST, 'GatewayPingHost')) {
	$View->Controller($Output, 'SetGatewayPingHost', filter_input(INPUT_POST, 'GatewayPingHost'));
} else if (filter_has_var(INPUT_POST, 'RemotePingHost')) {
	$View->Controller($Output, 'SetRemotePingHost', filter_input(INPUT_POST, 'RemotePingHost'));
}

$PingHosts= $View->Controller($Output, 'GetPingHosts');

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<?php
	if ($PingHosts) {
		$output= json_decode($Output[0], TRUE);
		$GatewayPingHost= $output[0] ? $output[0] : '';
		$RemotePingHost= $output[1] ? $output[1] : '';
		?>
		<tr class="oddline">
			<td class="title">
				<?php echo _TITLE2('Gateway ping host').':' ?>
			</td>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="GatewayPingHost" style="width: 100px;" maxlength="160" value="<?php echo $GatewayPingHost ?>"/>
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX2('By default, the first ping host is the system gateway.')) ?>
			</td>
		</tr>
		<tr class="evenline">
			<td class="title">
				<?php echo _TITLE2('Remote ping host').':' ?>
			</td>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input type="text" name="RemotePingHost" style="width: 100px;" maxlength="160" value="<?php echo $RemotePingHost ?>"/>
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX2('The second ping host is on the Internet.')) ?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('Collectd is configured to ping two hosts. Here you can configure them both. You should restart the collectd process to activate your changes.'));
require_once($VIEW_PATH.'/footer.php');
?>
