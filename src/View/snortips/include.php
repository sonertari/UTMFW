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

require_once('../lib/vars.php');

$Menu = array(
	'info' => array(
		'Name' => _MENU('Info'),
		'Perms' => $ALL_USERS,
		),
	'stats' => array(
		'Name' => _MENU('Statistics'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'general' => _MENU('General'),
			'daily' => _MENU('Daily'),
			'hourly' => _MENU('Hourly'),
			'live' => _MENU('Live'),
			),
		),
	'graphs' => array(
		'Name' => _MENU('Graphs'),
		'Perms' => $ALL_USERS,
		),
	'logs' => array(
		'Name' => _MENU('Logs'),
		'Perms' => $ALL_USERS,
		'SubMenu' => array(
			'archives' => _MENU('Archives'),
			'live' => _MENU('Live'),
			),
		),
	'conf' => array(
		'Name' => _MENU('Config'),
		'Perms' => $ADMIN,
		),
	);

$LogConf = array(
	'snortips' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				'red' => array('Blocking', 'already blocked', 'Exiting'),
				'green' => array('Unblocking'),
				),
			),
		),
	);

class Snortips extends View
{
	public $Model= 'snortips';
	public $Layout= 'snortips';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('Passive Intrusion Prevention');

		$this->LogsHelpMsg= _HELPWINDOW("Here are the definitions of a few terms used in the logs:<ul class='nomargin'><li class='nomargin'>Blocking a host means adding it to IPS pf table as blocked</li><li class='nomargin'>Unblocking means deleting a blocked host from the table</li><li class='nomargin'>Deblocking means adding a whitelisted host to the table</li><li class='nomargin'>Undeblocking means deleting a whitelisted host from the table</li></ul>
		Failure to block a host does not necessarily indicate an error; the host may be in the table already.");
		
		$this->GraphHelpMsg= _HELPWINDOW('SnortIPS is a perl process. These graphs display data from all perl processes.');
		
		$this->ConfHelpMsg= _HELPWINDOW('The IDS produces many alerts. Some alerts may be more serious than others, hence most alerts have priorities. You can configure the IPS to block only alerts at a certain priority and up. Each alert also contains log and classification text. You can add keywords to match within such text. The IP in the matching alert is blocked. If the alert does not contain an IP address, no action is taken.

Intrusion alerts produced by the IDS are guesses, hence there may be false positives or wrong alarms. Since the IPS depends on alerts produced by the IDS, you may want to make sure some IP addresses are never blocked accidentally, such as the internal and external IP addresses of the system, or the IP address of the computer you use to access this web administration interface. You can enter individual IPs or network addresses. IP and network addresses can overlap. For example, you can blacklist 10.0.0.0/24, but whitelist 10.0.0.1.');
	
		$this->Config = array(
			'Priority' => array(
				'title' => _TITLE2('Priority'),
				'info' => _HELPBOX2('This is the priority in the alerts. Alerts at this severity and up will be used to block IPs.'),
				),
			'AndPrioKey' => array(
				'title' => _TITLE2('And Priority and Keyword'),
				'info' => _HELPBOX2('If yes, both priority and keyword should match to block IPs.'),
				),
			'BlockDuration' => array(
				'title' => _TITLE2('Block Duration'),
				'info' => _HELPBOX2('Temporary block duration in seconds on each alert.'),
				),
			'MaxBlockDuration' => array(
				'title' => _TITLE2('Max Block Duration'),
				'info' => _HELPBOX2('Total of block extensions cannot be higher than this value.'),
				),
			);
	}

	/**
	 * Displays parsed log line.
	 *
	 * @param array $cols Columns parsed.
	 * @param int $linenum Line number to print as the first column.
	 * @param array $lastlinenum Last line number, used to detect the last line
	 */
	function PrintLogLine($cols, $linenum, $lastlinenum)
	{
		$class= $this->getLogLineClass($cols['Log'], $cols);
		PrintLogCols($linenum, $cols, $lastlinenum, $class);
	}

	/**
	 * Displays white or black listed IPs form.
	 *
	 * @param string $list Name of white or black list
	 * @param string $title Title to display
	 * @param string $helpmsg Help string
	 */
	function PrintListedIPsForm($list, $title, $helpmsg)
	{
		global $Class, $Row;

		$Class= $Row++ % 2 == 0 ? 'evenline' : 'oddline';
		?>
		<tr class="<?php echo $Class ?>">
			<td class="title">
				<?php echo $title.':' ?>
			</td>
			<td>
				<?php
				$cmd= $list == 'whitelist' ? 'GetAllowedIps' : 'GetRestrictedIps';
				$this->Controller($ips, $cmd);
				/// @attention The first invisible Add button is identical to the second
				/// to make Add the default form action, so that we save 3 html lines.
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<select name="IPs[]" multiple style="width: 200px; height: 100px;">
						<?php
						foreach ($ips as $ip) {
							?>
							<option value="<?php echo $ip ?>"><?php echo $ip ?></option>
							<?php
						}
						?>
					</select>
					<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
					<input type="text" name="IPToAdd" style="width: 200px;" maxlength="18"/>
					<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
					<input type="hidden" name="List" value="<?php echo $list ?>" />
				</form>
			</td>
			<td class="none">
				<?php
				PrintHelpBox($helpmsg);
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Displays a list of blocked, blacklisted, or whitelisted IPs.
	 *
	 * Also allows the user to add or delete blocked IPs.
	 */
	function PrintBlockedIPsForm()
	{
		global $ADMIN;
		?>
		<td>
			<?php
			/// @todo Do not run this command if SnortIPS is not running
			if ($this->Controller($output, 'GetInfo')) {
				$info= json_decode($output[0], TRUE);

				$blocked= count($info['Blocked']);
				$whitelisted= count($info['Whitelisted']);
				$blacklisted= count($info['Blacklisted']);
				$managed= $whitelisted + $blocked + $blacklisted;

				ProcessStartLine($startLine);
				UpdateLogsPageSessionVars($linesPerPage, $searchRegExp, $searchNeedle);

				ProcessNavigationButtons($linesPerPage, $managed, $startLine, $end);
				?>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<table id="nvp">
						<tr class="oddline">
							<td>
								<?php echo _TITLE('Line').':' ?>
								<input type="text" name="StartLine" style="width: 50px;" maxlength="10" value="<?php echo $startLine + 1 ?>" />/<?php echo $managed ?>
							</td>
							<td>
								<?php echo _TITLE('Lines per page').':' ?>
								<input type="text" name="LinesPerPage" style="width: 30px;" maxlength="3" value="<?php echo $linesPerPage ?>" />
								<input type="submit" name="Apply" value="<?php echo _CONTROL('Apply') ?>"/>
							</td>
						</tr>
						<tr class="evenline">
							<td class="center" colspan="<?php echo $showDateTimeSelect ? 2:3 ?>">
								<input type="submit" name="First" value="<?php echo _CONTROL('<< First') ?>"/>
								<input type="submit" name="Previous" value="<?php echo _CONTROL('< Previous') ?>"/>
								<input type="submit" name="Next" value="<?php echo _CONTROL('Next >') ?>"/>
								<input type="submit" name="Last" value="<?php echo _CONTROL('Last >>') ?>"/>
							</td>
						</tr>
					</table>
				</form>
				<?php
				echo $managed.' '._TITLE2('managed').': '.$whitelisted.' '._TITLE2('whitelisted').', '.$blocked.' '._TITLE2('blocked').', '.$blacklisted.' '._TITLE2('blacklisted');
				$start= $end - $linesPerPage;
				$line= 0;
				$lineCount= 0;
				?>
				<table id="ipsmanaged">
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<tr>
							<th><?php echo _('Line') ?></th>
							<th><?php echo _TITLE2('Host') ?></th>
							<th><?php echo _TITLE2('Time to expire (secs)') ?></th>
						</tr>
						<?php
						foreach ($info['Whitelisted'] as $host) {
							if ($line >= $start && $lineCount < $linesPerPage) {
								?>
								<tr class="whitelisted">
									<td>
										<?php echo $line + 1 ?>
									</td>
									<td>
										<?php echo $host ?>
									</td>
									<td>
										<?php echo _TITLE2('Whitelisted') ?>
									</td>
								</tr>
								<?php
								$lineCount++;
							}
							$line++;
						}

						foreach ($info['Blacklisted'] as $host) {
							if ($line >= $start && $lineCount < $linesPerPage) {
								?>
								<tr class="blacklisted">
									<td>
										<?php echo $line + 1 ?>
									</td>
									<td>
										<?php echo $host ?>
									</td>
									<td>
										<?php echo _TITLE2('Blacklisted') ?>
									</td>
								</tr>
								<?php
								$lineCount++;
							}
							$line++;
						}

						foreach ($info['Blocked'] as $host => $time) {
							if ($line >= $start && $lineCount < $linesPerPage) {
								?>
								<tr class="blocked">
									<td>
										<?php echo $line + 1 ?>
									</td>
									<td>
										<?php
										/// Only admin can delete/add hosts
										if (in_array($_SESSION['USER'], $ADMIN)) {
											?>
											<input name="ItemsToDelete[]" type="checkbox" value="<?php echo $host ?>"/><?php echo $host ?>
											<?php
										}
										else {
											?>
											<?php echo $host ?>
											<?php
										}
										?>
									</td>
									<td>
										<?php echo $time ?>
									</td>
								</tr>
								<?php
								$lineCount++;
							}
							$line++;
						}
						/// Only admin can delete/add hosts
						if (in_array($_SESSION['USER'], $ADMIN)) {
							?>
							<tr>
								<td>
									<input type="submit" name="Unblock" value="<?php echo _CONTROL('Unblock') ?>"/><br />
									<?php echo _TITLE2('Unblock selected') ?>
								</td>
								<td>
									<input type="submit" name="UnblockAll" value="<?php echo _CONTROL('Unblock All') ?>"/><br />
									<?php echo _TITLE2('Unblock all blocked entries') ?>
								</td>
							</tr>
							<?php
						}
						?>
					</form>
				</table>
				<?php
				/// Only admin can delete/add hosts
				if (in_array($_SESSION['USER'], $ADMIN)) {
					?>
					<br />
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<?php echo _TITLE2('IP or Net').':' ?>
						<input type="text" name="ItemToAdd" style="width: 100px;" maxlength="20"/>
						<?php echo _TITLE2('Time to expire').':' ?>
						<input type="text" name="TimeToAdd" style="width: 100px;" maxlength="20"/>
						<?php echo _TITLE('secs') ?>
						<input type="submit" name="Block" value="<?php echo _CONTROL('Block') ?>" />
					</form>
					<?php
				}
			}
			?>
		</td>
		<?php
	}
	
	function FormatDate($date)
	{
		global $MonthNames;

		return $MonthNames[$date['Month']].' '.sprintf('%02d', $date['Day']);
	}
}

$View= new Snortips();
?>
