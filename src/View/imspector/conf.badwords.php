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
 * Displays badwords list.
 */
function PrintBadwordsForm()
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Badwords').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="BadwordsToDelete[]" multiple style="width: 200px; height: 200px;">
					<?php
					if ($View->Controller($output, 'GetBadwords')) {
						foreach ($output as $badword) {
							?>
							<option value="<?php echo $badword ?>"><?php echo $badword ?></option>
							<?php
						}
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="BadwordToAdd" style="width: 200px;" maxlength="50"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/><br />
			</form>
		</td>
	</tr>
	<?php
}

if (filter_has_var(INPUT_POST, 'Delete')) {
	foreach ($_POST['BadwordsToDelete'] as $Badword) {
		$View->Controller($Output, 'DelBadword', $Badword);
	}
}
else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'BadwordToAdd')) {
	$View->Controller($Output, 'AddBadword', filter_input(INPUT_POST, 'BadwordToAdd'));
}

$CustomFunc= 'PrintBadwordsForm';
require_once('../lib/conf.php');
?>
