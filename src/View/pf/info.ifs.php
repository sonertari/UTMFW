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

require_once('pf.php');

$View->Controller($Output, 'GetPfIfsInfo');
$ifs= json_decode($Output[0], TRUE);

$Reload= TRUE;
require_once($VIEW_PATH . '/header.php');
?>
<table id="logline">
	<tr>
		<th rowspan="2" ><?php echo _('Name') ?></th>
		<th rowspan="2" ><?php echo _('States') ?></th>
		<th rowspan="2" ><?php echo _('Rules') ?></th>
		<th colspan="2" ><?php echo _('In4 Pass') ?></th>
		<th colspan="2" ><?php echo _('In4 Block') ?></th>
		<th colspan="2" ><?php echo _('Out4 Pass') ?></th>
		<th colspan="2" ><?php echo _('Out4 Block') ?></th>
		<th colspan="2" ><?php echo _('In6 Pass') ?></th>
		<th colspan="2" ><?php echo _('In6 Block') ?></th>
		<th colspan="2" ><?php echo _('Out6 Pass') ?></th>
		<th colspan="2" ><?php echo _('Out6 Block') ?></th>
		<th rowspan="2" ><?php echo _('Cleared') ?></th>
	</tr>
	<tr>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
		<th><?php echo _('Packets') ?></th>
		<th><?php echo _('Bytes') ?></th>
	</tr>
	<?php
	$linenum= 0;
	$totalLines= count($ifs);
	foreach ($ifs as $i) {
		$class= ($linenum++ % 2 == 0) ? 'evenline' : 'oddline';
		$lastLine= $linenum == $totalLines;
		?>
		<tr>
			<td class="center <?php echo $class ?><?php echo $lastLine ? ' lastLineFirstCell':'' ?>"><?php echo $i['name'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['states'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['rules'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in4PassPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in4PassBytes'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in4BlockPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in4BlockBytes'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out4PassPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out4PassBytes'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out4BlockPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out4BlockBytes'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in6PassPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in6PassBytes'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in6BlockPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['in6BlockBytes'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out6PassPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out6PassBytes'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out6BlockPackets'] ?></td>
			<td class="right <?php echo $class ?>"><?php echo $i['out6BlockBytes'] ?></td>
			<td class="<?php echo $class ?><?php echo $lastLine ? ' lastLineLastCell':'' ?>"><?php echo $i['cleared'] ?></td>
		</tr>
		<?php
	}
	?>
</table>
<?php
PrintHelpWindow(_HELPWINDOW('These are interface statistics reported by pf.'));
require_once($VIEW_PATH . '/footer.php');
?>
