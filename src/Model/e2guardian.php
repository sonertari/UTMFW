<?php
/*
 * Copyright (C) 2004-2022 Soner Tari
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

require_once($MODEL_PATH.'/model.php');

class E2guardian extends Model
{
	public $Name= 'e2guardian';
	// Need root at startup
	public $User= 'root|_e2guard\w*';
	
	private $ipLists= array();
	private $listConfig= array();
	
	public $NVPS= '=';
	public $ConfFile= '/etc/e2guardian/e2guardian.conf';
	
	protected $confDir= '/etc/e2guardian/';
	
	private $re_DgNet= '';
	private $re_DgRange= '';
	
	public $LogFile= '/var/log/e2guardian/e2guardian.log';
						
	public $VersionCmd= '/usr/local/sbin/e2guardian -v';

	public $PidFile= UTMFWDIR.'/run/e2guardian.pid';
	
	function __construct()
	{
		global $Re_Ip;
		
		parent::__construct();
		
		$this->StartCmd= '/usr/local/sbin/e2guardian';
		
		$this->re_DgNet= "$Re_Ip\/$Re_Ip";
		$this->re_DgRange= "$Re_Ip-$Re_Ip";
		
		$this->ipLists= array(
				'exceptionlist'	=> 'exceptionclient',
				'bannedlist' 	=> 'bannedclient',
			);

		$this->listConfig= array(
			'sites'			=> array(
				'key'				=> 'sitelist',
				'GetFunc'			=> 'GetSiteList',
				'DelFunc'			=> 'DelSiteList',
				'AddFunc'			=> 'AddSiteList',
				),

			'urls'			=>	array(
				'key'				=> 'urllist',
				'GetFunc'			=> 'GetUrlList',
				'DelFunc'			=> 'DelUrlList',
				'AddFunc'			=> 'AddUrlList',
				),

			'exts'			=>	array(
				'key'				=> 'fileextlist',
				'exception'			=> 'exceptionextension',
				'banned'			=> 'bannedextension',
				),

			'mimes'			=>	array(
				'key'				=> 'mimelist',
				'exception'			=> 'exceptionmime',
				'banned'			=> 'bannedmime',
				),

			// Used by CreateNewGroup() only
			'filesite'		=>	array(
				'key'				=> 'sitelist',
				'exception'			=> 'exceptionfile',
				),

			// Used by CreateNewGroup() only
			'fileurl'		=>	array(
				'key'				=> 'urllist',
				'exception'			=> 'exceptionfile',
				),

			'dm_exts'		=>	array(
				'key'				=> FALSE,
				'MetaConfigFile'	=> $this->confDir.'downloadmanagers/default.conf',
				'exception'			=> 'managedextensionlist',
				),

			'dm_mimes'		=>	array(
				'key'				=> FALSE,
				'MetaConfigFile'	=> $this->confDir.'downloadmanagers/default.conf',
				'exception'			=> 'managedmimetypelist',
				),

			'virus_sites'	=>	array(
				'key'				=> 'sitelist',
				'exception'			=> 'exceptionvirus',
				'GetFunc'			=> 'GetSiteList',
				'DelFunc'			=> 'DelSiteList',
				'AddFunc'			=> 'AddSiteList',
				),

			'virus_urls'	=>	array(
				'key'				=> 'urllist',
				'exception'			=> 'exceptionvirus',
				'GetFunc'			=> 'GetUrlList',
				'DelFunc'			=> 'DelUrlList',
				'AddFunc'			=> 'AddUrlList',
				),

			'virus_exts'	=>	array(
				'key'				=> 'fileextlist',
				'exception'			=> 'exceptionvirus',
				),

			'virus_mimes'	=>	array(
				'key'				=> 'mimelist',
				'exception'			=> 'exceptionvirus',
				),

			// Used by Cats only
			'phrases'		=>	array(
				'key'				=> FALSE,
				'exception'			=> 'exceptionphraselist',
				'banned'			=> 'bannedphraselist',
				'weighted'			=> 'weightedphraselist',
				),
			);

		$this->Commands= array_merge(
			$this->Commands,
			array(
				'DelUserFilterGrp'=>	array(
					'argv'	=>	array(IPADR|DGIPRANGE|NAME),
					'desc'	=>	_('Delete filter group user'),
					),

				'SetUserFilterGrp'=>	array(
					'argv'	=>	array(IPADR|DGIPRANGE|NAME, NUM),
					'desc'	=>	_('Add filter group user'),
					),

				'DelIp'		=>	array(
					'argv'	=>	array(NAME, IPADR|DGIPRANGE),
					'desc'	=>	_('Delete IP'),
					),

				'AddIp'		=>	array(
					'argv'	=>	array(NAME, IPADR|DGIPRANGE),
					'desc'	=>	_('Add IP'),
					),

				'CreateNewGroup'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Create new group'),
					),
				
				'GetGroupCount'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Get group count'),
					),

				'GetGroupUserList'=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Get group user list'),
					),

				'GetAuthIpList'=>	array(
					'argv'	=>	array(NAME),
					'desc'	=>	_('Get auth ip list'),
					),
				
				'GetList'		=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get list'),
					),

				'GetExtMimeList'=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get ext/mime list'),
					),

				'DelSiteUrl'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, URL),
					'desc'	=>	_('Delete list item'),
					),
				
				'AddSiteUrl'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, URL),
					'desc'	=>	_('Add list item'),
					),

				'DisableExtMime'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME),
					'desc'	=>	_('Disable ext/mime'),
					),

				'EnableExtMime'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME),
					'desc'	=>	_('Enable ext/mime'),
					),

				'DelExtMime'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME),
					'desc'	=>	_('Delete ext/mime'),
					),

				'AddExtMime'	=>	array(
					/// @todo EXT|MIME accepts ext for mime or visa versa, fix this
					/// @todo Is there any pattern or size for comment, 5th param?
					'argv'	=>	array(NUM, NAME, NAME, EXT|MIME, STR),
					'desc'	=>	_('Add ext/mime'),
					),

				'GetEnabledCats'=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get active cats'),
					),

				'GetDisabledCats'=>	array(
					'argv'	=>	array(NUM, NAME, NAME),
					'desc'	=>	_('Get inactive cats'),
					),

				'TurnOnCats'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, NAME, DGSUBCAT),
					'desc'	=>	_('Turn on cats'),
					),

				'TurnOffCats'	=>	array(
					'argv'	=>	array(NUM, NAME, NAME, NAME, DGSUBCAT),
					'desc'	=>	_('Turn off cats'),
					),

				'GroupExists'	=>	array(
					'argv'	=>	array(NUM),
					'desc'	=>	_('Group exists'),
					),

				'SetTemplateIps'	=>	array(
					'argv'	=>	array(IPADR),
					'desc'	=>	_('Set template ips'),
					),
				)
			);
	}

	function _getModuleInfo($start)
	{
		return array('requests'	=>	$this->getRrdValue('derive-all.rrd', $start, $result));
	}

	function GetConfFile($confname, $group)
	{
		if ($confname === 'GeneraldownloadsConfig') {
			$file= $this->confDir.'downloadmanagers/default.conf';
		}
		else if (preg_match('/^General.*Config$/', $confname)) {
			$file= $this->ConfFile;
		}
		else {
			$file= $this->confDir.'e2guardianf'.$group.'.conf';
		}
		return $file;
	}

	function SetConfig($confname)
	{
		global $GeneralbasicConfig, $GeneralfilterConfig, $GeneralscanConfig, $GenerallogsConfig, $GeneraldownloadsConfig, $GeneraladvancedConfig,
			$basicConfig, $scanConfig, $bypassConfig, $emailConfig;
		
		if ($confname !== '') {
			$this->Config= ${$confname};
		}
	}

	/**
	 * Returns the list of ips in exception or banned lists.
	 */
	function GetAuthIpList($list)
	{
		if ($filepath= $this->GetListFilePath($list)) {
			return Output($this->GetIps($filepath));
		}
		return FALSE;
	}

	/**
	 * Reads the list of IPs.
	 *
	 * @param string $file Config file pathname.
	 * @return string List of IPs.
	 */
	function GetIps($file)
	{
		global $Re_Ip;

		return $this->SearchFileAll($file, "/^\h*($Re_Ip|$this->re_DgNet|$this->re_DgRange)\b\h*(|$this->COMC.*)$/m");
	}

	function AddIp($list, $ip)
	{
		if ($filepath= $this->GetListFilePath($list)) {
			$this->DelIp($list, $ip);
			return $this->AppendToFile($filepath, $ip);
		}
		return FALSE;
	}

	function DelIp($list, $ip)
	{
		if ($filepath= $this->GetListFilePath($list)) {
			$ip= Escape($ip, '/.');
			return $this->ReplaceRegexp($filepath, "/^(\h*$ip\b.*(\s|))/m", '');
		}
		return FALSE;
	}

	function GetListFilePath($list)
	{
		if ($filepath= $this->GetFilterKeyConfFilePath($this->confDir.'e2guardian.conf', 'iplist', $this->ipLists[$list])) {
			return $filepath;
		}
		else {
			Error(_('Cannot read config').": $list");
		}
		return FALSE;
	}

	function AddSiteList($file, $site)
	{
		$this->DelSiteList($file, $site);
		return $this->AppendToFile($file, $site);
	}

	function AddUrlList($file, $url)
	{
		$this->DelUrlList($file, $url);
		return $this->AppendToFile($file, $url);
	}

	function DisableExtMime($group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->DisableExt($groupfile, $item);
		}
		return FALSE;
	}

	function DisableExt($file, $ext)
	{
		/// @todo Handle multiple entries with the same name
		$ext= Escape($ext, './');
		return $this->ReplaceRegexp($file, "/^(\h*$ext(\h*$this->COMC.*|\h*))$/m", $this->COMC.'${1}');
	}

	function EnableExtMime($group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->EnableExt($groupfile, $item);
		}
		return FALSE;
	}

	function EnableExt($file, $ext)
	{
		/// @todo Handle multiple entries with the same name
		$ext= Escape($ext, './');
		return $this->ReplaceRegexp($file, "/^\h*$this->COMC(\h*$ext(\h*$this->COMC.*|\h*))$/m", '${1}');
	}

	function AddExt($file, $ext, $cmt)
	{
		$this->DelExt($file, $ext);
		return $this->AppendToFile($file, $ext.($cmt == '' ? '' : " $this->COMC $cmt"));
	}

	function DelExt($file, $ext)
	{
		// Both extensions and mime types are deleted by this method
		$ext= Escape($ext, './');
		return $this->ReplaceRegexp($file, "/^(\h*($this->COMC\h*|)$ext\b.*(\s|))/m", '');
	}

	function DelSiteList($file, $site)
	{
		$site= Escape($site, '/.');
		return $this->ReplaceRegexp($file, "/^(\h*$site\b.*(\s|))/m", '');
	}

	function DelUrlList($file, $url)
	{
		$url= Escape($url, '/.');
		return $this->ReplaceRegexp($file, "/^(\h*$url\b.*(\s|))/m", '');
	}

	/**
	 * Gets the list of DG categories and subcats.
	 *
	 * Returns a list of all cats or subcats if those args are not provided.
	 *
	 * @param string $file Config file pathname.
	 * @param string $cat Category to get. Assumes all categories if not provided.
	 * @param string $subcat SubCategory to get. Assumes all subcats if not provided.
	 * @return string List of cats/subcats.
	 */
	function GetCats($file, $cat= '[^#\s]+', $subcat= '[^#\s]+')
	{
		return $this->SearchFileAll($file, "/^\h*\.Include.*lists\/($cat\/$subcat)>.*$/m");
	}

	/**
	 * Reads DG configuration file pathname setting.
	 *
	 * @param string $file Config file pathname.
	 * @param string $name Name of the NVP.
	 * @return string Value of the setting, file pathname.
	 */
	function GetFilterConfFilePath($file, $name)
	{
		return $this->GetNVP($file, $name, 0, "'");
	}

	/**
	 * Sets DG configuration file pathname setting.
	 *
	 * @param string $file Config file pathname.
	 * @param string $name Name of the NVP.
	 * @param string $value New value of the setting, file pathname.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetFilterConfFilePath($file, $name, $value)
	{
		return $this->SetNVP($file, $name, "'$value'");
	}

	/**
	 * Reads E2g configuration file pathname setting for the given key.
	 *
	 * @param string $file Config file pathname.
	 * @param string $key Key of the NVP.
	 * @param string $name Name of the config option.
	 * @return string Value of the setting, file pathname.
	 */
	function GetFilterKeyConfFilePath($file, $key, $name)
	{
		//iplist = 'name=exceptionclient,messageno=600,path=/etc/e2guardian/lists/exceptioniplist'
		return $this->SearchFile($file, "/^\h*$key\h*$this->NVPS\h*'\h*name\h*=\h*$name\b[^$this->COMC'\"\n]*,\h*path\h*=\h*([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", 0, "'");
	}

	/**
	 * Sets E2g configuration file pathname setting for the given key.
	 *
	 * @param string $file Config file pathname.
	 * @param string $key Key of the NVP.
	 * @param string $name Name of the config option.
	 * @param string $value New value of the setting, file pathname.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetFilterKeyConfFilePath($file, $key, $name, $value)
	{
		// The search regex ${2} matches the trailing single quote too, so don't forget to append a single quote in the replacement regex, i.e. ${3} does not have a leading single quote
		return $this->ReplaceRegexp($file, "/^(\h*$key\h*$this->NVPS\h*'\h*name\h*=\h*$name\b[^$this->COMC'\"\n]*,\h*path\h*=\h*)([^$this->COMC'\"\n]*|'[^'\n]*'|\"[^\"\n]*\"|[^$this->COMC\n]*)(\h*|\h*$this->COMC.*)$/m", '${1}'.$value.'\'${3}');
	}

	/**
	 * Gets the list off (commented) DG cats/subcats.
	 *
	 * @param string $file	Config file pathname.
	 * @param string $cat	Category to get. Assumes all categories if not provided.
	 * @param string $subcat	SubCategory to get. Assumes all subcats if not provided.
	 * @return string List of off cats/subcats.
	 */
	function GetOffCats($file, $cat= '[^#\s]+', $subcat= '[^#\s]+')
	{
		return $this->SearchFileAll($file, "/^\h*$this->COMC\h*\.Include.*lists\/($cat\/$subcat)>.*$/m");
	}

	/**
	 * Checks if site exists or gets the list of sites.
	 *
	 * @param string $file	Config file pathname.
	 * @param string $site	Site to get. Assumes all sites if not provided.
	 * @return string Site name (i.e. site exits) or list of sites.
	 */
	function GetSiteList($file, $site= '[^.#\s]+[^\s]*')
	{
		$site= Escape($site, '/.');
		return $this->SearchFileAll($file, "/^\h*($site)\b.*\v*$/m");
	}

	/**
	 * Checks if url exists or gets the list of urls.
	 */
	function GetUrlList($file, $url= '[^.#\s]+[^\s]*')
	{
		$url= Escape($url, '/.');
		return $this->SearchFileAll($file, "/^\h*($url)\b.*\v*$/m");
	}

	/**
	 * Gets the list of extensions.
	 *
	 * @todo Handle multiple entries with the same name
	 *
	 * @param string $file Config file pathname.
	 * @return string List of extensions.
	 */
	function GetExts($file)
	{
		$re_ext= '\.[a-z0-9A-Z][a-z0-9A-Z_.]{0,10}';
		$re_mime= '[a-zA-Z][a-z0-9A-Z_-]{0,20}\/[a-z0-9A-Z_.-]{0,20}';
		
		$result= array();

		$contents= file_get_contents($file);
		
		$re= "($re_ext|$re_mime)\b\h*(|$this->COMC.*)";
		if (preg_match_all("/^\h*($re)$/m", $contents, $match)) {
			$output= array_values($match[1]);
			foreach ($output as $line) {
				if (preg_match("/^$re$/", $line, $match)) {
					$result[$match[1]]= array(
						'Comment'	=> $match[2],
						'Enabled'	=> TRUE,
					);
				}
			}
		}
		
		if (preg_match_all("/^\h*$this->COMC\h*($re)$/m", $contents, $match)) {
			$output= array_values($match[1]);
			foreach ($output as $line) {
				if (preg_match("/^$re$/", $line, $match)) {
					$result[$match[1]]= array(
						'Comment'	=> $match[2],
						'Enabled'	=> FALSE,
					);
				}
			}
		}
		return Output(json_encode($result));
	}

	/**
	 * Gets the list of enabled or disabled categories.
	 */
	function GetFuncCats($func, $group, $list, $type)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			if (($output= $this->$func($groupfile)) !== FALSE) {
				return $output;
			}
			else {
				return '';
			}
		}
		return FALSE;
	}

	function TurnOffCats($group, $list, $type, $cat, $subcat)
	{
		return $this->FuncTurnCats('TurnOffCat', $group, $list, $type, $cat, $subcat);
	}

	function TurnOnCats($group, $list, $type, $cat, $subcat)
	{
		return $this->FuncTurnCats('TurnOnCat', $group, $list, $type, $cat, $subcat);
	}

	/**
	 * Enables or disables the given category.
	 */
	function FuncTurnCats($func, $group, $list, $type, $cat, $subcat)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			if (($output= $this->$func($groupfile, $cat, $subcat)) !== FALSE) {
				return $output;
			}
			else {
				return '';
			}
		}
		return FALSE;
	}

	/**
	 * Turns on (uncomments) DG cat/subcat.
	 *
	 * @param string $file Config file pathname.
	 * @param string $cat Category.
	 * @param string $subcat SubCategory. Assumes all subcats if not provided.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function TurnOnCat($file, $cat, $subcat= '[^#\s]+')
	{
		/// @todo No need to send CAT and SUBCAT separately, escape slashes in PHP and send
		return $this->ReplaceRegexp($file, "|^\h*$this->COMC(\s*\.Include.*lists/$cat/$subcat>.*)$|m", '${1}');
	}

	/**
	 * Turns off (comments out) DG cat/subcat.
	 *
	 * @param string $file Config file pathname.
	 * @param string $cat Category.
	 * @param string $subcat SubCategory. Assumes all subcats if not provided.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function TurnOffCat($file, $cat, $subcat= '[^#\s]+')
	{
		/// @todo No need to send CAT and SUBCAT separately, escape slashes in View and send here
		return $this->ReplaceRegexp($file, "|^(\h*\.Include.*lists/$cat/$subcat>.*)$|m", $this->COMC.'${1}');
	}

	function GetList($group, $list, $type)
	{
		return Output($this->GetFuncList($this->listConfig[$list]['GetFunc'], $group, $list, $type));
	}

	function GetExtMimeList($group, $list, $type)
	{
		return Output($this->GetFuncList('GetExts', $group, $list, $type));
	}

	/**
	 * Gets the list of sites or urls.
	 */
	function GetFuncList($func, $group, $list, $type)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			if (($output= $this->$func($groupfile)) !== FALSE) {
				return $output;
			}
			else {
				return '';
			}
		}
		return FALSE;
	}

	function DelSiteUrl($group, $list, $type, $item)
	{
		return $this->FuncSiteUrl($this->listConfig[$list]['DelFunc'], $group, $list, $type, $item);
	}

	function AddSiteUrl($group, $list, $type, $item)
	{
		return $this->FuncSiteUrl($this->listConfig[$list]['AddFunc'], $group, $list, $type, $item);
	}

	/**
	 * Adds or deletes the given site or url.
	 */
	function FuncSiteUrl($func, $group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->$func($groupfile, $item);
		}
		return FALSE;
	}

	function DelExtMime($group, $list, $type, $item)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->DelExt($groupfile, $item);
		}
		return FALSE;
	}

	function AddExtMime($group, $list, $type, $item, $comment)
	{
		if (($groupfile= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
			return $this->AddExt($groupfile, $item, $comment);
		}
		return FALSE;
	}

	function GetEnabledCats($group, $list, $type)
	{
		return Output($this->GetFuncCats('GetCats', $group, $list, $type));
	}

	function GetDisabledCats($group, $list, $type)
	{
		return Output($this->GetFuncCats('GetOffCats', $group, $list, $type));
	}

	/**
	 * Gets users listed in group
	 */
	function GetGroupUserList($group)
	{
		if ($filepath= $this->GetIpgroupsFilePath()) {
			return Output($this->GetGroupUsers($filepath, $group));
		}
		return FALSE;
	}

	/**
	 * Gets the list of group users.
	 *
	 * @param string $file Config file pathname.
	 * @param string $group DG group.
	 * @return string List of users, one on each line.
	 */
	function GetGroupUsers($file, $group= '[^#\s]+')
	{
		global $Re_Ip, $Re_User;

		return $this->SearchFileAll($file, "/^\h*($Re_Ip|$this->re_DgNet|$this->re_DgRange|$Re_User)\h*$this->NVPS\h*filter$group\b\h*(|$this->COMC.*)$/m");
	}

	/**
	 * Gets the ip.conf file pathname from master file.
	 */
	function GetIpgroupsFilePath()
	{
		if ($conffile= $this->GetFilterConfFilePath($this->confDir.'e2guardian.conf', 'authplugin')) {
			if ($filepath= $this->GetFilterConfFilePath($conffile, 'ipgroups')) {
				return $filepath;
			}
			else {
				Error(_('Cannot read config').': ipgroups');
			}
		}
		else {
			Error(_('Cannot read config').': authplugin');
		}
		return FALSE;
	}

	/**
	 * Adds user and filter group pair.
	 *
	 * @param string $user User to add.
	 * @param string $group Group to add user to.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetUserFilterGrp($user, $group)
	{
		if ($file= $this->GetIpgroupsFilePath()) {
			$this->DelUserFilterGrp($user, $group);
			$this->DelUserFilterGrp($user, '.*');
			return $this->AppendToFile($file, "$user = filter$group");
		}
		return FALSE;
	}

	/**
	 * Deletes user and filter group pair.
	 *
	 * @param string $user	User to delete.
	 * @param string $group	Group to delete user from.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function DelUserFilterGrp($user, $group= '[^#\s]+')
	{
		if ($file= $this->GetIpgroupsFilePath()) {
			$user= Escape($user, '/.');
			return $this->ReplaceRegexp($file, "/^(\h*$user\b\h*$this->NVPS\h*filter$group\b\h*.*(\s|))/m", '');
		}
		return FALSE;
	}

	function GetGroupFile($group, $list, $type)
	{
		$metafile= $this->GetMetaFile($group, $list);

		$key= $this->listConfig[$list]['key'];

		$name= $type;
		if (isset($this->listConfig[$list][$type])) {
			$name= $this->listConfig[$list][$type];
		}

		if ($key === FALSE) {
			if (($groupfile= $this->GetFilterConfFilePath($metafile, $name)) !== FALSE) {
				return $groupfile;
			}
		} else {
			if (($groupfile= $this->GetFilterKeyConfFilePath($metafile, $key, $name)) !== FALSE) {
				return $groupfile;
			}
		}
		Error(_('Cannot find group configuration file').": $type $list $key $name");
		return FALSE;
	}

	function SetGroupFile($group, $list, $type, $value)
	{
		$metafile= $this->GetMetaFile($group, $list);

		$key= $this->listConfig[$list]['key'];

		$name= $type;
		if (isset($this->listConfig[$list][$type])) {
			$name= $this->listConfig[$list][$type];
		}

		if ($key === FALSE) {
			return $this->SetFilterConfFilePath($metafile, $name, $value);
		} else {
			return $this->SetFilterKeyConfFilePath($metafile, $key, $name, $value);
		}
	}

	/**
	 * Gets meta file for the given group or list.
	 *
	 * E2guardian configuration is divided into many files,
	 * both group meta files and lists for groups.
	 *
	 * @param int $group Group
	 * @param string $list List name used as array index: sites, ext, etc.
	 * @return string Meta file
	 */
	function GetMetaFile($group, $list)
	{
		if (isset($this->listConfig[$list]['MetaConfigFile'])) {
			$metafile= $this->listConfig[$list]['MetaConfigFile'];
		}
		else {
			$metafile= $this->confDir.'e2guardianf'.$group.'.conf';
		}
		return $metafile;
	}

	/**
	 * Gets number of groups
	 */
	function GetGroupCount()
	{
		return Output($this->GetNVP($this->confDir.'e2guardian.conf', 'filtergroups'));
	}

	/**
	 * Checks if the meta file exists for the given group.
	 */
	function GroupExists($group)
	{
		return file_exists($this->confDir.'e2guardianf'.$group.'.conf');
	}

	/**
	 * Creates a new web filter group.
	 *
	 * Creates a new web filter group based on the active group for
	 * the current session.
	 *
	 * Makes a copy of some of the configuration files listed in
	 * $filenames array. Missing files cause warnings, but are not fatal,
	 * so that restricted and privileged groups can be copied too.
	 *
	 * @param int $group Group to copy
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function CreateNewGroup($group)
	{
		/// @todo What other files shall we copy over to the new filter group?
		$filesToCopy= array(
			'sites'		=> array('exception', 'grey', 'banned'),
			'urls'		=> array('exception', 'grey', 'banned'),
			'exts'		=> array('banned'),
			'mimes'		=> array('banned'),
			'filesite'	=> array('exception'),
			'fileurl'	=> array('exception'),
			);

		$result= TRUE;
		$fatal= FALSE;
		$info= "GetNVP: $this->ConfFile: filtergroups";
		if (($output= $this->GetNVP($this->ConfFile, 'filtergroups')) !== FALSE) {
			Error(_('SUCCESS').": $info");
			ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

			$newgroup= $output + 1;

			$conffile= $this->confDir."e2guardianf$group.conf";
			$newconffile= $this->confDir."e2guardianf$newgroup.conf";

			$info= 'File exists check: '.$newconffile;
			if (!file_exists($newconffile)) {
				Error(_('SUCCESS').": $info");
				ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

				$info= "File copy: $conffile $newconffile";
				exec("/bin/cp -p $conffile $newconffile 2>&1", $output, $retval);
				if ($retval === 0) {
					Error(_('SUCCESS').": $info");
					ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

					foreach ($filesToCopy as $list => $typeList) {
						foreach ($typeList as $type) {
							$info= "GetGroupFile: $conffile: $list $type";
							if (($output= $this->GetGroupFile($group, $list, $type)) !== FALSE) {
								Error(_('SUCCESS').": $info");
								ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

								$groupfile= rtrim(trim($output, "'"), $group);
								$newgroupfile= $groupfile.$newgroup;

								$info= "File exists check: $newgroupfile";
								if (!file_exists($newgroupfile)) {
									Error(_('SUCCESS').": $info");
									ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

									$info= "File copy: $groupfile $newgroupfile";
									exec("/bin/cp -p $groupfile $newgroupfile 2>&1", $output, $retval);
									if ($retval === 0) {
										Error(_('SUCCESS').": $info");
										ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");

										$info= "SetGroupFile: $newconffile: $list $type $newgroupfile";
										if ($this->SetGroupFile($newgroup, $list, $type, $newgroupfile)) {
											Error(_('SUCCESS').": $info");
											ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");
										}
										else {
											$result= FALSE;
											$fatal= TRUE;
											Error(_('FAILED').": $info");
											ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
										}
									}
									else {
										$result= FALSE;
										$fatal= TRUE;
										Error(_('FAILED').": $info ".implode("\n", $output));
										ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info ".implode("\n", $output));
									}
								}
								else {
									$result= FALSE;
									Error(_('FAILED').": $info");
									ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
								}
							}
							else {
								$result= FALSE;
								Error(_('FAILED').": $info");
								ctlr_syslog(LOG_WARNING, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
							}
							if ($fatal) {
								break;
							}
						}
						if ($fatal) {
							break;
						}
					}
				}
				else {
					$result= FALSE;
					$fatal= TRUE;
					Error(_('FAILED').": $info ".implode("\n", $output));
					ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info ".implode("\n", $output));
				}
			}
			else {
				$result= FALSE;
				$error= _('FAILED').": $info";
				Error(_('FAILED').": $info");
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
			}
		}
		else {
			$result= FALSE;
			$fatal= TRUE;
			Error(_('FAILED').": $info");
			ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
		}

		if ($fatal === FALSE) {
			$info= "SetNVP: $this->ConfFile: filtergroups $newgroup";
			if ($this->SetNVP($this->ConfFile, 'filtergroups', $newgroup)) {
					Error(_('SUCCESS').": $info");
					ctlr_syslog(LOG_NOTICE, __FILE__, __FUNCTION__, __LINE__, "SUCCESS: $info");
			}
			else {
				$result= FALSE;
				$fatal= TRUE;
				Error(_('FAILED').": $info");
				ctlr_syslog(LOG_ERR, __FILE__, __FUNCTION__, __LINE__, "FAILED: $info");
			}

			if (!$result) {
				Error("\n"._("One or more actions failed while creating new group\nExamine the report above to make sure this is the intended result!"));
			}
			else {
				Error("\n"._('Group created successfully').": $newgroup");
			}
		}

		if ($fatal) {
			Error("\n"._('FATAL ERRORS in creating new group'));
		}
		return !$fatal && $result;
	}

	/**
	 * Updates template files with the given ip.
	 *
	 * Used by installer or when system interface configuration is modified.
	 */
	function SetTemplateIps($ip)
	{
		global $Re_Ip;
		
		$langpath= '/usr/local/share/e2guardian/languages/';
	
		$re= "|(http://)($Re_Ip)(/images/utmfw.png)|m";
		$retval=  $this->ReplaceRegexp($langpath.'ukenglish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template_nobypass.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template_nobypass.html', $re, '${1}'.$ip.'${3}');
		
		$re= "|(http://)($Re_Ip)(/images/info.png)|m";
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/fancydmtemplate.html', $re, '${1}'.$ip.'${3}');
	
		
		$re= "|(http://)($Re_Ip)(/images/error.png)|m";
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'ukenglish/template_nobypass.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template.html', $re, '${1}'.$ip.'${3}');
		$retval&= $this->ReplaceRegexp($langpath.'turkish/template_nobypass.html', $re, '${1}'.$ip.'${3}');

		return $retval;
	}
}

