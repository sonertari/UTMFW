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

$ServiceStatus= array();
if ($View->Controller($Output, 'GetServiceStatus')) {
	$ServiceStatus= json_decode($Output[0], TRUE);
}

const COLUMN_COUNT= 4;

// Also represents categories, so used for bundling too
$Status2Images= array(
	'C' => 'critical.png',
	'E' => 'error2.png',
	'W' => 'warning2.png',
	'R' => 'running.png',
	'S' => 'stop.png',
	'N' => 'noerror.png',
	);

$StatusTitles= array(
	'C' => _TITLE('Critical Error'),
	'E' => _TITLE('Error'),
	'W' => _TITLE('Warning'),
	'R' => _TITLE('Running'),
	'S' => _TITLE('Stopped'),
	'N' => _TITLE('No Error'),
	);

$ModuleNames= array(
	'snortinline' => 'snort',
	'clamd' => 'clamav',
	'freshclam' => 'clamav',
	'symon' => 'monitoring',
	'symux' => 'monitoring',
	'pmacct' => 'monitoring',
	);

$SubmenuNames= array(
	'system' => 'system',
	'pf' => 'pf',
	);

$Reload= TRUE;
require_once($VIEW_PATH.'/header.php');

$Critical= 0;
$Error= 0;
$Warning= 0;
foreach ($ServiceStatus as $Module => $StatusArray) {
	$Critical+= $ServiceStatus[$Module]['Critical'];
	$Error+= $ServiceStatus[$Module]['Error'];
	$Warning+= $ServiceStatus[$Module]['Warning'];
}
?>
<table id="dashboard" style="width: 600px;">
	<tr>
		<td>
			<div style="background-color: #e72626;">
				<table>
					<tr>
						<td class="count">
							<?php echo $Critical ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _('CRITICAL') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td>
			<div style="background-color: #ff802b;">
				<table>
					<tr>
						<td class="count">
							<?php echo $Error ?>
						</td>
					</tr>
					<tr>
						<td class="prio">
							<?php echo _('ERROR') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
		<td>
			<div style="background-color: #ffd323; color: black;">
				<table>
					<tr>
						<td class="count">
							<?php echo $Warning ?>
						</td>
					</tr>
					<tr>
						<td class="prio" style="border-top: 1px solid black;">
							<?php echo _('WARNING') ?>
						</td>
					</tr>
				</table>
			</div>
		</td>
	</tr>
</table>
<table style="width: 600px;">
	<tr>
		<td>
			<strong><?php echo _('Module Status').':' ?></strong>
		</td>
	</tr>
	<?php
	$Id= 0;
	foreach ($Status2Images as $Status => $Image) {
		$moduleCount= 0;
		foreach ($ModelsToStat as $Module => $Caption) {
			if (array_key_exists($Module, $ServiceStatus) &&
					($Status == $ServiceStatus[$Module]['Status'] || $Status == $ServiceStatus[$Module]['ErrorStatus'])) {

				$RunStatus= $ServiceStatus[$Module]['Status'];
				$ErrorStatus= $ServiceStatus[$Module]['ErrorStatus'];

				// XXX?
				if ($Status == $RunStatus) {
					$StatusImage= $Image;
					$ErrorStatusImage= $Status2Images[$ErrorStatus];
				}
				else if ($Status == $ErrorStatus) {
					$StatusImage= $Status2Images[$RunStatus];
					$ErrorStatusImage= $Image;
				}

				$Critical= $ServiceStatus[$Module]['Critical'];
				$Error= $ServiceStatus[$Module]['Error'];
				$Warning= $ServiceStatus[$Module]['Warning'];
				$Logs= $ServiceStatus[$Module]['Logs'];

				$ErrorCounts= array();
				if ($Critical) {
					$ErrorCounts[]= _('Critical').': '.$Critical;
				}
				if ($Error) {
					$ErrorCounts[]= _('Error').': '.$Error;
				}
				if ($Warning) {
					$ErrorCounts[]= _('Warning').': '.$Warning;
				}
				$ErrorCountsStr= implode(', ', $ErrorCounts);
				//$ErrorCountsStr= $ErrorCountsStr == '' ? _('No Errors'):$ErrorCountsStr;

				// XXX
				$ServiceStatus[$Module]['Status']= '';
				$ServiceStatus[$Module]['ErrorStatus']= '';

				if ($moduleCount % COLUMN_COUNT == 0) {
					?>
					<tr>
					<?php
				}

				$FormId= 'form'.$Id++;

				if (array_key_exists($Module, $ModuleNames)) {
					$ModuleName= $ModuleNames[$Module];
				} else {
					$ModuleName= $Module;
				}

				if (array_key_exists($Module, $SubmenuNames)) {
					$SubmenuName= $SubmenuNames[$Module];
				} else {
					$SubmenuName= 'info';
				}
				?>
				<td style="vertical-align: top;">
					<form id="<?php echo $FormId ?>" name="<?php echo $FormId ?>" action="<?php echo "/$ModuleName/info.php?submenu=$SubmenuName" ?>" method="post"></form>
					<div id="modulestatus" onclick="document.<?php echo $FormId ?>.submit()" style="cursor: pointer;">
						<table>
							<tr>
								<td style="height: 22px;width: 22px;">
									<img src="<?php echo $IMG_PATH.$StatusImage ?>" name="<?php echo $RunStatus ?>" alt="<?php echo $RunStatus ?>" title="<?php echo $StatusTitles[$RunStatus] ?>" style="height: 22px;width: 22px;" align="absmiddle">
								</td>
								<td style="height: 22px;width: 22px;">
									<img src="<?php echo $IMG_PATH.$ErrorStatusImage ?>" name="<?php echo $ErrorStatus ?>" alt="<?php echo $ErrorStatus ?>" title="<?php echo $StatusTitles[$ErrorStatus] ?>" style="height: 22px;width: 22px;" align="absmiddle">
								</td>
								<td style="padding-left: 2px;">
									<strong><?php echo _($Caption) ?></strong>
								</td>
							</tr>
							<tr>
								<td style="font-size: 90%; text-align: right;" colspan="3">
									<?php echo $ErrorCountsStr ?>
								</td>
							</tr>
						</table>
					</div>
				</td>
				<?php
				if (++$moduleCount % COLUMN_COUNT == 0) {
					?>
					</tr>
					<?php
				}
			}
		}
	}
	?>
</table>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
