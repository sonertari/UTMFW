<?php
/*
 * Copyright (C) 2004-2023 Soner Tari
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
 * Category pages.
 */

/**
 * Displays lists of categories and forms with components to change.
 *
 * All category lists are handled by this function.
 */
function PrintFilterCatForms($list)
{
	global $View;

	$listconf= array(
		'exception'	=> array(
			'title'	=> _TITLE2('Whitelist'),
			'color'	=> 'white',
			),
		'grey'	=> array(
			'title'	=> _TITLE2('Greylist'),
			'color'	=> '#eee',
			),
		'banned'	=> array(
			'title'	=> _TITLE2('Blacklist'),
			'color'	=> 'gray',
			),
		'weighted'	=> array(
			'title'	=> _TITLE2('Weighted phrase list'),
			'color'	=> '#fff8f1',
			),
		);

	$group= $_SESSION[$View->Model]['ConfOpt'];

	$catlists= array(
		'sites'		=> array('exception', 'grey', 'banned'),
		'urls'		=> array('exception', 'grey', 'banned'),
		'phrases'	=> array('exception', 'banned', 'weighted'),
		);
	
	foreach ($catlists[$list] as $type) {
		if ($View->Controller($cats, 'GetEnabledCats', $group, $list, $type)) {
			echo $listconf[$type]['title'].':';
			?>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<table style="width: auto; background: <?php echo $listconf[$type]['color'] ?>;">
					<tr>
						<td>
							<?php echo _TITLE2('Enabled') ?>
							<br />
							<select name="Cats[]" multiple style="width: 200px; height: 100px;">
								<?php
								for ($i = 0; $i < count($cats); $i++) {
									if (preg_match('/([^\/]*)\/([^_]*)_(.*)/', $cats[$i], $match)) {
										$title= $match[1].' ('.$match[3].')';
									}
									else {
										$title= dirname($cats[$i]);
									}
									?>
									<option value="<?php echo $cats[$i] ?>"><?php echo $title ?></option>
									<?php
								}
								?>
							</select>
							<input type="submit" name="Disable" value="<?php echo _CONTROL('Disable') ?>"/>
						</td>
						<td>
							<?php
							if ($View->Controller($cats, 'GetDisabledCats', $group, $list, $type)) {
								echo _TITLE2('Disabled');
								?>
								<br />
								<select name="Cats[]" multiple style="width: 200px; height: 100px;">
									<?php
									for ($i = 0; $i < count($cats); $i++) {
										if (preg_match('/([^\/]*)\/([^_]*)_(.*)/', $cats[$i], $match)) {
											$title= $match[1].' ('.$match[3].')';
										}
										else {
											$title= dirname($cats[$i]);
										}
										?>
										<option value="<?php echo $cats[$i] ?>"><?php echo $title ?></option>
										<?php
									}
									?>
								</select>
								<input type="submit" name="Enable" value="<?php echo _CONTROL('Enable') ?>"/>
								<?php
							}
							?>
						</td>
					</tr>
				</table>
				<input type="hidden" name="ListType" value=<?php echo $type ?> />
			</form>
			<?php
		}
	}
}

if (filter_has_var(INPUT_POST, 'Cats')) {
	foreach ($_POST['Cats'] as $CatSubcat) {
		$CatArray= explode('/', $CatSubcat, 3);
		$Cat= $CatArray[0];
		$Subcat= $CatArray[1];
		if (isset($CatArray[2])) {
			// shallalist has sub-subdirs now
			$Subcat.= '/'.$CatArray[2];
		}
		if (filter_has_var(INPUT_POST, 'Disable')) {
			$View->Controller($Output, 'TurnOffCats', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'), $Cat, $Subcat);
		}
		else if (filter_has_var(INPUT_POST, 'Enable')) {
			$View->Controller($Output, 'TurnOnCats', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'), $Cat, $Subcat);
		}
	}
}

$View->SetSessionConfOpt();

require_once($VIEW_PATH.'/header.php');
		
$View->PrintConfOptForm();
PrintFilterCatForms($Submenu);

PrintHelpWindow($View->ConfHelpMsg."\n\n".$ListHelpMsg.' '.$WeightedListHelpMsg);
require_once($VIEW_PATH.'/footer.php');
?>