/**
 * Basic group configuration.
 */
$basicConfig = array(
    'groupmode' => array(
        'type' => UINT_0_2,
		),
    'groupname' => array(
        'type' => STR_SING_QUOTED,
		),
    'reportinglevel' => array(
        'type' => INT_M1_0_3,
		),
    'accessdeniedaddress' => array(
        'type' => STR_SING_QUOTED,
		),
    'nonstandarddelimiter' => array(
        'type' => STR_on_off,
		),
	);

/**
 * Content filtering configuration.
 */
$scanConfig = array(
    'weightedphrasemode' => array(
        'type' => UINT_0_2,
		),
    'naughtynesslimit' => array(
        'type' => UINT,
		),
    'categorydisplaythreshold' => array(
        'type' => INT_M1_0_UP,
		),
    'embeddedurlweight' => array(
        'type' => UINT_0_1,
		),
    'enablepics' => array(
        'type' => STR_on_off,
		),
    'disablecontentscan' => array(
        'type' => STR_on_off,
		),
    'contentscanexceptions' => array(
        'type' => STR_on_off,
		),
    'deepurlanalysis' => array(
        'type' => STR_on_off,
		),
    'textmimetypes' => array(
        'type' => STR_SING_QUOTED,
		),
    'maxuploadsize' => array(
        'type' => INT_M1_0_UP,
		),
	);

