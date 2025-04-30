#!/usr/bin/env php
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

/** @file
 * Gets module statuses and sends push notifications.
 */

/// This is a command line tool, should never be requested on the web interface.
if (isset($_SERVER['SERVER_ADDR'])) {
	header('Location: /index.php');
	exit(1);
}

// chdir is for libraries
chdir(dirname(__FILE__));

$ROOT= dirname(dirname(__FILE__));
$VIEW_PATH= $ROOT.'/View/';

require_once($ROOT.'/lib/setup.php');
require_once($ROOT.'/lib/defs.php');
require_once($SRC_ROOT.'/lib/lib.php');

require_once($VIEW_PATH.'/lib/libauth.php');
require_once($VIEW_PATH.'/lib/view.php');

$StatusText= array(
	'R' => _('running'),
	'S' => _('stopped'),
	);

$Prios= array(
	'Critical' => array('EMERGENCY', 'ALERT', 'CRITICAL', 'emergency', 'alert', 'critical'),
	'Error' => array('ERROR', 'error'),
	'Warning' => array('WARNING', 'NOTICE', 'warning', 'notice'),
	);

/**
 * Satisfies Controller.
 * 
 * This is due to common code used by all. Otherwise, the Controller cannot display help windows.
 */
function PrintHelpWindow($msg, $width= 'auto', $type= 'INFO')
{
	$msg= preg_replace('?<br\s*(|/)>?', ' ', $msg);
	echo "$type: $msg\n";
}

function Notify($title, $body, $data)
{
	global $NotifierHost, $NotifierAPIKey, $NotifierUsers, $NotifierSSLVerifyPeer, $NotifierTimeout;

	$return= FALSE;

	$ch= curl_init();
	if ($ch !== FALSE) {
		$headers= array(
			'Authorization: key='.$NotifierAPIKey,
			'Content-Type: application/json'
			);

		$message= json_encode(
			array(
				'registration_ids' => array_keys(json_decode($NotifierUsers, TRUE)),
				'notification' => array(
					'title' => $title,
					'body' => $body,
					'icon' => 'notification',
					'sound'=> 'default',
					),
				'data' => array(
					'title' => $title,
					'body' => $body,
					'data' => array(
						// Timestamp used as unique message id
						'timestamp' => time(),
						/// @attention Put data field within data, so that Android app does not convert the subfields to a map
						// We process these fields uniformly as json
						'data' => array(
							/// @attention If the Android app is in the background, it cannot access the title and body fields in notification
							// So, repeat the title and body fields in data
							'title' => $title,
							'body' => $body,
							'data' => $data,
							'datetime' => exec('/bin/date "+%c"'),
							)
						)
					)
				)
			);

		wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Notification: '.json_encode($headers).', '.$message);

		curl_setopt($ch, CURLOPT_URL, $NotifierHost);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $NotifierSSLVerifyPeer);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
		// Time curl_exec() out if it takes too long
		curl_setopt($ch, CURLOPT_TIMEOUT, $NotifierTimeout);
		
		$start= time();
		$output= curl_exec($ch);
		wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'curl_exec() runs for '.(time() - $start).' secs');

		if ($output !== FALSE) {
			$output= json_decode($output, TRUE);
			if ($output !== NULL) {
				if (is_array($output)) {
					if (isset($output['success']) && $output['success'] == 1) {
						$return= TRUE;
					}
					if (isset($output['failure']) && $output['failure'] == 1) {
						wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Notification failed');
					}
				} else {
					wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Curl exec output not array');
				}
			}
		} else {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed executing notifier curl');
		}
		curl_close($ch);

		return $return;
	}
}

function BuildFields(&$title, &$body, &$data, $total, $level, $text)
{
	global $ModuleErrorCounts, $ServiceStatus, $StatusText, $Prios;

	$title[]= "$total $text";

	$modules= array();
	foreach ($ModuleErrorCounts[$level] as $module => $count) {
		$modules[]= ucfirst($module).' '.$StatusText[$ServiceStatus[$module]['Status']].": $count $text";
	}
	$body[]= implode(', ', $modules);

	foreach ($ModuleErrorCounts[$level] as $module => $count) {
		foreach ($ServiceStatus[$module]['Logs'] as $log) {
			if (in_array($log['Prio'], $Prios[$level])) {
				$data[$module][$level][]= $log;
				break;
			}
		}
	}
}

function FilterLogLevels()
{
	global $ServiceStatus, $Prios, $NotifyLevel;

	foreach ($ServiceStatus as $module => $status) {
		foreach ($status['Logs'] as $i => $log) {
			// The log should pass the log level filter, otherwise we delete it
			$deleteLog= TRUE;

			// Critical errors are always reported
			if (in_array($log['Prio'], $Prios['Critical'])) {
				$deleteLog= FALSE;
			}

			if ($deleteLog && $NotifyLevel >= LOG_ERR) {
				if (in_array($log['Prio'], $Prios['Error'])) {
					$deleteLog= FALSE;
				}
			}

			if ($deleteLog && $NotifyLevel >= LOG_WARNING) {
				if (in_array($log['Prio'], $Prios['Warning'])) {
					$deleteLog= FALSE;
				}
			}

			if ($deleteLog) {
				unset($ServiceStatus[$module]['Logs'][$i]);
			}
		}

		if (count($ServiceStatus[$module]['Logs']) == 0) {
			unset($ServiceStatus[$module]);
		} else {
			/// @attention Fake slice to update the keys, keep all seen logs to filter the next $ServiceStatus
			$ServiceStatus[$module]['Logs']= array_slice($ServiceStatus[$module]['Logs'], 0);
		}
	}
}

