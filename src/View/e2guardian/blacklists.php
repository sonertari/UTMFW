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
    'blacklists' => array(
        'Fields' => array(
            'Category',
            'Site',
    		),
        'HighlightLogs' => array(
            'REs' => array(
				// Do not highlight any blacklist search result
        		),
    		),
		),
	);

class Blacklists extends View
{
	public $Model= 'blacklists';
	
	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->ConfHelpMsg= _HELPWINDOW('You can search categories on this page.');
	}
	
	function FormatLogCols(&$cols)
	{
		$link= 'http://'.$cols['Site'];
		$cols['Site']= '<a href="'.$link.'">'.$cols['Site'].'</a>';
	}
}

$View= new Blacklists();

/**
 * Displays a form to search sites/urls in black lists.
 *
 * @param string $site Search string.
 */
function PrintSiteCategorySearchForm($site)
{
	?>
	<table>
		<tr>
			<td>
				<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
					<?php echo _TITLE('Search site').': ' ?>
					<input type="text" name="SearchSite" style="width: 200px;" maxlength="100" value="<?php echo $site ?>"/>
					<input type="submit" name="Search" value="<?php echo _CONTROL('Search') ?>"/>
				</form>
			</td>
			<td>
				<?php
				PrintHelpBox(_HELPBOX2('Here you can search sites and urls in category listings. Regexp below can help you further refine your search.'));
				?>
			</td>
		</tr>
	</table>
	<?php
}

/**
 * Appends (prefixes) blacklist category descriptions table to cats page help boxes.
 *
 * @param string $msg Help box string to append to, output.
 */
