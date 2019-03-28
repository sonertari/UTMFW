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

require_once('include.php');

$LogConf = array(
	'e2guardian' => array(
		'Fields' => array(
			'Date' => _TITLE('Date'),
			'Time' => _TITLE('Time'),
			'Process' => _TITLE('Process'),
			'Prio' => _TITLE('Prio'),
			'Log' => _TITLE('Log'),
			),
		'HighlightLogs' => array(
			'REs' => array(
				'red' => array('\berror\b'),
				'yellow' => array('\bnotice\b'),
				'green' => array('\bsuccess'),
				),
			),
		),
	);

class E2guardian extends View
{
	public $Model= 'e2guardian';
	public $Layout= 'e2guardian';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('Web Filter');
		$this->LogsHelpMsg= _HELPWINDOW('These logs may be important for diagnosing web filter related problems.');
	}
	
	/**
	 * Sets the DG filter group for the current session.
	 *
	 * @warning Group file should probably be checked only for $_POST method. But
	 * there is no guarantee that session group will exist forever, or that Group 1 exists.
	 */
	function SetSessionConfOpt()
	{
		if (filter_has_var(INPUT_POST, 'ConfOpt')) {
			$group= filter_input(INPUT_POST, 'ConfOpt');
		}
		else if ($_SESSION[$this->Model]['ConfOpt']) {
			$group= $_SESSION[$this->Model]['ConfOpt'];
		}
		else {
			$group= '1';
		}

		if ($this->Controller($output, 'GroupExists', $group)) {
			$_SESSION[$this->Model]['ConfOpt']= $group;
		}
		else {
			PrintHelpWindow(_NOTICE('Filter group not found').': '.$group, 'auto', 'ERROR');
		}
	}

	/**
	 * Displays an edit box and a button to change current group.
	 */
	function PrintConfOptForm()
	{
		// Print the group form even if group not found, so user can change it
		?>
		<table>
			<tr>
				<td>
					<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
						<?php echo _TITLE('Group').':' ?>
						<input type="text" name="ConfOpt" style="width: 20px;" maxlength="2" value=<?php echo $_SESSION[$this->Model]['ConfOpt'] ?> />
						<input type="submit" name="ApplyGroup" value="<?php echo _CONTROL('Apply') ?>"/>
					</form>
				</td>
				<td>
					<?php
					PrintHelpBox(_HELPBOX2('This shows the active group for this page. Use this form to change the active group.'), 400);
					?>
				</td>
			</tr>
		</table>
		<?php
	}
}

$View= new E2guardian();

$ListHelpMsg= _HELPWINDOW('Whitelist entries are allowed. Greylist entries are allowed, and scanned for viruses. Blacklist entries are denied.');
$WeightedListHelpMsg= _HELPWINDOW('Categories in Weightedlist are blocked according to the sum of assigned weights to each phrase.');

/**
 * Basic group configuration.
 */