/**
 * Bypass configuration.
 */
$bypassConfig = array(
    'bypass' => array(
        'type' => INT_M1_0_UP,
		),
    'bypasskey' => array(
        'type' => STR_SING_QUOTED,
		),
    'infectionbypass' => array(
        'type' => INT_M1_0_UP,
		),
    'infectionbypasskey' => array(
        'type' => STR_SING_QUOTED,
		),
    'infectionbypasserrorsonly' => array(
        'type' => STR_on_off,
		),
	);

/**
 * E-mail configuration.
 */
$emailConfig = array(
    'usesmtp' => array(
        'type' => STR_on_off,
		),
    'mailfrom' => array(
        'type' => STR_SING_QUOTED,
		),
    'avadmin' => array(
        'type' => STR_SING_QUOTED,
		),
    'contentadmin' => array(
        'type' => STR_SING_QUOTED,
		),
    'avsubject' => array(
        'type' => STR_SING_QUOTED,
		),
    'contentsubject' => array(
        'type' => STR_SING_QUOTED,
		),
    'notifyav' => array(
        'type' => STR_on_off,
		),
    'notifycontent' => array(
        'type' => STR_on_off,
		),
    'thresholdbyuser' => array(
        'type' => STR_on_off,
		),
    'violations' => array(
        'type' => UINT,
		),
    'threshold' => array(
        'type' => UINT,
		),
	);

