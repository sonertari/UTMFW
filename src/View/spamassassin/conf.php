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

require_once('include.php');

/**
 * Displays SpamAssassin local.cf file selector.
 */
function PrintSetLanguageForm()
{
	global $View, $Class;
	
	$View->Controller($languages, 'GetLocales');
	if ($View->Controller($output, 'GetCurrentLocale')) {
		$currentlang= $output[0];
	}
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Report language').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<select name="LocalCfFile">
					<?php
					foreach ($languages as $lang) {
						$selected= $currentlang === $lang ? 'selected' : '';
						?>
						<option <?php echo $selected ?> value="<?php echo $lang ?>"><?php echo $lang ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" name="ApplyLocalCfFile" value="<?php echo _CONTROL('Apply') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php PrintHelpBox(_HELPBOX2('Here you can change the language used in the spam reports.')) ?>
		</td>
	</tr>
	<?php
}

if (filter_has_var(INPUT_POST, 'LocalCfFile')) {
	$View->Controller($Output, 'ChangeLocal', filter_input(INPUT_POST, 'LocalCfFile'));
}

$CustomFunc= 'PrintSetLanguageForm';

require_once('../lib/conf.php');
?>
