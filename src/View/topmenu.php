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
 * Prints top menu.
 */

require_once('lib/vars.php');

if (!isset($_SESSION[$View->Model]['ReloadRate'])) {
	$_SESSION[$View->Model]['ReloadRate']= $DefaultReloadRate;
}

if ($Reload) {
	HTMLHeader(NULL, $_SESSION[$View->Model]['ReloadRate']);
}
else {
	HTMLHeader();
}
?>
<tr id="top">
	<td class="center">
		<b><?php echo _MENU('UTM FIREWALL') ?></b>
	</td>
	<td>
		<table class="topmenu">
			<tr>
				<td>
					<?php
					$PageActivated= FALSE;
					if (in_array($_SESSION['USER'], $Menu[$TopMenu]['Perms'])) {
						$_SESSION[$View->Module]['topmenu']= $TopMenu;
						$PageActivated= TRUE;
					}

					if (isset($TopMenu)) {
						?>
						<form method="post" id="languageForm" name="languageForm" action="<?php echo preg_replace("/&/", "&amp;", $_SERVER['REQUEST_URI'], -1) ?>">
							<div id="menu">
								<ul id="tabs">
								<?php
								foreach ($Menu as $Name => $TopMenuConf) {
									if (in_array($_SESSION['USER'], $TopMenuConf['Perms'])) {
										?>
										<li<?php echo ($TopMenu == $Name ? ' class="active"' : '') ?>><a href="<?php echo $Name ?>.php"><?php echo _($TopMenuConf['Name']) ?></a></li>
										<?php
									}
								}
								?>
								</ul>

								<?php echo _MENU('Language').': ' ?>
								<select id="Locale" name="Locale" onchange="document.languageForm.submit()">
								<?php
								foreach ($LOCALES as $Locale => $Conf) {
									$Selected= ($_SESSION['Locale'] == $Locale) ? 'selected' : '';
									if ($_SESSION['Locale'] !== 'en_EN') {
										$LocaleDisplayName= _($Conf['Name']).' ('.$Conf['Name'].')';
									}
									else {
										$LocaleDisplayName= _($Conf['Name']);
									}
									?>
									<option value="<?php echo $Locale ?>" <?php echo $Selected ?>><?php echo $LocaleDisplayName ?></option>
									<?php
								}
								?>
								</select>
							</div>
						</form>
						<?php
					}
					?>
					<div id="menuunderline">
					</div>
				</td>
			</tr>
		</table>
	</td>
</tr>
