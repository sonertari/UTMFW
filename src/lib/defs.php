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
 * Common variables, arrays, and constants.
 */

/// Project version.
define('VERSION', '7.7');

$ROOT= dirname(dirname(dirname(__FILE__)));
$SRC_ROOT= dirname(dirname(__FILE__));

$VIEW_PATH= $SRC_ROOT . '/View';
$MODEL_PATH= $SRC_ROOT . '/Model';

define('UTMFWDIR', '/var/log/utmfw');

/// Syslog priority strings.
$LOG_PRIOS= array(
	'LOG_EMERG',	// system is unusable
	'LOG_ALERT',	// action must be taken immediately
	'LOG_CRIT',		// critical conditions
	'LOG_ERR',		// error conditions
	'LOG_WARNING',	// warning conditions
	'LOG_NOTICE',	// normal, but significant, condition
	'LOG_INFO',		// informational message
	'LOG_DEBUG',	// debug-level message
	);

/// Superuser
$ADMIN= array('admin');
/// Unprivileged user who can modify any configuration
$USER= array('user');
/// All valid users
$ALL_USERS= array_merge($ADMIN, $USER);

/**
 * Locale definitions used by both View and Controller.
 *
 * It is recommended that all translations use UTF-8 codeset.
 * @attention Do not translate the values for 'Name' here, we translate them when we use them in code
 *
 * @param string Name Title string
 * @param string Codeset Locale codeset
 */
$LOCALES = array(
    'en_EN' => array(
        'Name' => 'English',
        'Codeset' => 'UTF-8'
		),
    'tr_TR' => array(
        'Name' => 'Turkish',
        'Codeset' => 'UTF-8'
		),
    'sp_SP' => array(
        'Name' => 'Spanish',
        'Codeset' => 'UTF-8'
		),
    'ru_RU' => array(
        'Name' => 'Russian',
        'Codeset' => 'UTF-8'
		),
    'zh_CN' => array(
        'Name' => 'Chinese simplified',
        'Codeset' => 'UTF-8'
		),
    'nl_NL' => array(
        'Name' => 'Dutch',
        'Codeset' => 'UTF-8'
		),
    'fr_FR' => array(
        'Name' => 'French',
        'Codeset' => 'UTF-8'
		),
	);

/// Used in translating months from number to string.
$MonthNames= array(
	'01' => 'Jan',
	'02' => 'Feb',
	'03' => 'Mar',
	'04' => 'Apr',
	'05' => 'May',
	'06' => 'Jun',
	'07' => 'Jul',
	'08' => 'Aug',
	'09' => 'Sep',
	'10' => 'Oct',
	'11' => 'Nov',
	'12' => 'Dec',
	);

/// Used in translating months from string to number.
$MonthNumbers= array(
	'Jan' => '01',
	'Feb' => '02',
	'Mar' => '03',
	'Apr' => '04',
	'May' => '05',
	'Jun' => '06',
	'Jul' => '07',
	'Aug' => '08',
	'Sep' => '09',
	'Oct' => '10',
	'Nov' => '11',
	'Dec' => '12',
	);

$Re_MonthNames= implode('|', array_values($MonthNames));

$MonthNumbersNoLeadingZeros = array_map(function ($str) { return $str + 0; }, array_keys($MonthNames));
$Re_MonthNumbersNoLeadingZeros= implode('|', $MonthNumbersNoLeadingZeros);

$DaysNoLeadingZeros = array(
	'1', '2', '3', '4', '5', '6', '7', '8', '9', '10',
	'11', '12', '13', '14', '15', '16', '17', '18', '19', '20',
	'21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'
	);
$Re_DaysNoLeadingZeros= implode('|', $DaysNoLeadingZeros);

$WeekDays= array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
$Re_WeekDays= implode('|', $WeekDays);

/// General tcpdump command used everywhere.
/// @todo All system binaries called should be defined like this.
/// @attention Redirect stderr to /dev/null to hush tcpdump warning: "tcpdump: WARNING: snaplen raised from 116 to 160".
/// Otherwise that warning goes in front of the data.
$TCPDUMP= 'exec 2>/dev/null; /usr/sbin/tcpdump -nettt -r';

/// Type definitions for config settings as PREs
/// @todo Fix leading 0's problem(s)
define('UINT_0_2', '[0-2]');
define('STR_on_off', 'on|off');
define('STR_On_Off', 'On|Off');
define('STR_SING_QUOTED', '\'[^\']*\'');
define('STR_yes_no', 'yes|no');
define('UINT', '[0-9]+');
define('INT_M1_0_UP', '-1|[0-9]+');
define('UINT_0_1', '0|1');
define('INT_M1_0_3', '-1|[0-3]');
define('UINT_0_3', '[0-3]');
define('UINT_1_4', '[0-4]');
define('IP', '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}');
define('IPorNET', '(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})|(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\/\d{1,2})');
define('PORT', '[0-9]+');
define('FLOAT', '[0-9]+|([0-9]+\.[0-9]+)');
define('CHAR', '.');

