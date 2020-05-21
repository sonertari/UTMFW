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

require_once('include.php');

function PrintKeywordsForm()
{
	global $View, $Class, $Row;

	$Row= 0;
	$View->PrintListedIPsForm('whitelist', _TITLE2('Whitelisted'), _HELPBOX2('Whitelisted IPs are never blocked, even if IDS produces alerts for them. Make sure you have internal and external IP addresses of the system whitelisted here. Otherwise, false positives may block access to the system from the network.'));
	$View->PrintListedIPsForm('blacklist', _TITLE2('Blacklisted'), _HELPBOX2('Blacklisted IPs are always blocked.'));

	$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
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

if (filter_has_var(INPUT_POST, 'Delete') && filter_has_var(INPUT_POST, 'IPs')) {
	$View->Controller($Output, 'DelIPFromList', filter_input(INPUT_POST, 'List'), json_encode($_POST['IPs']));
}
else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'IPToAdd')) {
	$View->Controller($Output, 'AddIPToList', filter_input(INPUT_POST, 'List'), filter_input(INPUT_POST, 'IPToAdd'));
}

if (filter_has_var(INPUT_POST, 'Delete') && filter_has_var(INPUT_POST, 'Keywords')) {
	foreach ($_POST['Keywords'] as $Keyword) {
		$View->Controller($Output, 'DelKeyword', $Keyword);
	}
}
else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'KeywordToAdd')) {
	$View->Controller($Output, 'AddKeyword', filter_input(INPUT_POST, 'KeywordToAdd'));
}

$ReloadConfig= TRUE;

$CustomFunc= 'PrintKeywordsForm';
require_once('../lib/conf.php');
?>
