<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

require_once('squid.php');

/**
 * Prints Squid interface change form as IP:Port and lets the user modify it.
 */
function PrintSquidIPPortForm()
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Proxy IP:Ports').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="Interfaces[]" multiple style="width: 200px; height: 50px;">
					<?php
					if ($View->Controller($ifs, 'GetIpPort')) {
						foreach ($ifs as $if) {
							?>
							<option value="<?php echo $if ?>"><?php echo $if ?></option>
							<?php
						}
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="InterfaceToAdd" style="width: 200px;" maxlength="21"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2('This is the IP and port over which the proxy accepts connections.'));
			?>
		</td>
	</tr>
	<?php
}

if (filter_has_var(INPUT_POST, 'Delete')) {
	foreach ($_POST['Interfaces'] as $If) {
		$View->Controller($Output, 'DelIpPort', $If);
	}
}
else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'InterfaceToAdd')) {
	$View->Controller($Output, 'AddIpPort', filter_input(INPUT_POST, 'InterfaceToAdd'));
}

$CustomFunc= 'PrintSquidIPPortForm';
require_once('../lib/conf.php');
?>
