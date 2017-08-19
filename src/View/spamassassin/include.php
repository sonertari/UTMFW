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
    'spamassassin' => array(
        'Fields' => array(
            'Date',
            'Time',
            'Process',
            'Log',
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('identified spam\b'),
                'yellow' => array('WARNING'),
                'green' => array('clean message\b'),
        		),
    		),
		),
	);

class Spamassassin extends View
{
	public $Model= 'spamassassin';
	public $Layout= 'spamassassin';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('SPAM Filter');

		$this->LogsHelpMsg= _HELPWINDOW('Detailed results of spam content scans are recorded by SpamAssassin, such as spam scores and their reasons.');
		$this->GraphHelpMsg= _HELPWINDOW('SpamAssassin is a perl process. These graphs display data from all perl processes.');
		$this->ConfHelpMsg= _HELPWINDOW('An important setting may be the rewrite header which is used on the subject line of an e-mail identified as spam. Note that spam identification is a guess based on statistical calculations, hence there may be false positives.');
	
		$this->Config = array(
			'rewrite_header Subject' => array(
				'title' => _TITLE2('Rewrite header'),
				'info' => _HELPBOX2('Add *****SPAM***** to the Subject header of spam e-mails'),
				),
			'report_safe' => array(
				'title' => _TITLE2('Report safe'),
				'info' => _HELPBOX2('Save spam messages as a message/rfc822 MIME attachment instead of modifying the original message (0: off, 2: use text/plain instead)'),
				),
			'trusted_networks' => array(
				'title' => _TITLE2('Trusted networks'),
				'info' => _HELPBOX2('Set which networks or hosts are considered \'trusted\' by your mail server (i.e. not spammers)'),
				),
			'required_score' => array(
				'title' => _TITLE2('Required score'),
				'info' => _HELPBOX2('Set the threshold at which a message is considered spam (default: 5.0)'),
				),
			'use_bayes' => array(
				'title' => _TITLE2('Use bayes'),
				'info' => _HELPBOX2('Use Bayesian classifier (default: 1)'),
				),
			'bayes_auto_learn' => array(
				'title' => _TITLE2('Bayes auto learn'),
				'info' => _HELPBOX2('Bayesian classifier auto-learning (default: 1)'),
				),
			'bayes_ignore_header X-Bogosity' => array(
				'title' => _TITLE2('X-Bogosity'),
				'info' => _HELPBOX2('Set headers which may provide inappropriate cues to the Bayesian classifier'),
				),
			'bayes_ignore_header X-Spam-Flag' => array(
				'title' => _TITLE2('X-Spam-Flag'),
				),
			'bayes_ignore_header X-Spam-Status' => array(
				'title' => _TITLE2('X-Spam-Status'),
				),
			);
	}
	
	function FormatLogCols(&$cols)
	{
		$cols['Log']= htmlspecialchars($cols['Log']);
	}
}

$View= new Spamassassin();
?>
