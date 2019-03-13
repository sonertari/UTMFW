<?php
/*
 * Copyright (C) 2004-2019 Soner Tari
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
 * Exception and mime pages.
 */

/**
 * Displays a list of extensions and mime types defined and forms with components to change.
 *
 * @param string $list List name, selected submenu.
 */
function PrintFilterExtMimeForm($list)
{
	global $View;

	$titles= array(
		'exts'			=> _TITLE2('Ext'),
		'mimes'			=> _TITLE2('Mime'),
		'dm_exts'		=> _TITLE2('Ext'),
		'dm_mimes'		=> _TITLE2('Mime'),
		'virus_exts'	=> _TITLE2('Ext'),
		'virus_mimes'	=> _TITLE2('Mime'),
		);

	$title= $titles[$list];

	$lists= array(
		'exts'			=> array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'mimes'			=> array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			'banned'	=> array(
				'title' => _TITLE2('Blacklist'),
				'color' => 'gray',
				),
			),
		'dm_exts'		=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Managed extensions'),
				'color' => '#eee',
				),
			),
		'dm_mimes'		=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Managed mime types'),
				'color' => '#eee',
				),
			),
		'virus_exts'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			),
		'virus_mimes'	=>	array(
			'exception'	=> array(
				'title' => _TITLE2('Whitelist'),
				'color' => '#eee',
				),
			),
		);
	
	if (array_key_exists($list, $lists)) {
		foreach ($lists[$list] as $type => $conf) {
			if ($View->Controller($output, 'GetExtMimeList', $_SESSION[$View->Model]['ConfOpt'], $list, $type)) {
				echo $conf['title'].':';
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<table style="width: 600px;">
						<tr>
							<td style="background: <?php echo $conf['color'] ?>;">
								<?php
								$output= json_decode($output[0], TRUE);
								ksort($output);
								?>
								<table style="width: auto;">
									<tr>
										<td style="width: 0;">
											<?php
											echo _TITLE2('Enabled');
											?>
											<br />
											<select name="Items[]" multiple style="width: 250px; height: 250px;">
												<?php
												foreach ($output as $entry => $desc) {
													if ($desc['Enabled']) {
														$comment= trim(ltrim($desc['Comment'], '#'));
														$display= $comment !== '' ? "$entry ($comment)" : $entry;
														?>
														<option value="<?php echo $entry ?>" title="<?php echo $comment ?>"><?php echo $display ?></option>
														<?php
													}
												}
												?>
											</select>
										</td>
										<td class="center" style="width: 0;">
											<input type="submit" name=">>" value=">>"/>
											<br />
											<input type="submit" name="&lt&lt" value="&lt&lt"/>
											<br />
											<br />
											<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _NOTICE('Are you sure you want to delete the selected items?') ?>')"/>
										</td>
										<td style="width: 0;">
											<?php
											echo _TITLE2('Disabled');
											?>
											<br />
											<select name="Items[]" multiple style="width: 250px; height: 250px;">
												<?php
												foreach ($output as $entry => $desc) {
													if (!$desc['Enabled']) {
														$comment= trim(ltrim($desc['Comment'], '#'));
														$display= $comment !== '' ? "$entry ($comment)" : $entry;
														?>
														<option value="<?php echo $entry ?>" title="<?php echo $comment ?>"><?php echo $display ?></option>
														<?php
													}
												}
												?>
											</select>
										</td>
									</tr>
									<tr>
										<td id="extadd" colspan="3">
											<table id="extadd">
												<tr>
													<td class="title">
														<?php echo _($title).':' ?>
													</td>
													<td>
														<input type="text" name="ItemToAdd" style="width: 200px;" maxlength="50"/><br />
													</td>
												</tr>
												<tr>
													<td class="title">
														<?php echo _TITLE2('Desc').':' ?>
													</td>
													<td>
														<input type="text" name="CommentToAdd" style="width: 300px;" maxlength="100"/>
														<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>" />
													</td>
												</tr>
											</table>
										</td>
									</tr>
								</table>
							</td>
						</tr>
					</table>
					<input type="hidden" name="ListType" value=<?php echo $type ?> />
				</form>
				<?php
			}
		}
	}
	?>
	<?php
}

if (count($_POST)) {
	if (filter_has_var(INPUT_POST, '>>')) {
		foreach ($_POST['Items'] as $Ext) {
			$View->Controller($Output, 'DisableExtMime', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'), $Ext);
		}
	}
	else if (filter_has_var(INPUT_POST, '<<')) {
		foreach ($_POST['Items'] as $Ext) {
			$View->Controller($Output, 'EnableExtMime', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'), $Ext);
		}
	}
	else if (filter_has_var(INPUT_POST, 'Delete')) {
		foreach ($_POST['Items'] as $Ext) {
			$View->Controller($Output, 'DelExtMime', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'), $Ext);
		}
	}
	else if (filter_has_var(INPUT_POST, 'Add') && filter_has_var(INPUT_POST, 'ItemToAdd')) {
		$View->Controller($Output, 'AddExtMime', $_SESSION[$View->Model]['ConfOpt'], $Submenu, filter_input(INPUT_POST, 'ListType'),
				filter_input(INPUT_POST, 'ItemToAdd'), filter_input(INPUT_POST, 'CommentToAdd'));
	}
}

$View->SetSessionConfOpt();

require_once($VIEW_PATH.'/header.php');

if ($PrintGroupForm) {
	$View->PrintConfOptForm();
}
PrintFilterExtMimeForm($Submenu);
PrintHelpWindow($View->ConfHelpMsg."\n\n".$ListHelpMsg);

require_once($VIEW_PATH.'/footer.php');
?>
