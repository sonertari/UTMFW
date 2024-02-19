<?php
/*
 * Copyright (C) 2004-2024 Soner Tari
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
 * Site and url lists.
 */

/**
 * Displays exception, grey, and banned list boxes and components for modification.
 *
 * @param string $list List name, selected submenu.
 */
function PrintFilterConfForms($list)
{
	global $View;

	$lists= array(
		'sites'			=> array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			'grey'		=> array(
				'title' => _TITLE2('Greylist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'urls'			=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			'grey'		=> array(
				'title' => _TITLE2('Greylist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'virus_sites'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			),
		'virus_urls'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => 'white',
				),
			),
		);
	?>
	<table style="width: auto;">
	<?php
	foreach ($lists[$list] as $type => $conf) {
		if ($View->Controller($items, 'GetList', $_SESSION[$View->Model]['ConfOpt'], $list, $type)) {
			?>
			<tr>
				<td style="background: <?php echo $conf['color'] ?>;">
					<?php echo $conf['title'].':' ?>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
						<select name="SitesToDelete[]" multiple style="width: 400px; height: 100px;">
						<?php
						for ($i = 0; $i < count($items); $i++){
							?>
							<option value="<?php echo $items[$i] ?>"><?php echo $items[$i] ?></option>
							<?php
						}
						?>
						</select>
						<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
						<input type="text" name="SiteToAdd" style="width: 400px;" maxlength="200"/>
						<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/><br />
						<input type="hidden" name="ListType" value=<?php echo $type ?> />
					</form>
				</td>
			</tr>
			<?php
		}
	}
	?>
	</table>
	<?php
}

if (filter_has_var(INPUT_POST, 'Delete')) {
		foreach ($_POST['SitesToDelete'] as $Site) {
			$View->Controller($Output, 'DelSiteUrl', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'), $Site);
		}
}
else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'SiteToAdd')) {
	$View->Controller($Output, 'AddSiteUrl', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'), filter_input(INPUT_POST, 'SiteToAdd'));
}

$View->SetSessionConfOpt();

require_once($VIEW_PATH.'/header.php');
		
if ($PrintGroupForm) {
	$View->PrintConfOptForm();
}
PrintFilterConfForms($Submenu);

PrintHelpWindow($View->ConfHelpMsg."\n\n"._HELPWINDOW('Group users are allowed unrestricted access to Whitelisted sites or urls. Whitelisted entries are not checked for viruses either. Therefore, Whitelist should be used with caution. In contrast to Whitelist, Greylisted entries are checked for viruses. If possible, Greylist should be preferred over Whitelist. Access to blacklisted entries is denied.'));
require_once($VIEW_PATH.'/footer.php');
?>