/**
 * General log configuration.
 */
$GenerallogsConfig = array(
    'loglevel' => array(
        'type' => UINT_0_3,
		),
    'logexceptionhits' => array(
        'type' => UINT_0_2,
		),
    'logfileformat' => array(
        'type' => UINT_1_4,
		),
    'maxlogitemlength' => array(
        'type' => UINT,
		),
    'anonymizelogs' => array(
        'type' => STR_on_off,
		),
    'loglocation' => array(),
    'statlocation' => array(),
    'logclienthostnames' => array(
        'type' => STR_on_off,
		),
    'logconnectionhandlingerrors' => array(
        'type' => STR_on_off,
		),
    'logchildprocesshandling' => array(
        'type' => STR_on_off,
		),
    'logadblocks' => array(
        'type' => STR_on_off,
		),
	);

/**
 * General basic configuration.
 */
$GeneralbasicConfig = array(
    'language' => array(
        'type' => STR_SING_QUOTED,
		),
    'languagedir' => array(),
    'filterip' => array(
        'type' => IP,
		),
    'filterports' => array(
        'type' => PORT,
		),
    'proxyip' => array(
        'type' => IP,
		),
    'proxyport' => array(
        'type' => PORT,
		),
    'reportinglevel' => array(
        'type' => INT_M1_0_3,
		),
    'usecustombannedimage' => array(
        'type' => STR_on_off,
		),
    'custombannedimagefile' => array(),
    'usecustombannedflash' => array(
        'type' => STR_on_off,
		),
    'custombannedflashfile' => array(),
	);