function AppendCatsDescTable(&$msg)
{
	$catdescs= array(
		'adv' => _HELPWINDOW('All about advertising: This includes sites offering banners and banner creation as well as sites delivering banners to be shown in webpages and advertising companies.'),
		'aggressive' => _HELPWINDOW('Sites with aggressive content such as racism and hate speech.'),
		'alcohol' => _HELPWINDOW('Sites of breweries, wineries and destilleries. This category also covers sites that explain how to make beer, wines and spirits.'),
		'anonvpn' => _HELPWINDOW('Sites providing vpn services to the public. The focus is on vpn sites used to hide the origin of the traffic, f.e. tor nodes.'),
		'automobile_cars' => _HELPWINDOW('All sites related to cars. Included are automobile companies and automotive suppliers.'),
		'automobile_bikes' => _HELPWINDOW('All sites related to motorcycles. Included are vendor sites, resellers, fan and hobby pages as well as and suppliers. Scooters included.'),
		'automobile_boats' => _HELPWINDOW('All sites related motorboats. Included are vendor sites, resellers, fan and hobby pages as well as and suppliers.'),
		'automobile_planes' => _HELPWINDOW('All sites related to planes ranging from small one and two seaters up to the large traffic planes, old and new, private, commercial and military. Vendors and supplier are included (airports are not). Helicopter sites are included as well.'),
		'chat' => _HELPWINDOW('Site for real-time chatting and instant messaging.'),
		'costtraps' => _HELPWINDOW('Sites that lure with free of charge services but then give then give you a costly subscription (written somewhere in tiny letters nearly unreadable).'),
		'dating' => _HELPWINDOW('Sites to contact people for love and living together. He seeks her, she seeks him and so on.'),
		'downloads' => _HELPWINDOW('This covers mostly filesharing, p2p and torrent sites. Other download sites (for software, wallpapers, ..) are included as well.'),
		'drugs' => _HELPWINDOW('Sites offering drugs or explain how to make drugs. Covers alcohol and tobacco as well as viagra and similar substances.'),
		'dynamic' => _HELPWINDOW('All domains where people login obtaining a dynamic IP address.'),
		'education_schools' => _HELPWINDOW('Home pages of schools, colleges and universities.'),
		'finance_banking' => _HELPWINDOW('Home page of banking companies are listed here. This is not restricted to online banking.'),
		'finance_insurance' => _HELPWINDOW('Sites of insurance companies, information about insurances and link collections concering this subject.'),
		'finance_moneylending' => _HELPWINDOW('Sites one can apply for loans and mortgages or can obtain information about this business.'),
		'finance_other' => _HELPWINDOW('Finance in general.'),
		'finance_realestate' => _HELPWINDOW('Sites about all types of real estate, buying and selling homes, finding apartments for rent.'),
		'finance_trading' => _HELPWINDOW('Sites about and related to stock exchange.'),
		'fortunetelling' => _HELPWINDOW('All sites about astrology, horoscopes, numerology, palm reading and so on; sites that offer services to fortell the future.'),
		'forum' => _HELPWINDOW('Discussion sites. Covers explicit forum sites and some blogs. Sites where people can discuss and share information in a non interactive/real-time way.'),
		'gamble' => _HELPWINDOW('Sites offering the possibility to win money. Poker, Casino, Bingo and other chance games as well as betting sites.'),
		'government' => _HELPWINDOW('Sites belonging to the government of a country, county or city.'),
		'hacking' => _HELPWINDOW('Sites with information and discussions about security weaknesses and how to exploit them. Sites offering exploits are listed as well as sites distributing programs that help to find security leaks.'),
		'hobby_cooking' => _HELPWINDOW('Sites concerning food and food preparation.'),
		'hobby_games-misc' => _HELPWINDOW('Sites related to games. This includes descriptions, news and general information about games. No gambling sites.'),
		'hobby_games-online' => _HELPWINDOW('Sites about online games. The games are for fun only (no gambling).'),
		'hobby_gardening' => _HELPWINDOW('Sites about gardening, growing plants, fighting bugs and everything related to gardening.'),
		'hobby_pets' => _HELPWINDOW('All topics concerning pets: description, breed, food, looks, fairs, favorite pet stories and so on.'),
		'homestyle' => _HELPWINDOW('Sites about everything need to create a cozy home (interior design and accessories).'),
		'hospitals' => _HELPWINDOW('Sites of hospitals and medical facilities.'),
		'imagehosting' => _HELPWINDOW('Sites specialized on hosting images, photo galleries and so on.'),
		'isp' => _HELPWINDOW('Home pages of Internet Service Providers. Sites of companies offering webspace only are now being added, too'),
		'jobsearch' => _HELPWINDOW('Portals for job offers and job seekers as well as the career and work-for-us pages of companies.'),
		'library' => _HELPWINDOW('Online libraries and sites where you can read e-books.'),
		'military' => _HELPWINDOW('Sites of military facilities or related to the armed forces.'),
		'models' => _HELPWINDOW('Model agency, model and supermodel fan pages and other model sites presenting model photos. No porn pictures.'),
		'movies' => _HELPWINDOW('Sites offering cinema programs, information about movies and actors. Sites for downloading video clips/movies (as long as it is legal) are included as well.'),
		'music' => _HELPWINDOW('Sites that offer the download of music, information about music groups or music in general.'),
		'news' => _HELPWINDOW('Sites presenting news. Homepages from newspapers, magazines and journals as well as some blogs.'),
		'podcasts' => _HELPWINDOW('Sites offering podcasts or podcast services, includes audio books.'),
		'politics' => _HELPWINDOW('Sites of political parties, political organisations and associations; sites with political discussions.'),
		'porn' => _HELPWINDOW('Sites about all kinds of sexual content ranging from bare bosoms to hardcore porn and sm.'),
		'radiotv' => _HELPWINDOW('Domains and urls of TV and radio stations.'),
		'recreation_humor' => _HELPWINDOW('Humorous pages, comic strips, funny stories, everything which makes people laugh.'),
		'recreation_martialarts' => _HELPWINDOW('Sites dedicated to martial arts such as: karate, kung fu, taek won do as well as fighting sports sites like ufc.'),
		'recreation_restaurants' => _HELPWINDOW('Sites of restaurants as well as restaurant descriptions and commentaries.'),
		'recreation_sports' => _HELPWINDOW('All about sports: sports teams, sport discussions as well as information about sports people and the various sports themselves.'),
		'recreation_travel' => _HELPWINDOW('Sites with information about foreign countries, travel companies, travel fares, accommodations and everything else that has to do with travel.'),
		'recreation_wellness' => _HELPWINDOW('Sites about treatments for feeling internally and externally healthy and beautiful again.'),
		'redirector' => _HELPWINDOW('Sites that actively help to bypass url filters by accepting urls via form and play a proxing and redirecting role.'),
		'religion' => _HELPWINDOW('Sites with religious content: all kind of churches, sects, religious interpretations, etc.'),
		'remotecontrol' => _HELPWINDOW('Sites offering the service to remotely access computers, especially (but not limited to going) through firewalls. This does not cover traditional VPN.'),
		'ringtones' => _HELPWINDOW('Sites that offer the download of ringtones or present other information about ringtones.'),
		'science_astronomy' => _HELPWINDOW('Sites of institutions as well as of amateurs about all topics of astronomy.'),
		'science_chemistry' => _HELPWINDOW('Sites of institutions as well as of amateurs about all topics of chemistry.'),
		'searchengines' => _HELPWINDOW('Collection of search engines and directory sites.'),
		'sex_lingerie' => _HELPWINDOW('Sites selling and presenting sexy lingerie.'),
		'sex_education' => _HELPWINDOW('Sites explaining the biological functions of the body concerning sexuality as well as sexual health.'),
		'shopping' => _HELPWINDOW('Sites offering online shopping and price comparisons.'),
		'socialnet' => _HELPWINDOW('Sites bringing people together (social networking) be it for friendship or for business.'),
		'spyware' => _HELPWINDOW('Sites that try to actively install software or lure the user in doing so in order to spy the surfing behaviour (or worse). The home calling sites where the collected information is sent, are listed too.'),
		'tracker' => _HELPWINDOW('Sites keeping an eye on where you surf and what you do in a passive manner. Covers web bugs, counters and other tracking mechanisms in web pages that do not interfere with the local computer yet collect information about the surfing person for later analysis.'),
		'updatesites' => _HELPWINDOW('List to allow necessary downloads from vendors.'),
		'urlshortener' => _HELPWINDOW('Sites offering short links for URLs. '),
		'violence' => _HELPWINDOW('Sites about killing and harming people. Covers anything about brutality and beastiality.'),
		'warez' => _HELPWINDOW('Collection of sites offering programs to break licence keys, licence keys themselves, cracked software and other copyrighted material.'),
		'weapons' => _HELPWINDOW('Sites offering all kinds of weapons or accessories for weapons: Firearms, knifes, swords, bows, etc. Armory shops are included as well as sites holding general information about arms (manufacturing, usage).'),
		'webmail' => _HELPWINDOW('Sites that offer web-based email services.'),
		'webphone' => _HELPWINDOW('Sites that enable user to phone via the Internet. Any site where users can voice-chat with each other.'),
		'webradio' => _HELPWINDOW('Sites that offer listening to music and radio live streams.'),
		'webtv' => _HELPWINDOW('Sites offering TV streams via Internet.'),
	);
	
	$msg.= ''._HELPWINDOW("Although this is called a 'blacklist', the categories can be used as white or grey lists also. Being listed does not infer that the site is bad, these are just lists of sites.\n\n");
	
	$deschtml= '<table class="table"><tr><th>'._HELPWINDOW('Category').'</th><th>'._HELPWINDOW('Description').'</th></tr>';
	foreach ($catdescs as $cat => $desc) {
		$deschtml.= "<tr><td id=\"cat\">$cat</td><td id=\"catdesc\">$desc</td></tr>";
	}
	$deschtml.= "</table>";

	$msg.= $deschtml;
}