/// Common regexps.
/// @todo Find a proper regexp for IPv4 addresses, this is too general.
$Re_Ip= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}';
$Re_Net= "$Re_Ip\/\d{1,2}";
$Re_IpPort= '\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}:\d{1,5}';
$Re_User= '[\w_.-]{1,31}';
/// @todo $num and $range need full testing. Define $port.
$preIPOctet= '(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])';
$preIPRange= '(\d|[1-2]\d|3[0-2])';
$preIP= "$preIPOctet\\.$preIPOctet\\.$preIPOctet\\.$preIPOctet";
$preNet= "$preIP\/$preIPRange";

$preMacByte= '[\da-f]{2}';
$preMac= "$preMacByte\:$preMacByte\:$preMacByte\:$preMacByte\:$preMacByte\:$preMacByte";

$preIfName= '\w+\d+';

/// For classifying gettext strings into files.
function _STATS($str)
{
	return _($str);
}

/**
 * Master statistics configuration.
 *
 * This array provides stats configuration parameters needed for each module.
 * Detailed behaviour of each module is defined by the settings in this array.
 *
 * @param Stats				Parent field in configuration details used on
 *							statistics pages.
 * @param Stats>Total		Mandatory sub-field for each Stats field. Configures the
 *							the general settings for the basic stats for the module.
 * @param Stats>Total>Title	Title to display on top of the graph.
 * @param Stats>Total>Cmd	Command line to get log lines. Usually to get all lines.
 * @param Stats>Total>Needle To get only those lines that contain the Needle text among
 *							the lines obtained by Stats>Total>Cmd.
 * @param Stats>Total>Color	The color of the bars on the graph.
 * @param Stats>Total>NVPs	Name-Value-Pairs to print at the bottom of the graph.
 *							Usually top 5 of some of the more important stats.
 *							Displayed in 2 columns.
 * @param Stats>Total>BriefStats Statistics (parsed field names) to collect as
 *							BriefStats. Top 100 of collected data are
 *							shown on the left of General statistics page.
 * @param Stats>Total>Counters Statistics to collect and show as a graph over total
 *							data. The difference between these counters and Stats>\<StatName\>
 *							is that these are collected using the command line for
 *							the Total stats. So there is no separate Cmd field.
 *							Counters has one extra field, Divisor, which is used to
 *							divide the total count. Usually need to convert bytes to
 *							kilobytes.
 * @param Stats><StatName>	Custom statistics to be collected. The data for these
 *							graphs are collected using the Cmd and Needle fields.
 *							Stats>Total>Counters could have been merged with this one perhaps.
 *							The sub-fields for these custom stats is the same as the
 *							Total field described above.
 */