/**
 * General filter configuration.
 */
$GeneralfilterConfig = array(
    'showweightedfound' => array(
        'type' => STR_on_off,
		),
    'weightedphrasemode' => array(
        'type' => UINT_0_2,
		),
    'urlcachenumber' => array(
        'type' => UINT,
		),
    'urlcacheage' => array(
        'type' => UINT,
		),
    'scancleancache' => array(
        'type' => STR_on_off,
		),
    'phrasefiltermode' => array(
        'type' => UINT_0_3,
		),
    'preservecase' => array(
        'type' => UINT_0_2,
		),
    'hexdecodecontent' => array(
        'type' => STR_on_off,
		),
    'forcequicksearch' => array(
        'type' => STR_on_off,
		),
    'reverseaddresslookups' => array(
        'type' => STR_on_off,
		),
    'reverseclientiplookups' => array(
        'type' => STR_on_off,
		),
    'createlistcachefiles' => array(
        'type' => STR_on_off,
		),
    'maxheaderlines' => array(
        'type' => UINT,
		),
	);

/**
 * General content scanner configuration.
 */
$GeneralscanConfig = array(
    'maxcontentfiltersize' => array(
        'type' => UINT,
		),
    'maxcontentramcachescansize' => array(
        'type' => UINT,
		),
    'maxcontentfilecachescansize' => array(
        'type' => UINT,
		),
    'deletedownloadedtempfiles' => array(
        'type' => STR_on_off,
		),
    'initialtrickledelay' => array(
        'type' => UINT,
		),
    'trickledelay' => array(
        'type' => UINT,
		),
    'contentscannertimeout' => array(
        'type' => UINT,
		),
    'recheckreplacedurls' => array(
        'type' => STR_on_off,
		),
	);

