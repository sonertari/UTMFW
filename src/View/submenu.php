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

/** @file
 * Prints module submenu, if any.
 */

if (isset($Menu[$TopMenu]['SubMenu']) && isset($Submenu)) {
	?>
	<table id="topmenu">
		<tr>
			<td id="submenu">
				<ul id="tabs">
				<?php
				foreach ($Menu[$TopMenu]['SubMenu'] as $Name => $Caption) {
					?>
					<li<?php echo ($Submenu == $Name ? ' class="active"' : '') ?>><a href="?submenu=<?php echo $Name ?>"><?php echo _($Caption) ?></a></li>
					<?php
				}
				?>
				</ul>
			</td>
		</tr>
	</table>
	<?php
}
?>
