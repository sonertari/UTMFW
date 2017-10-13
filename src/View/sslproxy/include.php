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
    'sslproxy' => array(
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Prio',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('CRITICAL:', 'ERROR:', 'EXPIRED:'),
                'yellow' => array('WARNING:', 'IDLE:'),
                'green' => array('CONN:'),
        		),
    		),
		),
	'idleconns' => array(
		'Fields' => array(
            'Date',
            'Time',
			'ThreadIdx',
			'ConnIdx',
			'SrcAddr',
			'DstAddr',
			'IdleTime',
			'Duration',
			),
		),
	);

class Sslproxy extends View
{
	public $Model= 'sslproxy';
	public $Layout= 'sslproxy';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('SSL Proxy');

		$this->LogsHelpMsg= _HELPWINDOW('The SSL proxy takes 5 different kinds of logs: (1) STATS for periodic statistics per thread, (2) CONN for connection details at establisment time, (3) IDLE for slow connections, (4) EXPIRED for timed-out connections which are closed by the SSL proxy, and (5) CRITICAL, ERROR, WARNING, or INFO messages.');
		
		$this->GraphHelpMsg= _HELPWINDOW('The SSL proxy is an event-driven multi-threaded program.');
		
		$this->ConfHelpMsg= _HELPWINDOW('You should restart the SSL proxy for the changes to take effect.');
	
		$this->Config = array(
			'CACert' => array(
				'title' => _TITLE2('CA Cert'),
				'info' => _HELPBOX2('Use CA cert (and key) to sign forged certs.'),
				),
			'CAKey' => array(
				'title' => _TITLE2('CA Key'),
				'info' => _HELPBOX2('Use CA key (and cert) to sign forged certs.'),
				),
			'ConnIdleTimeout' => array(
				'title' => _TITLE2('Connection Idle Timeout'),
				'info' => _HELPBOX2('Close connections after this many seconds of idle time.'),
				),
			'ExpiredConnCheckPeriod' => array(
				'title' => _TITLE2('Expired Connection Check Period'),
				'info' => _HELPBOX2('Check for expired connections every this many seconds.'),
				),
			'SSLShutdownRetryDelay' => array(
				'title' => _TITLE2('SSL Shutdown Retry Delay'),
				'info' => _HELPBOX2('Retry to shut ssl conns down after this many micro seconds. Increasing this delay may avoid dirty shutdowns on slow connections, but increases resource usage, such as file desriptors and memory.'),
				),
			'LogStats' => array(
				'title' => _TITLE2('Log Statistics'),
				'info' => _HELPBOX2('Log statistics to syslog.'),
				),
			'StatsPeriod' => array(
				'title' => _TITLE2('Statistics Period'),
				'info' => _HELPBOX2('Log statistics every this many ExpiredConnCheckPeriod periods.'),
				),
			'RemoveHTTPAcceptEncoding' => array(
				'title' => _TITLE2('Remove HTTP Accept-Encoding'),
				'info' => _HELPBOX2('Remove HTTP header line for Accept-Encoding. HTTP compression and encodings are disabled to make the content logs more readable. You can turn this off if content logging is disabled.'),
				),
			'RemoveHTTPReferer' => array(
				'title' => _TITLE2('Remove HTTP Referer'),
				'info' => _HELPBOX2('Remove HTTP header line for Referer. The OWASP considers it a vulnerabilty, and it may cause redirection errors with SSLproxy.'),
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
		$class= $this->getLogLineClass($cols['Log']);
		PrintLogCols($linenum, $cols, $lastlinenum, $class);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= wordwrap($cols['Log'], 150, '<br />', TRUE);
	}
	
	function PrintStatsMaxValues($interval)
	{
		$key2Titles = array(
			'Load' => _TITLE2('Connections'),
			'UploadKB' => _TITLE2('Total Upload (KB)'),
			'DownloadKB' => _TITLE2('Total Download (KB)'),
			'CreateTime' => _TITLE2('Connection Duration'),
			'AccessTime' => _TITLE2('Connection Idle Time'),
			'Fd' => _TITLE2('File Descriptors'),
			);
		?>
		<table id="logline" style="width: 10%;">
			<tr>
				<th></th>
				<th nowrap><?php echo _('Max Values') ?></th>
			</tr>
		<?php
		$this->Controller($Output, 'GetMaxStats', $interval);
		$maxValues= json_decode($Output[0], TRUE);
		
		foreach ($key2Titles as $key => $title) {
			?>
			<tr>
				<th style="text-align: right;" nowrap>
					<?php echo $title ?>
				</th>
				<td style="text-align: center;">
					<?php echo $maxValues[$key] ?>
				</td>
			</tr>
			<?php
		}
		?>
		</table>
		<?php
	}

	function PrintIdleConns($interval)
	{
		?>
		<table id="logline" class="center">
			<?php
			PrintTableHeaders('idleconns');

			if ($this->Controller($Output, 'GetIdleConns', $interval)) {
				$conns= json_decode($Output[0], TRUE);
				$lineCount= 1;
				foreach ($conns as $cols) {
					PrintLogCols($lineCount++, $cols, '', 'idleconns');
				}
			}
			?>
		</table>
		<?php
	}
}

$View= new Sslproxy();

/**
 * Prints proxy specifications and download CA cert form.
 */
function PrintProxySpecsDownloadCACertForm()
{
	global $View, $Class;
	
	$View->Controller($output, 'GetCACertFileName');
	$certFile= $output[0];
	?>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Proxy Specifications').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input style="display:none;" type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
				<select name="Specs[]" multiple style="width: 200px; height: 100px;">
					<?php
					if ($View->Controller($output, 'GetSpecs')) {
						foreach ($output as $mirror) {
							?>
							<option value="<?php echo $mirror ?>"><?php echo $mirror ?></option>
							<?php
						}
					}
					?>
				</select>
				<input type="submit" name="Delete" value="<?php echo _CONTROL('Delete') ?>"/><br />
				<input type="text" name="SpecsToAdd" style="width: 200px;" maxlength="200"/>
				<input type="submit" name="Add" value="<?php echo _CONTROL('Add') ?>"/>
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2("Proxy specification format is type listenaddr+port up:utmport."));
			?>
		</td>
	</tr>
	<tr class="<?php echo $Class ?>">
		<td class="title">
			<?php echo _TITLE2('Download CA Cert').':' ?>
		</td>
		<td>
			<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
				<input type="submit" name="Download" value="<?php echo _CONTROL('Download') ?>"/>
				<input type="hidden" name="LogFile" value="<?php echo $certFile ?>" />
			</form>
		</td>
		<td class="none">
			<?php
			PrintHelpBox(_HELPBOX2("Download the CA cert file to install on client web browsers and mail programs."));
			?>
		</td>
	</tr>
	<?php
}
?>