$basicConfig= array(
	'groupname' => array(
		'title' => _TITLE2('Filter group name'),
		'info' => _HELPBOX2('Used to fill in the -FILTERGROUP- placeholder in the HTML template file, and to name the group in the access logs
Defaults to empty string'),
		),
	'groupmode' => array(
		'title' => _TITLE2('Filter group mode'),
		'info' => _HELPBOX2('This option determines whether members of this group have their web access unfiltered, filtered, or banned. This mechanism replaces the "banneduserlist" and "exceptionuserlist" files from previous versions.

0 = banned
1 = filtered
2 = unfiltered (exception)

Only filter groups with a mode of 1 need to define phrase, URL, site, extension, mimetype and PICS lists; in other modes, these options are ignored to conserve memory.

Defaults to 0 if unspecified.
Unauthenticated users are treated as being in the first filter group.'),
		),
	'blockdownloads' => array(
		'title' => _TITLE2('Block downloads'),
		'info' => _HELPBOX2('Blanket download blocking
If enabled, all files will be blocked, unless they match the exceptionextensionlist or exceptionmimetypelist.
These lists do not override any other content filtering or virus scanning.
Exception lists defined above override all types of filtering, including the blanket download block.
Defaults to disabled. (on | off)'),
		),
	'accessdeniedaddress' => array(
		'title' => _TITLE2('Access denied address'),
		'info' => _HELPBOX2('This is the address of your web server to which the cgi e2guardian reporting script was copied. Only used in reporting levels 1 and 2.

This webserver must be either:
 1. Non-proxied. Either a machine on the local network, or listed as an exception in your browser\'s proxy configuration.
 2. Added to the exceptionsitelist. Option 1 is preferable; this option is only for users using both transparent proxying and a non-local server to host this script.

Individual filter groups can override this setting in their own configuration.'),
		),
	'nonstandarddelimiter' => array(
		'title' => _TITLE2('Non standard delimiter'),
		'info' => _HELPBOX2('(only used with accessdeniedaddress)
To help preserve the full banned URL, including parameters, the variables passed into the access denied CGI are separated using non-standard delimiters. This can be useful to ensure correct operation of the filter bypass modes. Parameters are split using "::" in place of "&", and "==" in place of "=".
Default is enabled, but to go back to the standard mode, disable it.'),
		),
	);

/**
 * Content filtering configuration.
 */
$scanConfig= array(
	'weightedphrasemode' => array(
		'title' => _TITLE2('Weighted phrase mode'),
		'info' => _HELPBOX2('There are 3 possible modes of operation:
0 = off = do not use the weighted phrase feature.
1 = on, normal = normal weighted phrase operation.
2 = on, singular = each weighted phrase found only counts once on a page.'),
		),
	'naughtynesslimit' => array(
		'title' => _TITLE2('Naughtyness limit'),
		'info' => _HELPBOX2(' This the limit over which the page will be blocked.  Each weighted phrase is given a value either positive or negative and the values added up.  Phrases to do with good subjects will have negative values, and bad subjects will have positive values.  See the weightedphraselist file for examples.
As a guide:
50 is for young children,  100 for old children,  160 for young adults.'),
		),
	'categorydisplaythreshold' => array(
		'title' => _TITLE2('Category display threshold'),
		'info' => _HELPBOX2('This option only applies to pages blocked by weighted phrase filtering.
Defines the minimum score that must be accumulated within a particular category in order for it to show up on the block pages\' category list.
All categories under which the page scores positively will be logged; those that were not displayed to the user appear in brackets.

-1 = display only the highest scoring category
0 = display all categories (default)
> 0 = minimum score for a category to be displayed'),
		),
	'embeddedurlweight' => array(
		'title' => _TITLE2('Embedded URL weighting'),
		'info' => _HELPBOX2('When set to something greater than zero, this option causes URLs embedded within a page\'s HTML (from links, image tags, etc.) to be extracted and checked against the bannedsitelist and bannedurllist. Each link to a banned page causes the amount set here to be added to the page\'s weighting.
The behaviour of this option with regards to multiple occurrences of a site/URL is affected by the weightedphrasemode setting.

Set to 0 to disable.
Defaults to 0.
WARNING: This option is highly CPU intensive!'),
		),
	'enablepics' => array(
		'title' => _TITLE2('PICS rating support'),
		'info' => _HELPBOX2('Defaults to disabled (on | off)'),
		),
	'disablecontentscan' => array(
		'title' => _TITLE2('Disable content scanning'),
		'info' => _HELPBOX2('If you enable this option you will disable content scanning for this group.
Content scanning primarily is AV scanning (if enabled) but could include other types.
(on|off) default = off.'),
		),
	'contentscanexceptions' => array(
		'title' => _TITLE2('Content scan exceptions'),
		'info' => _HELPBOX2('If \'on\' exception sites, urls, users etc will be scanned
This is probably not desirable behavour as exceptions are supposed to be trusted and will increase load.
Correct use of grey lists are a better idea.
(on|off) default = off'),
		),
	'deepurlanalysis' => array(
		'title' => _TITLE2('Enable Deep URL Analysis'),
		'info' => _HELPBOX2('When enabled, E2 looks for URLs within URLs, checking against the bannedsitelist and bannedurllist. This can be used, for example, to block images originating from banned sites from appearing in Google Images search results, as the original URLs are embedded in the thumbnail GET requests.
(on|off) default = off'),
		),
	'textmimetypes' => array(
		'title' => _TITLE2('Additional mime types'),
		'info' => _HELPBOX2('Phrase filtering additional mime types.
default text/*'),
		),
	'maxuploadsize' => array(
		'title' => _TITLE2('Max upload size'),
		'info' => _HELPBOX2('POST protection (web upload and forms) does not block forms without any file upload, i.e. this is just for blocking or limiting uploads measured in kibibytes after MIME encoding and header bumph
use 0 for a complete block
use higher (e.g. 512 = 512Kbytes) for limiting
use -1 for no blocking'),
		),
	);

/**
 * Bypass configuration.
 */
$bypassConfig= array(
	'bypass' => array(
		'title' => _TITLE2('Temporary Denied Page Bypass'),
		'info' => _HELPBOX2('This provides a link on the denied page to bypass the ban for a few minutes.  To be secure it uses a random hashed secret generated at daemon startup.  You define the number of seconds the bypass will function for before the deny will appear again.
To allow the link on the denied page to appear you will need to edit the template.html or e2guardian.pl file for your language.
300 = enable for 5 minutes
0 = disable ( defaults to 0 )
-1 = enable but you require a separate program/CGI to generate a valid link'),
		),
	'bypasskey' => array(
		'title' => _TITLE2('Temporary Denied Page Bypass Secret Key'),
		'info' => _HELPBOX2('Rather than generating a random key you can specify one. It must be more than 8 chars.
\'\' = generate a random one (recommended and default)
\'Mary had a little lamb.\' = an example
\'76b42abc1cd0fdcaf6e943dcbc93b826\' = an example
Please note: manually entered keys are converted to all lowercase before use.'),
		),
	'infectionbypass' => array(
		'title' => _TITLE2('Infection/Scan Error Bypass'),
		'info' => _HELPBOX2('Similar to the \'bypass\' setting, but specifically for bypassing files scanned and found to be infected, or files that trigger scanner errors - for example, archive types with recognised but unsupported compression schemes, or corrupt archives.
The option specifies the number of seconds for which the bypass link will be valid.
300 = enable for 5 minutes
0 = disable (default)
-1 = enable, but require a separate program/CGI to generate a valid link'),
		),
	'infectionbypasskey' => array(
		'title' => _TITLE2('Infection/Scan Error Bypass Secret Key'),
		'info' => _HELPBOX2('Same as the \'bypasskey\' option, but used for infection bypass mode.'),
		),
	'infectionbypasserrorsonly' => array(
		'title' => _TITLE2('Infection/Scan Error Bypass on Scan Errors Only'),
		'info' => _HELPBOX2('Enable this option to allow infectionbypass links only when virus scanning fails, not when a file is found to contain a virus.
on = enable (default and highly recommended)
off = disable'),
		),
	);

/**
 * E-mail configuration.
 */
$emailConfig= array(
	'usesmtp' => array(
		'title' => _TITLE2('Use SMTP'),
		'info' => _HELPBOX2('If on, will enable system wide events to be reported by email.
need to configure mail program (see \'mailer\' in global config) and email recipients
default usesmtp = off'),
		),
	'mailfrom' => array(
		'title' => _TITLE2('Mail From'),
		'info' => _HELPBOX2('Who the email would come from
example: mailfrom = \'e2guardian@mycompany.com\''),
		),
	'avadmin' => array(
		'title' => _TITLE2('AV Admin'),
		'info' => _HELPBOX2('Who the virus emails go to (if notify av is on)
example: avadmin = \'admin@mycompany.com\''),
		),
	'contentadmin' => array(
		'title' => _TITLE2('Content Admin'),
		'info' => _HELPBOX2('Who the content emails go to (when thresholds are exceeded) and contentnotify is on
example: contentadmin = \'admin@mycompany.com\''),
		),
	'avsubject' => array(
		'title' => _TITLE2('AV Subject'),
		'info' => _HELPBOX2('Subject of the email sent when a virus is caught.
Only applicable if notifyav is on.
default avsubject = \'e2guardian virus block\''),
		),
	'contentsubject' => array(
		'title' => _TITLE2('Content Subject'),
		'info' => _HELPBOX2('Subject of the email sent when violation thresholds are exceeded default contentsubject = \'e2guardian violation\''),
		),
	'notifyav' => array(
		'title' => _TITLE2('Notify AV'),
		'info' => _HELPBOX2('This will send a notification, if usesmtp/notifyav is on, any time an infection is found.
Important: If this option is off, viruses will still be recorded like a content infection.'),
		),
	'notifycontent' => array(
		'title' => _TITLE2('Notify Content'),
		'info' => _HELPBOX2('This will send a notification, if usesmtp is on, based on thresholds below'),
		),
	'thresholdbyuser' => array(
		'title' => _TITLE2('Threshold By User'),
		'info' => _HELPBOX2('Results are only predictable with user authenticated configs, if enabled the violation/threshold count is kept track of by the user'),
		),
	'violations' => array(
		'title' => _TITLE2('Violations'),
		'info' => _HELPBOX2('Number of violations before notification. Setting to 0 will never trigger a notification'),
		),
	'threshold' => array(
		'title' => _TITLE2('Threshold'),
		'info' => _HELPBOX2('This is in seconds. If \'violations\' occur in \'threshold\' seconds, then a notification is made.
If this is set to 0, then whenever the set number of violations are made a notifaction will be sent.'),
		),
	);

/**
 * General basic configuration.
 */
$GeneralbasicConfig= array(
	'language' => array(
		'title' => _TITLE2('Language'),
		'info' => _HELPBOX2('language to use from languagedir, e.g. \'ukenglish\''),
		),
	'filterip' => array(
		'title' => _TITLE2('Filter IP'),
		'info' => _HELPBOX2('The IP that E2Guardian listens on.  If left blank E2Guardian will listen on all IPs.  That would include all NICs, loopback, modem, etc.
Normally you would have your firewall protecting this, but if you want you can limit it to a certain IP. To bind to multiple interfaces, specify each IP on an individual filterip line.'),
		),
	// @todo There may be more than one such port settings
	'filterports' => array(
		'title' => _TITLE2('Filter Port'),
		'info' => _HELPBOX2('the port that E2Guardian listens to.'),
		),
	'proxyip' => array(
		'title' => _TITLE2('Proxy IP'),
		'info' => _HELPBOX2('the ip of the proxy (default is the loopback - i.e. this server)'),
		),
	'proxyport' => array(
		'title' => _TITLE2('Proxy Port'),
		'info' => _HELPBOX2('the port E2Guardian connects to proxy on'),
		),
	'reportinglevel' => array(
		'title' => _TITLE2('Reporting level'),
		'info' => _HELPBOX2('-1 = log, but do not block - Stealth mode
0 = just say Access Denied
1 = report why but not what denied phrase
2 = report fully
3 = use HTML template file (accessdeniedaddress ignored) - recommended

If defined, this overrides the global setting in e2guardian.conf for members of this filter group.'),
		),
	'usecustombannedimage' => array(
		'title' => _TITLE2('Banned image replacement'),
		'info' => _HELPBOX2('Images that are banned due to domain/url/etc reasons including those in the adverts blacklists can be replaced by an image.  This will, for example, hide images from advert sites and remove broken image icons from banned domains.
0 = off
1 = on (default)'),
		),
	'usecustombannedflash' => array(
		'title' => _TITLE2('Banned flash replacement'),
		),
	);

/**
 * General filter configuration.
 */
$GeneralfilterConfig= array(
	'showweightedfound' => array(
		'title' => _TITLE2('Show weighted phrases found'),
		'info' => _HELPBOX2('If enabled then the phrases found that made up the total which excedes the naughtyness limit will be logged and, if the reporting level is high enough, reported. on | off'),
		),
	'weightedphrasemode' => array(
		'title' => _TITLE2('Weighted phrase mode'),
		'info' => _HELPBOX2('There are 3 possible modes of operation:
0 = off = do not use the weighted phrase feature.
1 = on, normal = normal weighted phrase operation.
2 = on, singular = each weighted phrase found only counts once on a page.'),
		),
	'urlcachenumber' => array(
		'title' => _TITLE2('Url cache number'),
		'info' => _HELPBOX2('Positive (clean) result caching for URLs Caches good pages so they don\'t need to be scanned again.
It also works with AV plugins.
0 = off (recommended for ISPs with users with disimilar browsing)
1000 = recommended for most users
5000 = suggested max upper limit
If you\'re using an AV plugin then use at least 5000.'),
		),
	'urlcacheage' => array(
		'title' => _TITLE2('Url cache age'),
		'info' => _HELPBOX2('Age before they are stale and should be ignored in seconds
0 = never
900 = recommended = 15 mins'),
		),
	'scancleancache' => array(
		'title' => _TITLE2('Scan clean cache'),
		'info' => _HELPBOX2('Clean cache for content (AV) scan results.
By default, to save CPU, files scanned and found to be clean are inserted into the clean cache and NOT scanned again for a while.  If you don\'t like this then choose to disable it.
(on|off) default = on.'),
		),
	'phrasefiltermode' => array(
		'title' => _TITLE2('Phrase filter mode'),
		'info' => _HELPBOX2('Smart, Raw and Meta/Title phrase content filtering options.
Smart is where the multiple spaces and HTML are removed before phrase filtering.
Raw is where the raw HTML including meta tags are phrase filtered.
Meta/Title is where only meta and title tags are phrase filtered (v. quick).
CPU usage can be effectively halved by using setting 0 or 1 compared to 2.
0 = raw only
1 = smart only
2 = both of the above (default)
3 = meta/title'),
		),
	'preservecase' => array(
		'title' => _TITLE2('Preserve case'),
		'info' => _HELPBOX2('Lower casing options.
When a document is scanned the uppercase letters are converted to lower case in order to compare them with the phrases.  However this can break Big5 and other 16-bit texts.  If needed preserve the case.  As of version 2.7.0 accented characters are supported.
0 = force lower case (default)
1 = do not change case'),
		),
	'hexdecodecontent' => array(
		'title' => _TITLE2('Hex decode content'),
		'info' => _HELPBOX2('Hex decoding options.
When a document is scanned it can optionally convert %XX to chars.
If you find documents are getting past the phrase filtering due to encoding then enable. However this can break Big5 and other 16-bit texts.
0 = disabled (default)
1 = enabled'),
		),
	'forcequicksearch' => array(
		'title' => _TITLE2('Force quick search'),
		'info' => _HELPBOX2('Force Quick Search rather than DFA search algorithm.
The current DFA implementation is not totally 16-bit character compatible but is used by default as it handles large phrase lists much faster.
If you wish to use a large number of 16-bit character phrases then enable this option.
0 = off (default)
1 = on (Big5 compatible)'),
		),
	'reverseaddresslookups' => array(
		'title' => _TITLE2('Reverse address lookups'),
		'info' => _HELPBOX2('Reverse lookups for banned site and URLs.
If set to on, E2Guardian will look up the forward DNS for an IP URL address and search for both in the banned site and URL lists. This would prevent a user from simply entering the IP for a banned address.
It will reduce searching speed somewhat so unless you have a local caching DNS server, leave it off and use the Blanket IP Block option in the bannedsitelist file instead.'),
		),
	'reverseclientiplookups' => array(
		'title' => _TITLE2('Reverse client ip lookups'),
		'info' => _HELPBOX2('Reverse lookups for banned and exception IP lists.
If set to on, E2Guardian will look up the forward DNS for the IP of the connecting computer.  This means you can put in hostnames in the exceptioniplist and bannediplist.
If a client computer is matched against an IP given in the lists, then the IP will be recorded in any log entries; if forward DNS is successful and a match occurs against a hostname, the hostname will be logged instead.
It will reduce searching speed somewhat so unless you have a local DNS server, leave it off.'),
		),
	'createlistcachefiles' => array(
		'title' => _TITLE2('Create list cache files'),
		'info' => _HELPBOX2('Build bannedsitelist and bannedurllist cache files.
This will compare the date stamp of the list file with the date stamp of the cache file and will recreate as needed.
If a bsl or bul .processed file exists, then that will be used instead.
It will increase process start speed by 300%.
On slow computers this will be significant.
Fast computers do not need this option.
on | off'),
		),
	);

/**
 * General content scanner configuration.
 */
$GeneralscanConfig= array(
	'maxcontentfiltersize' => array(
		'title' => _TITLE2('Max content filter size'),
		'info' => _HELPBOX2('Sometimes web servers label binary files as text which can be very large which causes a huge drain on memory and cpu resources.
To counter this, you can limit the size of the document to be filtered and get it to just pass it straight through.
This setting also applies to content regular expression modification.
The value must not be higher than maxcontentramcachescansize
The size is in Kibibytes - eg 2048 = 2Mb
use 0 to set it to maxcontentramcachescansize'),
		),
	'maxcontentramcachescansize' => array(
		'title' => _TITLE2('Max content ram cache scan size'),
		'info' => _HELPBOX2('This is only used if you use a content scanner plugin such as AV
This is the max size of file that E2 will download and cache in RAM.  After this limit is reached it will cache to disk.
This value must be less than or equal to maxcontentfilecachescansize.
The size is in Kibibytes - eg 10240 = 10Mb
use 0 to set it to maxcontentfilecachescansize
This option may be ignored by the configured download manager.'),
		),
	'maxcontentfilecachescansize' => array(
		'title' => _TITLE2('Max content file cache scan size'),
		'info' => _HELPBOX2('This is only used if you use a content scanner plugin such as AV.
This is the max size file that E2 will download so that it can be scanned or virus checked.
This value must be greater or equal to maxcontentramcachescansize.
The size is in Kibibytes - eg 10240 = 10Mb'),
		),
	'deletedownloadedtempfiles' => array(
		'title' => _TITLE2('Delete downloaded temp files'),
		'info' => _HELPBOX2('Delete file cache after user completes download
When a file gets save to temp it stays there until it is deleted.
You can choose to have the file deleted when the user makes a sucessful download. This will mean if they click on the link to download from the temp store a second time it will give a 404 error.
You should configure something to delete old files in temp to stop it filling up.
on|off ( defaults to on )'),
		),
	'initialtrickledelay' => array(
		'title' => _TITLE2('Initial Trickle delay'),
		'info' => _HELPBOX2('This is the number of seconds a browser connection is left waiting before first being sent *something* to keep it alive. The *something* depends on the download manager chosen.
Do not choose a value too low or normal web pages will be affected.
A value between 20 and 110 would be sensible.
This may be ignored by the configured download manager.'),
		),
	'trickledelay' => array(
		'title' => _TITLE2('Trickle delay'),
		'info' => _HELPBOX2('This is the number of seconds a browser connection is left waiting before being sent more *something* to keep it alive. The *something* depends on the download manager chosen.
This may be ignored by the configured download manager.'),
		),
	'contentscannertimeout' => array(
		'title' => _TITLE2('Content scanner timeout'),
		'info' => _HELPBOX2('Some of the content scanners support using a timeout value to stop processing (eg AV scanning) the file if it takes too long.
If supported this will be used.
The default of 60 seconds is probably reasonable.'),
		),
	'recheckreplacedurls' => array(
		'title' => _TITLE2('Re-check replaced URLs'),
		'info' => _HELPBOX2('As a matter of course, URLs undergo regular expression search/replace (urlregexplist) *after* checking the exception site/URL/regexpURL lists, but *before* checking against the banned site/URL lists, allowing certain requests that would be matched against the latter in their original state to effectively be converted into grey requests.
With this option enabled, the exception site/URL/regexpURL lists are also re-checked after replacement, making it possible for URL replacement to trigger exceptions based on them.
Defaults to off.'),
		),
	);

/**
 * General log configuration.
 */
$GenerallogsConfig= array(
	'reportinglevel' => array(
		'title' => _TITLE2('Web Access Denied Reporting'),
		'info' => _HELPBOX2('-1 = log, but do not block - Stealth mode
0 = just say \'Access Denied\'
1 = report why but not what denied phrase
2 = report fully
3 = use HTML template file (accessdeniedaddress ignored) - recommended'),
		),
	'loglevel' => array(
		'title' => _TITLE2('Logging Settings'),
		'info' => _HELPBOX2('0 = none  1 = just denied  2 = all text based  3 = all requests'),
		),
	'logexceptionhits' => array(
		'title' => _TITLE2('Log Exception Hits'),
		'info' => _HELPBOX2('Log if an exception (user, ip, URL, phrase) is matched and so the page gets let through.  Can be useful for diagnosing why a site gets through the filter.  on | off'),
		),
	'maxlogitemlength' => array(
		'title' => _TITLE2('Max Log Item Length'),
		'info' => _HELPBOX2('truncate large items in log lines'),
		),
	'anonymizelogs' => array(
		'title' => _TITLE2('Anonymize Logs'),
		'info' => _HELPBOX2('anonymize logs (blank out usernames & IPs)'),
		),
	'logclienthostnames' => array(
		'title' => _TITLE2('Log client hostnames'),
		'info' => _HELPBOX2('Perform reverse lookups on client IPs for successful requests.
If set to on, E2Guardian will look up the forward DNS for the IP of the connecting computer, and log host names (where available) rather than IPs against requests.
This is not dependent on reverseclientiplookups being enabled; however, if it is, enabling this option does not incur any additional forward DNS requests.'),
		),
	'logconnectionhandlingerrors' => array(
		'title' => _TITLE2('Log connection handling errors'),
		'info' => _HELPBOX2('if on it logs some debug info regarding fork()ing and accept()ing which can usually be ignored.  These are logged by syslog.  It is safe to leave it on or off'),
		),
	'logchildprocesshandling' => array(
		'title' => _TITLE2('Log child process handling'),
		'info' => _HELPBOX2('If on, this causes E2 to write to the log file whenever child processes are created or destroyed (other than by crashes). This information can help in understanding and tuning the following parameters, but is not generally useful in production.'),
		),
	'logadblocks' => array(
		'title' => _TITLE2('Log ad blocks'),
		'info' => _HELPBOX2('Enable logging of "ADs" category blocks
on|off (defaults to off)'),
		),
	);


/**
 * Fancy download manager plugin configuration.
 */
$GeneraldownloadsConfig= array(
	'useragentregexp' => array(
		'title' => _TITLE2('User agent regexp'),
		'info' => _HELPBOX2('Regular expression for matching user agents
When not defined, matches all agents.

\'mozilla\' also matches firefox, IE, etc.'),
		),
	'maxdownloadsize' => array(
		'title' => _TITLE2('Maximum download size'),
		'info' => _HELPBOX2('When a file with unknown content length gets handled by the fancy DM, something must be done in the case that the file is found to be too large to scan (i.e. larger than maxcontentfilecachescansize).
As of 2.9.7.0, a warning will be issued to the user that the fancy DM may not be able to cache the entire file, and the file will continue to be downloaded to disk (but not scanned) until it reaches this size, at which point the user will simply have to re-download the file (the URL won\'t be scanned again).
The size is in kibibytes (i.e. 10240 = 10Mb)'),
		),
	);

/**
 * General advanced configuration.
 */
$GeneraladvancedConfig= array(
	'proxytimeout' => array(
		'title' => _TITLE2('Proxy timeout'),
		'info' => _HELPBOX2('Set tcp timeout between the Proxy and e2guardian 
Min 5 - Max 100'),
		),
	'proxyfailureloginterval' => array(
		'title' => _TITLE2('Proxy failure log interval'),
		'info' => _HELPBOX2('The interval between log status entries when proxy is not responding minimum is proxytimeout - maximum 3600 (= 1 hour)
default = 600  (= 10 mins)'),
		),
	'proxyexchange' => array(
		'title' => _TITLE2('Proxy header exchange'),
		'info' => _HELPBOX2('Set timeout between the Proxy and e2guardian 
Min 20 - Max 300'),
		),
	'pcontimeout' => array(
		'title' => _TITLE2('Pconn timeout'),
		'info' => _HELPBOX2('How long a persistent connection will wait for other requests, squid apparently defaults to 1 minute (persistent_request_timeout), so wait slightly less than this to avoid duff pconns.
Min 5 - Max 300'),
		),
	'forwardedfor' => array(
		'title' => _TITLE2('Forwarded for'),
		'info' => _HELPBOX2('If on it adds an X-Forwarded-For: <clientip> to the HTTP request header. This may help solve some problem sites that need to know the source ip. on | off'),
		),
	'usexforwardedfor' => array(
		'title' => _TITLE2('Use xforwarded for'),
		'info' => _HELPBOX2('If on it uses the X-Forwarded-For: <clientip> to determine the client IP. This is for when you have squid between the clients and E2Guardian.
Warning - headers are easily spoofed. on | off'),
		),
	// @todo There may be more than one such ip settings
	'xforwardedforfilterip' => array(
		'title' => _TITLE2('Filter IP for xforwarded for'),
		'info' => _HELPBOX2('The headers can be easily spoofed in order to fake the request origin by setting the X-Forwarded-For header. If you have the "usexforwardedfor" option enabled, you may want to specify the IPs from which this kind of header is allowed, such as another upstream proxy server for instance If you want authorize multiple IPs, specify each one on an individual xforwardedforfilterip line.'),
		),
	'httpworkers' => array(
		'title' => _TITLE2('Max threads'),
		'info' => _HELPBOX2('This is the maximum number of threads, i.e. concurrent connections. If more connections are made, connections will queue until a worker thread is free.
On large sites you might want to increase this number, e.g. 5000 (max value 20000).'),
		),
	'maxchildren' => array(
		'title' => _TITLE2('Max children'),
		'info' => _HELPBOX2('Sets the maximum number of processes to spawn to handle the incoming connections.  Max value usually 250 depending on OS.
On large sites you might want to try 180.'),
		),
	'minchildren' => array(
		'title' => _TITLE2('Min children'),
		'info' => _HELPBOX2('Sets the minimum number of processes to spawn to handle the incoming connections.
On large sites you might want to try 32.'),
		),
	'minsparechildren' => array(
		'title' => _TITLE2('Min spare children'),
		'info' => _HELPBOX2('Sets the minimum number of processes to be kept ready to handle connections.
On large sites you might want to try 8.'),
		),
	'preforkchildren' => array(
		'title' => _TITLE2('Prefork children'),
		'info' => _HELPBOX2('Sets the minimum number of processes to spawn when it runs out.
On large sites you might want to try 10.'),
		),
	'maxsparechildren' => array(
		'title' => _TITLE2('Max spare children'),
		'info' => _HELPBOX2('Sets the maximum number of processes to have doing nothing.
When this many are spare it will cull some of them.
On large sites you might want to try 64.'),
		),
	'maxagechildren' => array(
		'title' => _TITLE2('Max age children'),
		'info' => _HELPBOX2('Sets the maximum age of a child process before it croaks it.
This is the number of connections they handle before exiting.
On large sites you might want to try 10000.'),
		),
	'gentlechunk' => array(
		'title' => _TITLE2('Gentle child restart'),
		'info' => _HELPBOX2('Sets the number of child process to kill/fork at each 5 sec interval, during gentle restart.
Defaults to preforkchildren.'),
		),
	'maxips' => array(
		'title' => _TITLE2('Max ips'),
		'info' => _HELPBOX2('Sets the maximum number client IP addresses allowed to connect at once.
Use this to set a hard limit on the number of users allowed to concurrently browse the web. Set to 0 for no limit, and to disable the IP cache process.'),
		),
	'nologger' => array(
		'title' => _TITLE2('Disable logging process'),
		'info' => _HELPBOX2('on|off ( defaults to off )'),
		),
	'softrestart' => array(
		'title' => _TITLE2('Soft restart'),
		'info' => _HELPBOX2('When on this disables the forced killing off all processes in the process group.
This is not to be confused with the -g run time option - they are not related.
on|off ( defaults to off )'),
		),
	'mailer' => array(
		'title' => _TITLE2('Mail program'),
		'info' => _HELPBOX2('Path (sendmail-compatible) email program, with options.
Not used if usesmtp is disabled (filtergroup specific).'),
		),
	);
?>