$StatsConf = array(
	'pf' => array(
		'Total' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => $TCPDUMP.' <LF>',
			// @attention An empty needle is needed while collecting stats, see IncStats()
			'Needle' => '',
			'SearchRegexpPrefix' => '([[:blank:]\|.]+)',
			'Color' => '#01466b',
			'NVPs' => array(
				'SrcIP' => _STATS('Source addresses'),
				'DstIP' => _STATS('Destination addresses'),
				'DPort' => _STATS('Destination ports'),
				'Type' => _STATS('Packet types'),
				),
			'BriefStats' => array(
				'Date' => _STATS('Requests by date'),
				'SrcIP' => _STATS('Source addresses'),
				'DstIP' => _STATS('Destination addresses'),
				'DPort' => _STATS('Destination ports'),
				'Type' => _STATS('Packet types'),
				),
			'Counters' => array(),
			),
		'Pass' => array(
			'Title' => _STATS('Allowed requests'),
			'Cmd' => $TCPDUMP.' <LF>',
			'Needle' => ' pass ',
			'Color' => 'green',
			'NVPs' => array(
				'SrcIP' => _STATS('Source addresses'),
				'DstIP' => _STATS('Destination addresses'),
				'DPort' => _STATS('Destination ports'),
				'Type' => _STATS('Packet types'),
				),
			),
		'Block' => array(
			'Title' => _STATS('Blocked requests'),
			'Cmd' => $TCPDUMP.' <LF>',
			'Needle' => ' block ',
			'Color' => 'red',
			'NVPs' => array(
				'SrcIP' => _STATS('Source addresses'),
				'DstIP' => _STATS('Destination addresses'),
				'DPort' => _STATS('Destination ports'),
				'Type' => _STATS('Packet types'),
				),
			),
		'Match' => array(
			'Title' => _STATS('Matched requests'),
			'Cmd' => $TCPDUMP.' <LF>',
			'Needle' => ' match ',
			'Color' => '#FF8000',
			'NVPs' => array(
				'SrcIP' => _STATS('Source addresses'),
				'DstIP' => _STATS('Destination addresses'),
				'DPort' => _STATS('Destination ports'),
				'Type' => _STATS('Packet types'),
				),
			),
		),
    'e2guardianlogs' => array(
		'Total' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '',
			'SearchRegexpPrefix' => '(https://|http://|[[:blank:]]+)',
			'Color' => '#01466b',
			'NVPs' => array(
				'Link' => _STATS('Requests'),
				'User' => _STATS('Users'),
				'IP' => _STATS('IPs'),
				'Proto' => _STATS('Protocols'),
				'Mtd' => _STATS('Methods'),
				),
			'BriefStats' => array(
				'Date' => _STATS('Requests by date'),
				'User' => _STATS('Requests by user'),
				'IP' => _STATS('Requests by IP'),
				'Link' => _STATS('Links visited'),
				'Proto' => _STATS('Protocols'),
				'Mtd' => _STATS('Methods'),
				'Cat' => _STATS('Denied categories'),
				),
			'Counters' => array(
				'Sizes' => array(
					'Field' => 'Size',
					'Title' => _STATS('Downloaded (KB)'),
					'Color' => '#FF8000',
					'Divisor' => 1000,
					'NVPs' => array(
						'Link' => _STATS('Size by site (KB)'),
						'User' => _STATS('Size by User (KB)'),
						'IP' => _STATS('Size by IP (KB)'),
						'Proto' => _STATS('Size by Protocol (KB)'),
						'Mtd' => _STATS('Size by Method (KB)'),
						),
					),
				),
			),
		'Scanned' => array(
			'Title' => _STATS('Scanned requests'),
			'Needle' => 'SCANNED',
			'Color' => 'blue',
			'NVPs' => array(
				'Link' => _STATS('Requests scanned'),
				'User' => _STATS('Scanned users'),
				'IP' => _STATS('Scanned IPs'),
				'Proto' => _STATS('Protocols'),
				'Mtd' => _STATS('Methods'),
				),
			),
		'Exception' => array(
			'Title' => _STATS('Exception requests'),
			'Needle' => 'EXCEPTION',
			'Color' => '#FF8000',
			'NVPs' => array(
				'Link' => _STATS('Exception requests'),
				'User' => _STATS('Exception users'),
				'IP' => _STATS('Exception IPs'),
				'Proto' => _STATS('Protocols'),
				'Mtd' => _STATS('Methods'),
				),
			),
		'Denied' => array(
			'Title' => _STATS('Denied requests'),
			'Needle' => 'DENIED',
			'Color' => 'red',
			'NVPs' => array(
				'Link' => _STATS('Requests denied'),
				'User' => _STATS('Denied users'),
				'IP' => _STATS('Denied IPs'),
				'Cat' => _STATS('Denied categories'),
				'Proto' => _STATS('Protocols'),
				'Mtd' => _STATS('Methods'),
				),
			),
		'Infected' => array(
			'Title' => _STATS('Infected requests'),
			'Needle' => 'INFECTED',
			'Color' => 'red',
			'NVPs' => array(
				'Link' => _STATS('Requests infected'),
				'User' => _STATS('Infected users'),
				'IP' => _STATS('Infected IPs'),
				'Proto' => _STATS('Protocols'),
				'Mtd' => _STATS('Methods'),
				),
			),
		'Bypassed' => array(
			'Title' => _STATS('Bypassed denials'),
			'Needle' => 'GBYPASS| Bypass |TRUSTED',
			'Color' => '#FF8000',
			'NVPs' => array(
				'Link' => _STATS('Requests bypassed'),
				'User' => _STATS('Bypassing users'),
				'IP' => _STATS('Bypassing IPs'),
				'Proto' => _STATS('Protocols'),
				'Mtd' => _STATS('Methods'),
				),
			),
		),
    'dnsmasq' => array(
		'Total' => array(
			'Title' => _STATS('All queries'),
			'Cmd' => '/bin/cat <LF>',
			'SearchRegexpPrefix' => '([[:blank:]]+|\[)',
			'SearchRegexpPostfix' => '([[:blank:]]+|\]|\[)',
			'BriefStats' => array(
				'Date' => _STATS('Requests by date'),
				'Domain' => _STATS('Domains'),
				'IP' => _STATS('IPs querying'),
				'Type' => _STATS('Query types'),
				'Reason' => _STATS('Reason'),
				),
			'Counters' => array(),
			),
		'Queries' => array(
			'Title' => _STATS('All queries'),
			'Needle' => '( query\[)',
			'Color' => '#01466b',
			'NVPs' => array(
				'Domain' => _STATS('Domains'),
				'IP' => _STATS('IPs querying'),
				'Type' => _STATS('Query types'),
				'Reason' => _STATS('Reason'),
				),
			),
		'Failures' => array(
			'Title' => _STATS('Failed queries'),
			'Needle' => '( REFUSED$)',
			'Color' => 'Red',
			'NVPs' => array(
				'Reason' => _STATS('Reason'),
				),
			),
		'Cached' => array(
			'Title' => _STATS('Cached queries'),
			'Needle' => '( cached )',
			'Color' => 'Yellow',
			'NVPs' => array(
				'Reason' => _STATS('Reason'),
				),
			),
		),
    'openssh' => array(
		'Total' => array(
			'Title' => _STATS('All attempts'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '(Accepted|Failed)',
			'Color' => '#01466b',
			'NVPs' => array(
				'IP' => _STATS('Client IPs'),
				'User' => _STATS('Users'),
				'Type' => _STATS('SSH version'),
				),
			'BriefStats' => array(
				'Date' => _STATS('Requests by date'),
				'Type' => _STATS('SSH version'),
				'Reason' => _STATS('Failure reason'),
				'IP' => _STATS('Client IPs'),
				'User' => _STATS('Users'),
				),
			'Counters' => array(),
			),
		'Failures' => array(
			'Title' => _STATS('Failed attempts'),
			'Needle' => '(Failed .* for )',
			'Color' => 'Red',
			'NVPs' => array(
				'IP' => _STATS('Client IPs'),
				'User' => _STATS('Failed users'),
				'Type' => _STATS('SSH version'),
				'Reason' => _STATS('Failure reason'),
				),
			),
		'Successes' => array(
			'Title' => _STATS('Successful logins'),
			'Needle' => '(Accepted .* for )',
			'Color' => 'Green',
			'NVPs' => array(
				'IP' => _STATS('Client IPs'),
				'User' => _STATS('Logged in user'),
				'Type' => _STATS('SSH version'),
				),
			),
		),
    'ftp-proxy' => array(
		'Total' => array(
			'Title' => _STATS('All sessions'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '(FTP session )',
			'Color' => '#01466b',
			'NVPs' => array(
				'Client' => _STATS('Client'),
				'Server' => _STATS('Server'),
				),
			'BriefStats' => array(
				'Client' => _STATS('Client'),
				'Server' => _STATS('Server'),
				),
			),
		),
    'p3scan' => array(
		'Total' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '( p3scan\[)',
			'SearchRegexpPrefix' => "([[:blank:]'\(]+)",
			'SearchRegexpPostfix' => '([^[:alnum:].]+)',
			'BriefStats' => array(
				'SPUser' => _STATS('User'),
				'Result' => _STATS('Results'),
				'Virus' => _STATS('Infected'),
				'User' => _STATS('Account name'),
				'SrcIP' => _STATS('Source IPs'),
				'DstIP' => _STATS('Destination IPs'),
				'Proto' => _STATS('Protocols'),
				'Mails' => _STATS('E-mails per request'),
				),
			'Counters' => array(
				'Mails' => array(
					'Field' => 'Mails',
					'Title' => _STATS('Number of e-mails'),
					'Color' => 'Blue',
					'Needle' => '(. Mails: [1-9])',
					'NVPs' => array(
						'Result' => _STATS('Results'),
						),
					),
				'Bytes' => array(
					'Field' => 'Bytes',
					'Title' => _STATS('Processed (KB)'),
					'Color' => '#FF8000',
					'Divisor' => 1000,
					),
				),
			),
		'Requests' => array(
			'Title' => _STATS('All requests'),
			'Needle' => '(Connection from )',
			'Color' => '#01466b',
			'NVPs' => array(
				'Date' => _STATS('Requests by date'),
				'SPUser' => _STATS('User'),
				'SrcIP' => _STATS('Source IPs'),
				'Proto' => _STATS('Protocols'),
				),
			),
		'Results' => array(
			'Title' => _STATS('Relay results'),
			'Needle' => '(Session done )',
			'Color' => '#01466b',
			'NVPs' => array(
				'Result' => _STATS('Results'),
				'Mails' => _STATS('E-mails per request'),
				),
			),
		'Infected' => array(
			'Title' => _STATS('Infected e-mails'),
			'Needle' => '( virus:)',
			'Color' => 'red',
			'NVPs' => array(
				'SPUser' => _STATS('User'),
				'From' => _STATS('Senders'),
				'To' => _STATS('Recipients'),
				'Virus' => _STATS('Virus'),
				),
			),
		),
    'smtp-gated' => array(
		'Total' => array(
			'Cmd' => '/bin/cat <LF>',
			'SearchRegexpPrefix' => '([[:blank:]<=:]+)',
			'BriefStats' => array(
				'NewUser' => _STATS('Users'),
				'Scanner' => _STATS('Scan Types'),
				'Result' => _STATS('Scan Results'),
				'Sender' => _STATS('Senders'),
				'Recipient' => _STATS('Recipients'),
				'NewSrcIP' => _STATS('Source IPs'),
				'NewDstIP' => _STATS('Destination IPs'),
				'LockedIP' => _STATS('Locked IPs'),
				'Proto' => _STATS('Protocols'),
				),
			),
		'Requests' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '(NEW )',
			'Color' => '#01466b',
			'NVPs' => array(
				'Date' => _STATS('Requests by date'),
				'NewUser' => _STATS('Users'),
				'NewSrcIP' => _STATS('Source IPs'),
				'NewDstIP' => _STATS('Destination IPs'),
				),
			'Counters' => array(
				'Xmted' => array(
					'Field' => 'Xmted',
					'Title' => _STATS('Transmitted (KB)'),
					'Color' => 'Blue',
					'Needle' => '(CLOSE |SESSION TAKEOVER: |LOCK:LOCKED)',
					'Divisor' => 1000,
					'NVPs' => array(
						'User' => _STATS('Users'),
						'SrcIP' => _STATS('Source IPs'),
						'ClosedBy' => _STATS('Closed by'),
						),
					),
				'Rcved' => array(
					'Field' => 'Rcved',
					'Title' => _STATS('Received (KB)'),
					'Color' => '#FF8000',
					'Needle' => '(CLOSE |SESSION TAKEOVER: |LOCK:LOCKED)',
					'Divisor' => 1000,
					'NVPs' => array(
						'User' => _STATS('Users'),
						'SrcIP' => _STATS('Source IPs'),
						'ClosedBy' => _STATS('Closed by'),
						),
					),
				'Trns' => array(
					'Field' => 'Trns',
					'Title' => _STATS('Number of messages'),
					'Color' => 'Blue',
					'Needle' => '(CLOSE |SESSION TAKEOVER: |LOCK:LOCKED)',
					'NVPs' => array(
						'User' => _STATS('Users'),
						'SrcIP' => _STATS('Source IPs'),
						'ClosedBy' => _STATS('Closed by'),
						),
					),
				'Rcpts' => array(
					'Field' => 'Rcpts',
					'Title' => _STATS('Sent messages'),
					'Color' => '#FF8000',
					'Needle' => '(CLOSE |SESSION TAKEOVER: |LOCK:LOCKED)',
					'NVPs' => array(
						'User' => _STATS('Users'),
						'SrcIP' => _STATS('Source IPs'),
						'ClosedBy' => _STATS('Closed by'),
						),
					),
				),
			),
		'ST' => array(
			'Title' => _STATS('Session Takeover'),
			'Needle' => '(SESSION TAKEOVER: |Rejecting |rejected \[|LOCK:LOCKED)',
			'Color' => 'red',
			'NVPs' => array(
				'STReason' => _STATS('Reasons'),
				'User' => _STATS('Users'),
				'SrcIP' => _STATS('Source IPs'),
				),
			),
		'Scan' => array(
			'Title' => _STATS('Virus Scan Requests'),
			'Needle' => 'SCAN:',
			'Color' => 'blue',
			'NVPs' => array(
				'Result' => _STATS('Scan Results'),
				'User' => _STATS('Users'),
				'ScanSrcIP' => _STATS('Source IPs'),
				),
			),
		'Spam' => array(
			'Title' => _STATS('Spam Scan Requests'),
			'Needle' => 'SPAM:',
			'Color' => 'blue',
			'NVPs' => array(
				'Result' => _STATS('Scan Results'),
				'User' => _STATS('Users'),
				'ScanSrcIP' => _STATS('Source IPs'),
				),
			),
		'Results' => array(
			'Title' => _STATS('Relay results'),
			'Needle' => '(RCPT TO:|rejected)',
			'Color' => '#01466b',
			'NVPs' => array(
				'Sender' => _STATS('Senders'),
				'Recipient' => _STATS('Recipients'),
				'RResult' => _STATS('Results'),
				'User' => _STATS('Users'),
				),
			),
		),
    'snortalerts' => array(
		'Total' => array(
			'Title' => _STATS('All alerts'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '( -> )',
			'Color' => '#01466b',
			'NVPs' => array(
				'SrcIP' => _STATS('Source IPs'),
				'DstIP' => _STATS('Target IPs'),
				'SPort' => _STATS('Source Ports'),
				'DPort' => _STATS('Target Ports'),
				),
			'BriefStats' => array(
				'SrcIP' => _STATS('Source IPs'),
				'DstIP' => _STATS('Target IPs'),
				'DPort' => _STATS('Target Ports'),
				'Prio' => _STATS('Priorities'),
				),
			'Counters' => array(),
			),
		'Priorities' => array(
			'Title' => _STATS('Priorities'),
			'Needle' => '( -> )',
			'Color' => 'Red',
			'NVPs' => array(
				'Prio' => _STATS('Priority'),
				),
			),
		'Names' => array(
			'Title' => _STATS('Attack Types'),
			'Needle' => '( -> )',
			'Color' => 'Blue',
			'NVPs' => array(
				'Log' => _STATS('Type'),
				),
			),
		),
    'snortips' => array(
		'Total' => array(
			'Title' => _STATS('All un/blocks and extensions'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '(Blocking|Unblocking|extending| blocking)',
			'Color' => '#01466b',
			'NVPs' => array(
				'Blocked' => _STATS('Blocked Hosts'),
				'Unblocking' => _STATS('Unblocked Hosts'),
				'Extended' => _STATS('Extended Hosts'),
				),
			'BriefStats' => array(
				'Softinit' => _STATS('Soft inits (unblock all)'),
				'Blocked' => _STATS('Blocked Hosts'),
				'Extended' => _STATS('Extended Hosts'),
				'Unblocking' => _STATS('Unblocked Hosts'),
				'BlockedTime' => _STATS('Blocked Times (sec)'),
				),
			'Counters' => array(
				'BlockedTime' => array(
					'Field' => 'BlockedTime',
					'Title' => _STATS('Blocked Times (min)'),
					'Color' => '#FF8000',
					'Divisor' => 60,
					'NVPs' => array(
						'Blocked' => _STATS('Blocked Hosts'),
						),
					),
				'ExtendedTime' => array(
					'Field' => 'ExtendedTime',
					'Title' => _STATS('Extended Times (min)'),
					'Color' => '#FF8000',
					'Divisor' => 60,
					'NVPs' => array(
						'Extended' => _STATS('Extended Hosts'),
						),
					),
				),
			),
		'Blocked' => array(
			'Title' => _STATS('Blocked Hosts'),
			'Needle' => '(Blocking| blocking)',
			'Color' => 'Red',
			'NVPs' => array(
				'Blocked' => _STATS('Blocked Hosts'),
				'BlockedTime' => _STATS('Blocked Time'),
				),
			),
		'Unblocking' => array(
			'Title' => _STATS('Unblocked Hosts'),
			'Needle' => '(Unblocking)',
			'Color' => 'Green',
			'NVPs' => array(
				'Unblocking' => _STATS('Unblocked Hosts'),
				),
			),
		'Softinit' => array(
			'Title' => _STATS('Soft inits'),
			'Needle' => '(Soft init)',
			'Color' => 'Blue',
			'NVPs' => array(
				'Softinit' => _STATS('Soft inits (unblock all)'),
				),
			),
		),
    'spamassassin' => array(
		'Total' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => '/usr/bin/grep -a -E "( spamd\[)" <LF>',
			'Needle' => ' bytes.',
			'SearchRegexpPostfix' => '([^[:alnum:],]+)',
			'BriefStats' => array(),
			'Counters' => array(
				'Ham' => array(
					'Field' => 'Ham',
					'Title' => _STATS('Ham'),
					'Color' => 'Green',
					'Needle' => ': clean message \(',
					'NVPs' => array(
						'User' => _STATS('Account name'),
						),
					),
				'Spam' => array(
					'Field' => 'Spam',
					'Title' => _STATS('Spam'),
					'Color' => 'Red',
					'NVPs' => array(
						'User' => _STATS('Account name'),
						),
					),
				'Bytes' => array(
					'Field' => 'Bytes',
					'Title' => _STATS('Processed (KB)'),
					'Color' => '#FF8000',
					'Needle' => ' seconds, ',
					'Divisor' => 1000,
					'NVPs' => array(
						'User' => _STATS('Account name'),
						),
					),
				'Seconds' => array(
					'Field' => 'Seconds',
					'Title' => _STATS('Processing time (sec)'),
					'Color' => '#FF8000',
					'Needle' => ' bytes.',
					'NVPs' => array(
						'User' => _STATS('Account name'),
						),
					),
				),
			),
		'Requests' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => "/usr/bin/grep -a ' spamd\[' <LF>",
			'Needle' => ' bytes.',
			'Color' => '#01466b',
			'NVPs' => array(
				'Date' => _STATS('Requests by date'),
				'User' => _STATS('Account name'),
				),
			),
		),
    'httpdlogs' => array(
		'Total' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '',
			'SearchRegexpPrefix' => '([^[:alnum:]]+)',
			'Color' => '#01466b',
			'NVPs' => array(
				'IP' => _STATS('Clients'),
				'Link' => _STATS('Links'),
				'Mtd' => _STATS('Methods'),
				'Code' => _STATS('HTTP Codes'),
				),
			'BriefStats' => array(
				'Date' => _STATS('Requests by date'),
				'IP' => _STATS('Clients'),
				'Mtd' => _STATS('Methods'),
				'Code' => _STATS('HTTP Codes'),
				'Link' => _STATS('Links'),
				),
			'Counters' => array(
				'Sizes' => array(
					'Field' => 'Size',
					'Title' => _STATS('Downloaded (KB)'),
					'Color' => '#FF8000',
					'Divisor' => 1000,
					'NVPs' => array(
						'Link' => _STATS('Size by Link (KB)'),
						'IP' => _STATS('Size by IP (KB)'),
						),
					),
				),
			),
		),
    // 'spamd' => array(
	// 	'Total' => array(
	// 		'Title' => _STATS('All connections'),
	// 		'Cmd' => '/bin/cat <LF>',
	// 		'Needle' => '( spamd\[)',
	// 		'BriefStats' => array(
	// 			'List' => _STATS('Blacklists'),
	// 			'IP' => _STATS('Deferred IPs'),
	// 			),
	// 		'Counters' => array(
	// 			'Seconds' => array(
	// 				'Field' => 'Seconds',
	// 				'Title' => _STATS('Total deferred time (sec)'),
	// 				'Color' => 'Green',
	// 				'NVPs' => array(
	// 					'IP' => _STATS('IPs'),
	// 					'Seconds' => _STATS('Longest deferred (sec)'),
	// 					'Date' => _STATS('Connections by date'),
	// 					'List' => _STATS('Blacklists'),
	// 					),
	// 				),
	// 			),
	// 		),
	// 	'Requests' => array(
	// 		'Title' => _STATS('All connections'),
	// 		'Needle' => ' disconnected ',
	// 		'Color' => '#01466b',
	// 		'NVPs' => array(
	// 			'IP' => _STATS('IPs'),
	// 			'List' => _STATS('Blacklists'),
	// 			'Date' => _STATS('Connections by date'),
	// 			),
	// 		),
	// 	),
    'sslproxy' => array(
		'Total' => array(
			'Cmd' => '/bin/cat <LF>',
			'SearchRegexpPrefix' => '([[:blank:]=:]+)',
			'BriefStats' => array(
				'Error' => _STATS('Errors'),
				'Warning' => _STATS('Warnings'),
				),
			),
		'Stats' => array(
			'Needle' => 'STATS:',
			'Counters' => array(
				'DownloadBytes' => array(
					'Field' => 'IntifOutBytes',
					'Title' => _STATS('Downloads (KB)'),
					'Color' => 'Blue',
					'Divisor' => 1000,
					),
				'UploadBytes' => array(
					'Field' => 'IntifInBytes',
					'Title' => _STATS('Uploads (KB)'),
					'Color' => 'Green',
					'Divisor' => 1000,
					),
				'SetWatermarks' => array(
					'Field' => 'SetWatermark',
					'Title' => _STATS('Set watermarks'),
					'Color' => 'Red',
					),
				),
			),
		'Error' => array(
			'Title' => _STATS('Errors'),
			'Needle' => 'ERROR:|CRITICAL:',
			'Color' => 'Red',
			'NVPs' => array(
				'Error' => _STATS('Reasons'),
				),
			),
		'Warning' => array(
			'Title' => _STATS('Warnings'),
			'Needle' => 'WARNING:',
			'Color' => 'Yellow',
			'NVPs' => array(
				'Warning' => _STATS('Reasons'),
				),
			),
		),
    'sslproxyconns' => array(
		'Total' => array(
			'Cmd' => '/bin/cat <LF>',
			'SearchRegexpPrefix' => '([[:blank:]=:]+)',
			'BriefStats' => array(
				'User' => _STATS('Users'),
				'Proto' => _STATS('Protocols'),
				'SrcAddr' => _STATS('Source Addresses'),
				'DstAddr' => _STATS('Destination Addresses'),
				'SProto' => _STATS('SSL Source Protocols'),
				'DProto' => _STATS('SSL Destination Protocols'),
				),
			),
		'Connections' => array(
			'Title' => _STATS('Connections'),
			'Needle' => 'CONN:',
			'Color' => '#01466b',
			'NVPs' => array(
				'User' => _STATS('Users'),
				'Proto' => _STATS('Protocols'),
				'SrcAddr' => _STATS('Source Addresses'),
				'DstAddr' => _STATS('Destination Addresses'),
				'SProto' => _STATS('SSL Source Protocols'),
				'DProto' => _STATS('SSL Destination Protocols'),
				'Date' => _STATS('Requests by date'),
				),
			),
		'Idle' => array(
			'Title' => _STATS('Idle connections'),
			'Needle' => 'IDLE:',
			'Color' => 'Yellow',
			'NVPs' => array(
				'IdleSrcAddr' => _STATS('Source Addresses'),
				'IdleDstAddr' => _STATS('Destination Addresses'),
				'IdleTime' => _STATS('Idle Times'),
				'IdleDuration' => _STATS('Durations'),
				'IdleUser' => _STATS('Users'),
				),
			),
		'Expired' => array(
			'Title' => _STATS('Expired connections'),
			'Needle' => 'EXPIRED:',
			'Color' => 'Red',
			'NVPs' => array(
				'ExpiredSrcAddr' => _STATS('Source Addresses'),
				'ExpiredDstAddr' => _STATS('Destination Addresses'),
				'ExpiredIdleTime' => _STATS('Idle Times'),
				'ExpiredUser' => _STATS('Users'),
				),
			),
		),
    'clamd' => array(
		'Total' => array(
			'Title' => _STATS('All requests'),
			'Cmd' => '/bin/cat <LF>',
			'Needle' => '(FOUND|OK)$',
			'SearchRegexpPrefix' => '([^[:alnum:]]+)',
			'SearchRegexpPostfix' => '([^[:alnum:]\.]+)',
			'Color' => '#01466b',
			'NVPs' => array(
				'Scan' => _STATS('Scan Results'),
				'Virus' => _STATS('Virus'),
				),
			'BriefStats' => array(
				'Date' => _STATS('Requests by date'),
				'Scan' => _STATS('Scan Results'),
				'Virus' => _STATS('Virus'),
				),
			),
		),
	);