function FilterKeywords()
{
	global $ServiceStatus, $NotifierFilters;

	$filters= json_decode($NotifierFilters, TRUE);

	if (count($filters) == 0) {
		return;
	}

	foreach ($ServiceStatus as $module => $status) {
		foreach ($status['Logs'] as $i => $log) {
			$deleteLog= TRUE;

			foreach ($filters as $re) {
				$re= Escape($re, '/');
				/// @todo Filter module names too? So the module name must be among the filter keywords
				if (preg_match("/$re/", $log['Process']) || preg_match("/$re/", $log['Prio']) || preg_match("/$re/", $log['Log'])) {
					$deleteLog= FALSE;
					break;
				}
			}

			if ($deleteLog) {
				unset($ServiceStatus[$module]['Logs'][$i]);
			}
		}

		if (count($ServiceStatus[$module]['Logs']) == 0) {
			unset($ServiceStatus[$module]);
		} else {
			/// @attention Fake slice to update the keys, keep all seen logs to filter the next $ServiceStatus
			$ServiceStatus[$module]['Logs']= array_slice($ServiceStatus[$module]['Logs'], 0);
		}
	}
}

function FilterPreviousLogs()
{
	global $ServiceStatus;

	$notifierFile= UTMFWDIR.'/out/NotifierServiceStatus.json';

	$previousServiceStatus= array();
	if (file_exists($notifierFile)) {
		$previousServiceStatus= json_decode(file_get_contents($notifierFile), TRUE);
	}

	// Save the current $ServiceStatus to use as a filter for the next notifier call
	// Do not use PutFile(), it expects file to exist
	/// @attention Save $ServiceStatus before filtering with the previous logs, otherwise we may delete current logs
	// All logs should remain in $previousServiceStatus until they drop from the unfiltered $ServiceStatus, hence the definition of "current" logs
	file_put_contents($notifierFile, json_encode($ServiceStatus), LOCK_EX);

	// Filter with the previously sent logs, i.e. do not send the same log twice
	foreach ($previousServiceStatus as $module => $status) {
		if (!array_key_exists($module, $ServiceStatus)) {
			continue;
		}

		foreach ($previousServiceStatus[$module]['Logs'] as $plog) {
			foreach ($ServiceStatus[$module]['Logs'] as $i => $clog) {
				if ($plog['Date'] == $clog['Date'] && $plog['Time'] == $clog['Time'] && $plog['Process'] == $clog['Process']
						&& $plog['Prio'] == $clog['Prio'] && $plog['Log'] == $clog['Log']) {
					unset($ServiceStatus[$module]['Logs'][$i]);
					break;
				}
			}
		}

		if (count($ServiceStatus[$module]['Logs']) == 0) {
			unset($ServiceStatus[$module]);
		} else {
			/// @attention Fake slice to update the keys
			$ServiceStatus[$module]['Logs']= array_slice($ServiceStatus[$module]['Logs'], 0);
		}
	}
}

$View= new View();
$View->Model= 'system';

/// @attention This script is executed on the command line, so we don't have access to cookie and session vars here.
// Do not use SSH to run Controller commands
$UseSSH= FALSE;

if (count(json_decode($NotifierUsers, TRUE)) > 0) {
	if ($View->Controller($Output, 'GetServiceStatus')) {
		$ServiceStatus= json_decode($Output[0], TRUE)['status'];

		FilterLogLevels();
		FilterKeywords();
		FilterPreviousLogs();

		// $ServiceStatus may become empty after applying filters
		if (count($ServiceStatus)) {
			// Critical errors are always reported
			$NotifyWarning= $NotifyLevel >= LOG_WARNING;
			$NotifyError= $NotifyLevel >= LOG_ERR;

			$Critical= 0;
			$Error= 0;
			$Warning= 0;

			$ModuleErrorCounts= array();
			foreach ($ServiceStatus as $Module => $StatusArray) {
				$count= $ServiceStatus[$Module]['Critical'];
				if ($count) {
					$Critical+= $count;
					$ModuleErrorCounts['Critical'][$Module]= $count;
				}

				if ($NotifyError) {
					$count= $ServiceStatus[$Module]['Error'];
					if ($count) {
						$Error+= $count;
						$ModuleErrorCounts['Error'][$Module]= $count;
					}
				}

				if ($NotifyWarning) {
					$count= $ServiceStatus[$Module]['Warning'];
					if ($count) {
						$Warning+= $count;
						$ModuleErrorCounts['Warning'][$Module]= $count;
					}
				}
			}

			wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Counts: $Critical, $Error, $Warning, Level: $NotifyLevel, $NotifyError, $NotifyWarning");

			if ($Critical || ($NotifyError && $Error) || ($NotifyWarning && $Warning)) {
				$Title= array();
				$Body= array();
				$Data= array();

				if ($Critical) {
					BuildFields($Title, $Body, $Data, $Critical, 'Critical', 'critical errors');
				}
				if ($Error && $NotifyError) {
					BuildFields($Title, $Body, $Data, $Error, 'Error', 'errors');
				}
				if ($Warning && $NotifyWarning) {
					BuildFields($Title, $Body, $Data, $Warning, 'Warning', 'warnings');
				}

				$hostname= 'UTMFW';
				if ($View->Controller($Myname, 'GetMyName')) {
					$hostname= $Myname[0];
				} else {
					wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot get system name');
				}

				$Title= $hostname.': '.implode(', ', $Title);
				$Body= implode(', ', $Body);

				if (Notify($Title, $Body, $Data)) {
					exit(0);
				}
			} else {
				wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Nothing to notify due to notify level or counts');
				exit(0);
			}
		} else {
			wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Nothing to notify due to service status being empty, perhaps after applying filters');
			exit(0);
		}
	} else {
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot get service status');
	}
} else {
	wui_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, 'No device token to send notifications to');
	exit(0);
}

wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Notifier failed');
exit(1);
?>
