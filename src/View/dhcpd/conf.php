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

/** @file
 * Dynamic configuration.
 */

require_once('include.php');

/**
 * Displays a form to change the interface(s) DHCP server distributes IPs from.
 */
function PrintDHCPInterfaceForm()
{
	global $View, $Row;
	
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _TITLE2('Interfaces').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="InterfacesToDelete[]" multiple style="width: 100px; height: 50px;">
				<?php
				if ($View->Controller($ifs, 'GetIfs')) {
					foreach ($ifs as $if) {
						?>
						<option value="<?php echo $if ?>"><?php echo $if ?></option>
						<?php
					}
				}
				?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/>
				<br />
				<select name="Interfaces[]" multiple style="width: 100px; height: 50px;">
				<?php
				if ($View->Controller($ifs, 'GetPhyIfs')) {
					foreach ($ifs as $if) {
						?>
						<option value="<?php echo $if ?>"><?php echo $if ?></option>
						<?php
					}
				}
				?>
				</select>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('This is the list of interfaces DHCP server listens and distributes IP leases. Here you should add the interface(s) to start the DHCP server for.')) ?>
		</td>
	</tr>
	<?php
}

/**
 * Displays a form to change DHCP server dynamic options.
 *
 * All the options, except range, use this function.
 */
function PrintDHCPOptionForm($option)
{
	global $View, $Row;
	
	$helpmsgs= array(
		'domain-name-servers'	=>	_HELPBOX2('This is the DNS server internal clients use. The default is the internal IP address of the system.'),
		'routers'				=>	_HELPBOX2('This is the gateway internal clients use to reach the external network. The default is the internal IP address of the system.'),
		);

	$titles= array(
		'domain-name-servers'	=>	_TITLE2('Name server'),
		'routers'				=>	_TITLE2('Gateway'),
		'subnet-mask'			=>	_TITLE2('Subnet mask'),
		'broadcast-address'		=>	_TITLE2('Broadcast address'),
		);

	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _($titles[$option]).':';?>
		</td>
		<td>
			<?php
			if ($View->Controller($value, 'GetOption', $option)) {
				?>
				<input type="text" name="<?php echo $option ?>" value="<?php echo $value[0] ?>" style="width: 100px;" maxlength="15"/>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php
			if (isset($helpmsgs[$option])) {
				PrintHelpBox(_($helpmsgs[$option]));
			}
			?>
		</td>
	</tr>
	<?php
}

/**
 * Displays a form to change DHCP server IP range.
 */
function PrintDHCPRangeOptionForm()
{
	global $View, $Row;
	
	$class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
	?>
	<tr class="<?php echo $class ?>">
		<td class="title">
			<?php echo _TITLE2('IP range').':' ?>
		</td>
		<td>
			<?php
			if ($View->Controller($value, 'GetRange')) {
				?>
				<input type="text" name="lower_range" value="<?php echo $value[0] ?>" style="width: 100px;" maxlength="15"/>
				-
				<input type="text" name="upper_range" value="<?php echo $value[1] ?>" style="width: 100px;" maxlength="15"/>
				<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				<?php
			}
			?>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('You should take into account that there may be computers with static IP addresses or using BOOTP protocol, and choose a range accordingly.')) ?>
		</td>
	</tr>
	<?php
}

if (filter_has_var(INPUT_POST, 'Delete')) {
	foreach ($_POST['InterfacesToDelete'] as $If) {
		$View->Controller($Output, 'DelIf', $If);
	}
}
else if (filter_has_var(INPUT_POST, 'Add')) {
	foreach ($_POST['Interfaces'] as $If) {
		$View->Controller($Output, 'AddIf', $If);
	}
}
else if (filter_has_var(INPUT_POST, 'Apply')) {
	$View->Controller($Output, 'SetOptions', filter_input(INPUT_POST, 'domain-name-servers'), filter_input(INPUT_POST, 'routers'),
			filter_input(INPUT_POST, 'subnet-mask'), filter_input(INPUT_POST, 'broadcast-address'), filter_input(INPUT_POST, 'lower_range'),
			filter_input(INPUT_POST, 'upper_range'));
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<?php
	$Row= 1;
	PrintDHCPInterfaceForm();
	?>
	<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
		<?php
		PrintDHCPOptionForm('domain-name-servers');
		PrintDHCPOptionForm('routers');
		PrintDHCPOptionForm('subnet-mask');
		PrintDHCPOptionForm('broadcast-address');
		PrintDHCPRangeOptionForm();
		?>
	</form>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('DHCP dynamic configuration is used by clients in the internal network to obtain an IP address and network settings.'));
require_once($VIEW_PATH.'/footer.php');
?>
