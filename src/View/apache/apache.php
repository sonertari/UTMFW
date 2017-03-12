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

require_once('include.php');

$LogConf = array(
    'apache' => array(
        'Fields' => array(
            'Date',
            'Time',
            'Level',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('\berror\b'),
                'yellow' => array('\bwarning\b', '\bnotice\b'),
                'green' => array('\bsuccess'),
        		),
    		),
		),
	);

class Apache extends View
{
	public $Model= 'apache';
	public $Layout= 'httpd';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('Web Server');

		$this->LogsHelpMsg= _HELPWINDOW('These logs may be important for diagnosing web server related problems.');

		$this->ConfHelpMsg= _HELPWINDOW('Since this web administration interface depends on the Apache web server, you should be careful while modifying these options. By default, the web server is configured to serve this web interface only, hence the default values should suffice for most purposes. However, OpenBSD/httpd is a full-featured HTTP server, and you can configure it to serve web sites too.');
	
		$this->Config= array(
			'ServerAdmin' => array(
				'title' => _TITLE2('Server Admin'),
				'info' => _HELPBOX2('ServerAdmin: Your address, where problems with the server should be e-mailed.
		This address appears on some server-generated pages, such as error documents.'),
				),
			'HostnameLookups' => array(
				'title' => _TITLE2('Hostname Lookups'),
				'info' => _HELPBOX2('Log the names of clients or just their IP addresses e.g., www.apache.org (on) or 204.62.129.132 (off).
		The default is off because it\'d be overall better for the net if people had to knowingly turn this feature on, since enabling it means that each client request will result in AT LEAST one lookup request to the nameserver.'),
				),
			'LogLevel' => array(
				'title' => _TITLE2('Log Level'),
				'info' => _HELPBOX2('LogLevel: Control the number of messages logged to the error_log.
		Possible values include: debug, info, notice, warn, error, crit, alert, emerg.'),
				),
			'Timeout' => array(
				'title' => _TITLE2('Timeout'),
				'info' => _HELPBOX2('The number of seconds before receives and sends time out.'),
				),
			'KeepAlive' => array(
				'title' => _TITLE2('KeepAlive'),
				'info' => _HELPBOX2('Whether or not to allow persistent connections (more than one request per connection). Set to "Off" to deactivate.'),
				),
			'MaxKeepAliveRequests' => array(
				'title' => _TITLE2('Max KeepAlive Requests'),
				'info' => _HELPBOX2('The maximum number of requests to allow during a persistent connection. Set to 0 to allow an unlimited amount. We recommend you leave this number high, for maximum performance.'),
				),
			'KeepAliveTimeout' => array(
				'title' => _TITLE2('KeepAlive Timeout'),
				'info' => _HELPBOX2('Number of seconds to wait for the next request from the same client on the same connection.'),
				),
			'MinSpareServers' => array(
				'title' => _TITLE2('Min Spare Servers'),
				'info' => _HELPBOX2('Server-pool size regulation.
		Rather than making you guess how many server processes you need, Apache dynamically adapts to the load it sees --- that is, it tries to maintain enough server processes to handle the current load, plus a few spare servers to handle transient load spikes (e.g., multiple simultaneous requests from a single Netscape browser).

		It does this by periodically checking how many servers are waiting for a request.  If there are fewer than MinSpareServers, it creates a new spare.  If there are more than MaxSpareServers, some of the spares die off.  The default values in httpd.conf-dist are probably OK for most sites.'),
				),
			'MaxSpareServers' => array(
				'title' => _TITLE2('Max Spare Servers'),
				),
			'StartServers' => array(
				'title' => _TITLE2('Start Servers'),
				'info' => _HELPBOX2('Number of servers to start initially --- should be a reasonable ballpark figure.'),
				),
			'MaxClients' => array(
				'title' => _TITLE2('Max Clients'),
				'info' => _HELPBOX2('Limit on total number of servers running, i.e., limit on the number of clients who can simultaneously connect --- if this limit is ever reached, clients will be LOCKED OUT, so it should NOT BE SET TOO LOW. It is intended mainly as a brake to keep a runaway server from taking the system with it as it spirals down...'),
				),
			);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= htmlspecialchars($cols['Log']);
	}
}

$View= new Apache();
?>