/**
 * Fancy download manager plugin configuration.
 */
$GeneraldownloadsConfig = array(
    'useragentregexp' => array(
        'type' => STR_SING_QUOTED,
		),
    'maxdownloadsize' => array(
        'type' => UINT,
		),
	);

/**
 * General advanced configuration.
 */
$GeneraladvancedConfig = array(
    'proxytimeout' => array(
        'type' => UINT,
		),
    'proxyfailureloginterval' => array(
        'type' => UINT,
		),
    'proxyexchange' => array(
        'type' => UINT,
		),
    'pcontimeout' => array(
        'type' => UINT,
		),
    'forwardedfor' => array(
        'type' => STR_on_off,
		),
    'usexforwardedfor' => array(
        'type' => STR_on_off,
		),
    'xforwardedforfilterip' => array(
        'type' => IP,
		),
    'maxchildren' => array(
        'type' => UINT,
		),
    'httpworkers' => array(
        'type' => UINT,
		),
    'minchildren' => array(
        'type' => UINT,
		),
    'minsparechildren' => array(
        'type' => UINT,
		),
    'preforkchildren' => array(
        'type' => UINT,
		),
    'maxsparechildren' => array(
        'type' => UINT,
		),
    'maxagechildren' => array(
        'type' => UINT,
		),
    'gentlechunk' => array(
        'type' => UINT,
		),
    'maxips' => array(
        'type' => UINT,
		),
    'nologger' => array(
        'type' => STR_on_off,
		),
    'softrestart' => array(
        'type' => STR_on_off,
		),
    'mailer' => array(
        'type' => STR_SING_QUOTED,
		),
	);
?>
