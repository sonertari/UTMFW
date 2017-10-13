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
 * Page header.
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

$PageActivated= FALSE;
if (isset($TopMenu)) {
	if (in_array($_SESSION['USER'], $Menu[$TopMenu]['Perms'])) {
		$_SESSION[$View->Module]['topmenu']= $TopMenu;
		$PageActivated= TRUE;
	}
}

CheckPageActivation($PageActivated);

$ModuleFiles= array();
$DirHandle= opendir($VIEW_PATH);
while (FALSE !== ($DirName= readdir($DirHandle))) {
	if (is_dir("$VIEW_PATH/$DirName")) {
		$ModuleFiles[]= $DirName;
	}
}
closedir($DirHandle);

define('ACTIVE_LI_STYLE', ' style="background: #01466b;"');
define('ACTIVE_A_STYLE', ' style="color: white;"');
?>
<table id="menu">
	<tr >
		<td nowrap>
			<div id="wrapper">
				<nav id="nav">
					<ul id="navigation">
						<li>
							<a class="menuwithimage" href="#">
								<span class="menuwithimage">UTMFW</span>
								<img class="menuwithimage" src="/images/menu.png" name="Menu" alt="Menu" align="absmiddle">
								<?php
								foreach ($UTMFW_MODULES as $Module => $ModuleConf) {
									if (strpos($_SERVER['PHP_SELF'], "/$Module/") !== FALSE && in_array($Module, $ModuleFiles) && in_array($_SESSION['USER'], $ModuleConf['Perms'])) {
										?>
										<span class="menuwithimage"><?php echo _($ModuleConf['Name']) ?></span>
										<?php
										break;
									}
								}
								?>
							</a>
							<ul>
								<?php
								foreach ($UTMFW_MODULES as $Module => $ModuleConf) {
									if (in_array($Module, $ModuleFiles) && in_array($_SESSION['USER'], $ModuleConf['Perms'])) {
										$ActiveLiStyle= '';
										$ActiveAStyle= '';
										if (strpos($_SERVER['PHP_SELF'], "/$Module/") !== FALSE) {
											$ActiveLiStyle= ACTIVE_LI_STYLE;
											$ActiveAStyle= ACTIVE_A_STYLE;
										}
										?>
										<li<?php echo $ActiveLiStyle ?>>
											<a href="<?php echo "/$Module/index.php" ?>"<?php echo $ActiveAStyle ?>><?php echo _($ModuleConf['Name']) ?></a>
										</li>
										<?php
									}
								}
								?>
							</ul>				
						</li>
						<?php
						foreach ($Menu as $TopMenuName => $TopMenuConf) {
							if (in_array($_SESSION['USER'], $TopMenuConf['Perms'])) {
								$ActiveLiStyle= '';
								$ActiveAStyle= '';
								if ($TopMenu == $TopMenuName) {
									$ActiveLiStyle= ACTIVE_LI_STYLE;
									$ActiveAStyle= ACTIVE_A_STYLE;
								}
								?>
								<li<?php echo $ActiveLiStyle ?>>
									<a href="<?php echo $TopMenuName ?>.php"<?php echo $ActiveAStyle ?>><?php echo _($TopMenuConf['Name']) ?></a>
									<?php
									if (isset($TopMenuConf['SubMenu'])) {
										?>
										<ul>
										<?php
										foreach ($TopMenuConf['SubMenu'] as $SubMenuName => $Caption) {
											$ActiveLiStyle= '';
											$ActiveAStyle= '';
											if (($TopMenu == $TopMenuName) && ($Submenu == $SubMenuName)) {
												$ActiveLiStyle= ACTIVE_LI_STYLE;
												$ActiveAStyle= ACTIVE_A_STYLE;
											}
											?>
											<li<?php echo $ActiveLiStyle ?>>
												<a href="<?php echo $TopMenuName ?>.php?submenu=<?php echo $SubMenuName ?>"<?php echo $ActiveAStyle ?>><?php echo _($Caption) ?></a>
											</li>
											<?php
										}
										?>
										</ul>
										<?php
									}
									?>
								</li>
								<?php
							}
						}
						?>
						<li>
							<a class="menuwithimage" href="#">
								<?php $_SESSION['Timeout']= time() + $SessionTimeout; ?>
								<span id="timeout"></span>
								<script language="javascript" type="text/javascript">
									<!--
									// Add one to session timeout start, to LogUserOut() after redirect below (it's PHP's task)
									// Otherwise session timeout restarts from max
									var timeout= <?php echo $_SESSION['Timeout'] - time() ?> + 1;
									function countdown()
									{
										if (timeout > 0) {
											timeout-= 1;
											min= Math.floor(timeout / 60);
											sec= timeout % 60;
											// Pad left
											if (sec.toString().length < 2) {
												sec= "0" + sec;
											}
											document.getElementById("timeout").innerHTML= min + ":" + sec;
										}
										else {
											// redirect
											window.location= "/index.php";
											return;
										}
										setTimeout("countdown()", 1000);
									}
									countdown();
									// -->
								</script>
								<img class="menuwithimage" src="/images/rightmenu.png" name="Right Menu" alt="Right Menu" align="absmiddle">
								<span class="menuwithimage"><?php echo exec('/bin/date "+%d.%m.%Y %H:%M"') ?></span>
							</a>
							<ul>
								<li>
									<a href="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF') ?>?logout"><?php echo _TITLE('Logout') ?> <?php echo $_SESSION['USER'].'@'.filter_input(INPUT_SERVER, 'REMOTE_ADDR') ?></a>
								</li>
								<li>
									<a href="/system/dashboard.php"><?php echo _('Dashboard') ?></a>
								</li>
								<li>
									<a href="/info/help.php"><?php echo _TITLE('Help') ?></a>
								</li>
								<li>
									<a href="/info/docs.php"><?php echo _TITLE('Documentation') ?></a>
								</li>
								<li>
									<a href="#"><?php echo _TITLE('Language') ?></a>
									<ul>
										<?php
										foreach ($LOCALES as $Locale => $Conf) {
											$ActiveLiStyle= '';
											$ActiveAStyle= '';
											if ($_SESSION['Locale'] == $Locale) {
												$ActiveLiStyle= ACTIVE_LI_STYLE;
												$ActiveAStyle= ACTIVE_A_STYLE;
											}
											if ($_SESSION['Locale'] !== 'en_EN') {
												$LocaleDisplayName= _($Conf['Name']).' ('.$Conf['Name'].')';
											}
											else {
												$LocaleDisplayName= _($Conf['Name']);
											}
											?>
											<li<?php echo $ActiveLiStyle ?>>
												<a href="<?php echo $_SERVER['PHP_SELF'] ?>?locale=<?php echo $Locale ?>"<?php echo $ActiveAStyle ?>><?php echo $LocaleDisplayName ?></a>
											</li>
											<?php
										}
										?>
									</ul>				
								</li>
							</ul>
						</li>
					</ul>
				</nav>
			</div>
		</td>
	</tr>
</table>
<?php
$InHelpRegion= TRUE;
PrintHelpWindow('', 'auto', 'PRINT');
$InHelpRegion= FALSE;
?>
