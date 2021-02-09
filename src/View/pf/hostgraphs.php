<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
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

$PnrgImgPath= '../pmacct/pnrg/spool/';

if (filter_has_var(INPUT_POST, 'IP')) {
	$Ip= filter_input(INPUT_POST, 'IP');
}

require_once($VIEW_PATH.'/header.php');
?>
<table>
	<tr>
		<td>
			<form method="post" id="ipform" name="ipform" action="<?php echo $_SERVER['REQUEST_URI'] ?>">
				<strong><?php echo _TITLE2('Host IP').': ' ?></strong>
				<select name="IP" onchange="document.ipform.submit()">
				<?php
				$View->Controller($Output, 'GetHostGraphsList', $Ip);
				foreach ($Output as $Host) {
					if (!isset($Ip)) {
						$Ip= $Host;
					}

					$Selected= $Host === $Ip ? ' selected' : '';
					?>
					<option value="<?php echo $Host ?>"<?php echo $Selected ?>><?php echo $Host ?></option>
					<?php
				}
				?>
				</select>
			</form>
		</td>
		<td style="width: 50%;">
			<?php PrintHelpBox(_HELPBOX2('Select an IP to view its network usage graphs.')) ?>
		</td>
	</tr>
</table>
<?php
if ($Ip) {
	/// No need for yearly graph.
	$Exts= array('.1hr.gif', '.8hr.gif', '.1day.gif', '.1wk.gif', '.1mon.gif');
	foreach ($Exts as $Ext) {
		?>
		<p>
		<img src="<?php echo $PnrgImgPath.$Ip.$Ext ?>" name="IP Graph" alt="IP Graph" border="0">
		</p>
		<?php
	}
}

PrintHelpWindow($View->GraphHelpMsg);
require_once($VIEW_PATH.'/footer.php');
?>
