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
    'squid' => array(
        'Fields' => array(
            'DateTime',
            'Target',
            'Link',
            'Size',
            'Mtd',
            'Code',
            'Direct',
            'Cache',
            'Type',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('ERROR'),
                'yellow' => array('MISS'),
                'green' => array('HIT'),
        		),
    		),
		),
	);

class Squid extends View
{
	public $Model= 'squid';
	public $Layout= 'squid';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('HTTP Proxy');

		$this->LogsHelpMsg= _HELPWINDOW('Source IP of all the requests on this page is the loopback interface, i.e. 127.0.0.1, hence not listed here. If the HTTP proxy is configured as non-caching proxy, you should see TCP_MISS on the Cache column.');
		$this->ConfHelpMsg= _HELPWINDOW('By default, the Web Filter connects to the HTTP proxy over the loopback interface and at port 3128. If you do not want to use the Web Filter, you can change this Proxy IP:Port setting to the internal IP address of the system and port 8080. When you stop the Web Filter, all requests from the internal network should be directed to the HTTP proxy.');
	
		$this->Config = array(
			'no_cache deny localhost' => array(
				'title' => _TITLE2('No cache'),
				'info' => _HELPBOX2('Enable UTMFW as a non-caching proxy for the local network.'),
				),
			'log_ip_on_direct' => array(
				'title' => _TITLE2('Log ip on direct'),
				'info' => _HELPBOX2('Log the destination IP address in the hierarchy log tag when going direct. Earlier Squid versions logged the hostname here. If you prefer the old way set this to off.'),
				),
			'debug_options' => array(
				'title' => _TITLE2('Debug options'),
				'info' => _HELPBOX2('Logging options are set as section,level where each source file is assigned a unique section.  Lower levels result in less output,  Full debugging (level 9) can result in a very large log file, so be careful.  The magic word "ALL" sets debugging levels for all sections.  We recommend normally running with "ALL,1".'),
				),
			'log_fqdn' => array(
				'title' => _TITLE2('Log fqdn'),
				'info' => _HELPBOX2('Turn this on if you wish to log fully qualified domain names in the access.log. To do this Squid does a DNS lookup of all IP\'s connecting to it. This can (in some situations) increase latency, which makes your cache seem slower for interactive browsing.'),
				),
			'client_netmask' => array(
				'title' => _TITLE2('Client netmask'),
				'info' => _HELPBOX2('A netmask for client addresses in logfiles and cachemgr output.
		Change this to protect the privacy of your cache clients.
		A netmask of 255.255.255.0 will log all IP\'s in that range with the last digit set to \'0\'.'),
				),
			'http_access allow localhost' => array(
				'title' => _TITLE2('Allow localhost'),
				),
			'http_access deny all' => array(
				'title' => _TITLE2('Deny others'),
				'info' => _HELPBOX2('And finally deny all other access to this proxy'),
				),
			'cache_mgr' => array(
				'title' => _TITLE2('Cache mgr'),
				'info' => _HELPBOX2('Email-address of local cache manager who will receive mail if the cache dies. The default is "webmaster".'),
				),
			);
	}
	
	function PrintLogLine($cols, $linenum)
	{
		$this->PrintLogLineClass($cols['Cache']);

		PrintLogCols($linenum, $cols);
		echo '</tr>';
	}
	
	function FormatLogCols(&$cols)
	{
		$link= $cols['Link'];
		if (preg_match('|^(http://[^/]*)|', $cols['Link'], $match)) {
			$linkbase= $match[1];
		}
		$cols['Link']= '<a href="'.$link.'" title="'.$link.'">'.wordwrap($linkbase, 40, '<br />', TRUE).'</a>';
	}
}

$View= new Squid();
?>
