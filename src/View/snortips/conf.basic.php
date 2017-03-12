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

/**
 * Displays alert keywords.
 */
function PrintKeywordsForm()
{
	global $View, $Class;
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Keywords').':' ?>
		</td>
		<td>
			<?php
			$View->Controller($keywords, 'GetKeywords');
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="Keywords[]" multiple style="width: 200px; height: 100px;">
					<?php
					foreach ($keywords as $key) {
						?>
						<option value="<?php echo $key ?>"><?php echo $key ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="KeywordToAdd" style="width: 200px;" maxlength="50"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php 
			PrintHelpBox(_HELPBOX2('These are keywords to match in alert messages. If alerts contain such words, the IP in that alert will be blocked.'));
			?>
		</td>
	</tr>
	<?php
}

if (filter_has_var(INPUT_POST, 'Delete')) {
	foreach ($_POST['Keywords'] as $Keyword) {
		$View->Controller($Output, 'DelKeyword', $Keyword);
	}
}
else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'KeywordToAdd')) {
	$View->Controller($Output, 'AddKeyword', filter_input(INPUT_POST, 'KeywordToAdd'));
}

$CustomFunc= 'PrintKeywordsForm';
require_once('../lib/conf.php');
?>
