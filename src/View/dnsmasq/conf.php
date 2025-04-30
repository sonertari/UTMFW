<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
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

if (filter_has_var(INPUT_POST, 'ListenOn')) {
	$View->Controller($Output, 'SetListenOn', filter_input(INPUT_POST, 'ListenOn'));
}

require_once($VIEW_PATH.'/header.php');
?>
<table id="nvp">
	<?php
	if ($View->Controller($Output, 'GetListenOn')) {
		$ListenOn= $Output[0];
		?>
		<tr class="oddline">
			<td class="title">
				<?php echo _TITLE2('Listen on').':' ?>
			</td>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<select name="ListenOn" style="width: 100px;">
						<option selected value="<?php echo $ListenOn ?>"><?php echo $ListenOn ?></option>
						<?php
						if ($View->Controller($output, 'GetPhyIfs')) {
							foreach ($output as $if) {
								if ($ListenOn !== $if) {
									?>
									<option value="<?php echo $if ?>"><?php echo $if ?></option>
									<?php
								}
							}
						}
						?>
					</select>
					<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
				</form>
			</td>
			<td class="none">
				<?php PrintHelpBox(_HELPBOX2('The interface that the DNS forwarder binds to listen for queries.')) ?>
			</td>
		</tr>
		<?php
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('You should restart the DNS forwarder for the changes to take effect.'));
require_once($VIEW_PATH.'/footer.php');
?>
