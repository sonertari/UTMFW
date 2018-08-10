#!/usr/local/bin/php
<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
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

function Notify($title, $body, $data)
{
	global $NotifierHost, $NotifierAPIKey, $NotifierTokens, $NotifierSSLVerifyPeer;

	$return= FALSE;

	$ch= curl_init();
	if ($ch !== FALSE) {
		$headers= array(
			'Authorization: key='.$NotifierAPIKey,
			'Content-Type: application/json'
			);

		$message= json_encode(
			array(
				'registration_ids' => json_decode($NotifierTokens, TRUE),
				'notification' => array(
					'notId' => 1,
					'sound'=> 1,
					'vibrate'=> 1,
					'title' => $title,
					'body' => $body,
					),
				'data' => $data,
				)
			);

		wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Notification: '.json_encode($headers).', '.$message);

		curl_setopt($ch, CURLOPT_URL, $NotifierHost);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $NotifierSSLVerifyPeer);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $message);

		// Fork curl_exec() to time it out if it takes too long
		if (!CurlExec($ch, $output, $retval)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed executing notifier curl');
		} else {
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
		}
		curl_close($ch);

		return $return;
	}
}

/// @todo Code reuse: Combine this with RunPfctlCmd()?
function CurlExec($ch, &$output, &$retval)
{
	global $NotifierTimeout;

	$retval= 0;
	$output= array();

	$mqid= 1;
	$queue= msg_get_queue($mqid);

	if (!msg_queue_exists($mqid)) {
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed creating or attaching to message queue');
		return FALSE;
	}

	$sendtype= 1;

	$pid= pcntl_fork();

	if ($pid == -1) {
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot fork notifier process');
	} elseif ($pid) {
		// This is the parent!

		$return= FALSE;

		// Parent should wait for output for $NotifierTimeout seconds
		// Wait count starts from 1 due to do..while loop
		$count= 1;

		// We use this $interval var instead of a constant like .1, because
		// if $NotifierTimeout is set to 0, $interval becomes 0 too, effectively disabling sleep
		// Add 1 to prevent division by zero ($NotifierTimeout cannot be set to -1 on the WUI)
		$interval= $NotifierTimeout/($NotifierTimeout + 1)/10;

		do {
			exec("/bin/sleep $interval");
			wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, "Receive message wait count: $count, sleep interval: $interval");

			/// @attention Do not wait for a message, loop instead: MSG_IPC_NOWAIT
			$received= msg_receive($queue, 0, $recvtype, 10000, $msg, TRUE, MSG_NOERROR|MSG_IPC_NOWAIT, $error);

			if ($received && $sendtype == $recvtype) {
				if (is_array($msg) && array_key_exists('retval', $msg) && array_key_exists('output', $msg)) {
					$retval= $msg['retval'];
					$output= $msg['output'];

					wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Received notifier output: ' . print_r($msg, TRUE));

					$return= TRUE;
					break;
				} else {
					wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Output not in correct format: ' . print_r($msg, TRUE));
					break;
				}
			} else {
				wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Failed receiving notifier output: ' . posix_strerror($error));
			}

		} while ($count++ < $NotifierTimeout * 10);

		if (!$return) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Timed out running notifier command');
		}

		// Parent removes the queue
		if (!msg_remove_queue($queue)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed removing message queue');
		}

		/// @attention Make sure the child is terminated, otherwise the parent gets stuck too.
		if (posix_getpgid($pid)) {
			exec("/bin/kill -KILL $pid");
		}

		// Parent survives
		return $return;
	} else {
		// This is the child!

		// Child should run the command and send the result in a message
		wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Running notifier command');
		$result= curl_exec($ch);

		$msg= array(
			'retval' => $result === FALSE ? FALSE:TRUE,
			'output' => $result === FALSE ? '':$result
			);

		if (!msg_send($queue, $sendtype, $msg, TRUE, TRUE, $error)) {
			wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Failed sending notifier output: ' . print_r($msg, TRUE) . ', error: ' . posix_strerror($error));
		} else {
			wui_syslog(LOG_DEBUG, __FILE__, __FUNCTION__, __LINE__, 'Sent notifier output: ' . print_r($msg, TRUE));
		}

		// Child exits
		exit;
	}
}

function BuildFields(&$title, &$body, &$data, $total, $level, $text)
{
	global $ModuleErrorCounts, $ServiceStatus;

	$StatusText= array(
		'R' => _('running'),
		'S' => _('stopped'),
		);

	$title[]= "$total $text";

	$modules= array();
	foreach ($ModuleErrorCounts[$level] as $module => $count) {
		$modules[]= ucfirst($module).' '.$StatusText[$ServiceStatus[$module]['Status']].": $count $text";
	}
	$body[]= implode(', ', $modules);

	foreach ($ModuleErrorCounts[$level] as $module => $count) {
		// TODO: Should we send data and time only, so delete the other fields?
		$data[$module][$level][]= $ServiceStatus[$module]['Logs'][0];
	}
}

$View= new View();
$View->Model= 'system';

/// @attention This script is executed on the command line, so we don't have access to cookie and session vars here.
// Do not use SSH to run Controller commands
$UseSSH= FALSE;

if (count(json_decode($NotifierTokens, TRUE))) {
	if ($View->Controller($Output, 'GetServiceStatus')) {
		$ServiceStatus= json_decode($Output[0], TRUE);

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
		wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Cannot get service status');
	}
} else {
	wui_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, 'No device token to send notifications');
	exit(0);
}

wui_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, 'Notifier failed');
exit(1);
?>