/// For classifying gettext strings into files.
/// @attention Moved here to satisfy $ModelsToStat below
function _TITLE($str)
{
	return _($str);
}

/// Models to get statuses
/// @todo Code reuse issue: Titles are already available as class vars
$ModelsToStat= array(
	'system' => _TITLE('System'),
	'pf' => _TITLE('Packet Filter'),
	'sslproxy' => _TITLE('SSL Proxy'),
	'e2guardian' => _TITLE('Web Filter'),
	'snort' => _TITLE('IDS'),
	'snortinline' => _TITLE('Inline IPS'),
	'snortips' => _TITLE('Passive IPS'),
	'spamassassin' => _TITLE('SPAM Filter'),
	'clamd' => _TITLE('Virus Filter'),
	'freshclam' => _TITLE('Virus DB Update'),
	'p3scan' => _TITLE('POP3 Proxy'),
	'smtp-gated' => _TITLE('SMTP Proxy'),
	'imspector' => _TITLE('IM Proxy'),
	'dhcpd' => _TITLE('DHCP Server'),
	'dnsmasq' => _TITLE('DNS Forwarder'),
	'openvpn' => _TITLE('OpenVPN'),
	'openssh' => _TITLE('OpenSSH'),
	'ftp-proxy' => _TITLE('FTP Proxy'),
	'dante' => _TITLE('SOCKS Proxy'),
	// 'spamd' => _TITLE('SPAM Deferral'),
	'httpd' => _TITLE('Web User Interface'),
	'symon' => _TITLE('Symon'),
	'symux' => _TITLE('Symux'),
	'pmacct' => _TITLE('Pmacct'),
	'collectd' => _TITLE('Collectd'),
	);

$DashboardIntervals2Seconds= array(
	'1min' => 60,
	'5min' => 300,
	'10min' => 600,
	'30min' => 1800,
	'1hour' => 3600,
	'12hour' => 43200,
	'1day' => 86400,
	'1week' => 604800,
	'1month' => 2592000,
	'6month' => 15552000,
	);

$PF_CONFIG_PATH= '/etc/pfre';
$TMP_PATH= '/var/log/utmfw/tmp';

$TEST_DIR_PATH= '';
/// @attention Necessary to set to '/utmfw' instead of '' to fix $ROOT . $TEST_DIR_SRC in model.php
$TEST_DIR_SRC= '/utmfw';
$INSTALL_USER= 'root';

$SSLPROXY_CONFIG_PATH= '/etc/sslproxy';
?>