$LogFile= $_SESSION[$View->Model]['LogFile'];
$SearchSite= $_SESSION[$View->Model]['SearchSite'];

if (filter_has_var(INPUT_POST, 'SearchSite')) {
	$SearchSite= filter_input(INPUT_POST, 'SearchSite');
	$_SESSION[$View->Model]['SearchSite']= $SearchSite;
	
	if ($View->Controller($Output, 'SearchSite', $SearchSite)) {
		$LogFile= $Output[0];
		$_SESSION[$View->Model]['LogFile']= $LogFile;
	}
	else {
		$LogFile= FALSE;
	}
}

require_once($VIEW_PATH.'/header.php');
		
PrintSiteCategorySearchForm($SearchSite);

/// @attention $LogFile may be NULL too, not just FALSE or a string
if ($LogFile) {
	ProcessStartLine($StartLine);
	UpdateLogsPageSessionVars($LinesPerPage, $SearchRegExp);

	/// @todo GetLogs here, compute LogSize using Logs, this is double work otherwise
	$View->Controller($Output, 'GetFileLineCount', $LogFile, $SearchRegExp);
	$LogSize= $Output[0];

	ProcessNavigationButtons($LinesPerPage, $LogSize, $StartLine, $HeadStart);

	PrintLogHeaderForm($StartLine, $LogSize, $LinesPerPage, $SearchRegExp, $CustomHiddenInputs);
	?>
	<table id="logline">
		<?php
		PrintTableHeaders($View->Model);

		$View->Controller($Output, 'GetLogs', $LogFile, $HeadStart, $LinesPerPage, $SearchRegExp);
		$Logs= json_decode($Output[0], TRUE);

		$LineCount= $StartLine + 1;
		foreach ($Logs as $Log) {
			$View->PrintLogLine($Log, $LineCount++);
		}
		?>
	</table>
	<?php
}
AppendCatsDescTable($View->ConfHelpMsg);

PrintHelpWindow($View->ConfHelpMsg);
require_once($VIEW_PATH.'/footer.php');
?>
