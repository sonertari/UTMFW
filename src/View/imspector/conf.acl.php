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

/**
 * Displays Access Control List.
 */
function PrintAclForm()
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('ACL').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="SelectedAcls[]" multiple style="width: 300px; height: 200px;">
					<?php
					if ($View->Controller($output, 'GetAcl')) {
						foreach ($output as $acl) {
							if (filter_has_var(INPUT_POST, 'SelectedAcls') && in_array($acl, $_POST['SelectedAcls'])) {
								$selected= ' selected';
							}
							else {
								$selected= '';
							}
							?>
							<option value="<?php echo $acl ?>" title="<?php echo $acl ?>"<?php echo $selected ?>><?php echo $acl ?></option>
							<?php
						}
					}
					?>
				</select>
				<br />
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/>
				<input type="submit" name="MoveUp" value="<?php echo _CONTROL('Move Up') ?>"/>
				<input type="submit" name="MoveDown" value="<?php echo _CONTROL('Move Down') ?>"/><br />
				<br />
				<select name="Action" style="width: 100px;">
					<option value="allow">allow</option>
					<option value="deny">deny</option>
				</select>
				<input type="text" name="List" style="width: 200px;" maxlength="200"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/><br />
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2('List format is:
allow|deny localid|all [groupchat|remoteid1 ... remoteidN]

Order of the access rules is important. For example, if deny all is the first rule, all access is effectively blocked, so make sure the deny all rule, if any, is at the bottom.'));
			?>
		</td>
	</tr>
	<?php
}

if (count($_POST)) {
	if (filter_has_var(INPUT_POST, 'Delete')) {
		foreach ($_POST['SelectedAcls'] as $Acl) {
			$View->Controller($Output, 'DelAcl', $Acl);
		}
	}
	else if (filter_has_var(INPUT_POST, 'MoveUp')) {
		foreach ($_POST['SelectedAcls'] as $Acl) {
			$View->Controller($Output, 'MoveAclUp', $Acl);
		}
	}
	else if (filter_has_var(INPUT_POST, 'MoveDown')) {
		$SelectedAcls= $_POST['SelectedAcls'];
		for ($i= count($SelectedAcls) - 1; $i >= 0; $i--) {
			$View->Controller($Output, 'MoveAclDown', $SelectedAcls[$i]);
		}
	}
	else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'List')) {
		$View->Controller($Output, 'AddAcl', filter_input(INPUT_POST, 'Action').' '.filter_input(INPUT_POST, 'List'));
	}
}

$CustomFunc= 'PrintAclForm';
require_once('../lib/conf.php');
?>
