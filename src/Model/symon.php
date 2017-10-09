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

/** @file
 * System monitoring.
 */

require_once($MODEL_PATH.'/monitoring.php');

class Symon extends Monitoring
{
	public $Name= 'symon';
	public $User= '_symon';
	
	private $layoutsPath= '/var/www/htdocs/utmfw/View/symon/layouts';
	private $rrdsPath= '/var/www/htdocs/utmfw/View/symon/rrds/localhost';
	
	public $VersionCmd= '/usr/local/libexec/symon -v 2>&1';

	protected $LogFilter= 'symon';

	function __construct()
	{
		global $TmpFile;
		
		parent::__construct();
		
		$this->StartCmd= "/usr/local/libexec/symon > $TmpFile 2>&1 &";
		
		$this->Commands= array_merge(
			$this->Commands,
			array(
				'SetIfs'=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set symon ifs'),
					),
				
				'SetConf'=>	array(
					'argv'	=>	array(NAME, NAME),
					'desc'	=>	_('Set symon conf'),
					),
				
				'RenderLayout'=>	array(
					'argv'	=>	array(NAME, NUM|NONE, NUM|NONE),
					'desc'	=>	_('Render layout'),
					),
				
				'SetCpus'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Set symon cpus'),
					),
				
				'SetSensors'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Set symon sensors'),
					),
				
				'SetPartitions'=>	array(
					'argv'	=>	array(),
					'desc'	=>	_('Set symon partitions'),
					),
				)
			);
	}
	
	/**
	 * Sets interface names in the layout files.
	 * 
	 * @param string $lanif Internal interface.
	 * @param string $wanif External interface.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetIfs($lanif, $wanif)
	{
		$re= '|^(\s*graph\s+rrdfile\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*title\s*=\s*"Internal Interface\s+\()(\w+\d+)(\)\s*"\s*;)|ms';
		$retval=  $this->ReplaceRegexp($this->layoutsPath.'/ifs.layout', $re, '${1}'.$lanif.'${3}'.$lanif.'${5}');
		
		$re= '|^(\s*graph\s+rrdfile\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*title\s*=\s*"External Interface\s+\()(\w+\d+)(\)\s*"\s*;)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/ifs.layout', $re, '${1}'.$wanif.'${3}'.$wanif.'${5}');

		$re= '|(\s+lan_rrd\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/states.layout', $re, '${1}'.$lanif.'${3}');
		
		$re= '|(\s+wan_rrd\s*=\s*[\w/]+/if_)(\w+\d+)(\.rrd\s*,\s*)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/states.layout', $re, '${1}'.$wanif.'${3}');
		
		return $retval;
	}

	/**
	 * Sets interface names in the symon and symux config files.
	 * 
	 * @param string $lanif Internal interface.
	 * @param string $wanif External interface.
	 * @return bool TRUE on success, FALSE on fail.
	 */
	function SetConf($lanif, $wanif)
	{
		/// @todo See the new OpenBSD installer to get this and sensors paths
		$ncpu= 1;
		if (($hwncpu= $this->_getSysCtl('hw.ncpu')) !== FALSE) {
			if (preg_match('|^hw.ncpu=(\d+)$|ms', $hwncpu, $match)) {
				$ncpu= $match[1];
			}
		}

		$cpus= '';
		for ($i= 0; $i < $ncpu; $i++) {
			$cpus.= "	cpu($i),\n";
		}
		
		$others= "	mem,\n	pf,\n	mbuf,\n";
		
		$ifs= "	if(lo0),\n	if($lanif),\n	if($wanif),\n";
		
		$partitions= $this->_getPartitions();
		$parts= '';
		$disks= array();
		foreach ($partitions as $part => $mdir) {
			if (preg_match('|/dev/((\w+\d+)[a-z]+)|', $part, $match)) {
				$parts.= '	df('.$match[1]."),\n";
				if (!in_array($match[2], $disks)) {
					$disks[]= $match[2];
				}
			}
		}

		$ios= '';
		foreach ($disks as $io) {
			$ios.= "	io($io),\n";
		}
		
		$sensors= '';
		if (($sensorslist= $this->GetSensors()) !== FALSE) {
			if (isset($sensorslist['temp']) && count($sensorslist['temp']) > 0) {
				foreach ($sensorslist['temp'] as $s) {
					$sensors.= "	sensor($s),\n";
				}
			}
			
			if (isset($sensorslist['fan']) && count($sensorslist['fan']) > 0) {
				foreach ($sensorslist['fan'] as $s) {
					$sensors.= "	sensor($s),\n";
				}
			}
		}

		$proclist= array(
			'httpd',
			'e2guardian',
			'snort',
			'clamd',
			'p3scan',
			'perl',
			'sshd',
			'squid',
			'smtp-gated',
			'named',
			'dhcpd',
			'sockd',
			'imspector',
			'ftp-proxy',
			'openvpn',
			'sslproxy',
		);
		$procs= '';
		foreach ($proclist as $p) {
			$procs.= "	proc($p),\n";
		}
		
		$conf= "\n".$cpus.$others.$ifs.$ios.$parts.$sensors.$procs;
		
		$re= '|(\s*monitor\s*\{\h*)([^\}]*)(\s*\})|ms';
		$retval=  $this->ReplaceRegexp('/etc/symon.conf', $re, '${1}'.$conf.'${3}');

		$conf= preg_replace('/(	)/ms', '		', $conf);
		
		$re= '|(\s*source\s*127\.0\.0\.1\s*\{\s*accept\s*\{\h*)([^\}]*)(\h*\})|ms';
		$retval&= $this->ReplaceRegexp('/etc/symux.conf', $re, '${1}'.$conf.'	${3}');

		return $retval;
	}

	/**
	 * Generates graphs for the given layout.
	 * 
	 * This is a hacky workaround until we move symon code in the View to the Model.
	 * 
	 * @param string $layout Layout name.
	 * @return array Graph titles and files.
	 */
	function RenderLayout($layout, $width= 700, $heigth= 250)
	{
		/// XXX
		global $SRC_ROOT, $symon, $cache, $session, $chr2html, $runtime;
		
		/// XXX
		/// For classifying gettext strings into files.
		function _TITLE($str)
		{
			return _($str);
		}

		require_once ("$SRC_ROOT/View/symon/class_session.inc");
		require_once ("$SRC_ROOT/View/symon/class_layout.inc");

		$session->getform('start');
		$session->getform('end');
		$session->getform('width', $width);
		$session->getform('heigth', $heigth);
		$session->getform('layout');
		$session->getform('timespan');
		$session->getform('size', 'custom');

		$l = new Layout($layout);

		$graphs = $l->render(false);

		require_once("$SRC_ROOT/View/symon/class_rrdtool.inc");

		foreach ($graphs as $title => $g) {
			if (preg_match("/^([0-9a-f]+)/", $g, $match)) {
				$key = $match[1];
				$filename = $cache->getfilename($key);
				$extension = get_extension($filename);

				if ($extension == 'txt') {
					$definition = load($filename);
					$cache->expire_key($key);
					$rrdtool = new RRDTool();
					$graph_file = $cache->obtain_filecache($key);
					$result = $rrdtool->graph($graph_file, $definition);

				} else {
					$graph_file = $filename;
					$result = 1;
				}
			}
		}

		return Output(json_encode($graphs));
	}

	function SetCpus()
	{
		$ncpu= 1;
		if (($hwncpu= $this->_getSysCtl('hw.ncpu')) !== FALSE) {
			if (preg_match('|^hw.ncpu=(\d+)$|ms', $hwncpu, $match)) {
				$ncpu= $match[1];
			}
		}
		
		$layout= '';
		for ($c= 0; $c < $ncpu; $c++) {
			$layout.= "graph	rrdfile=$this->rrdsPath/cpu$c.rrd, title=\"CPU $c\";\n";
		}

		$re= '|(\s*group\h+name\h*=\h*"CPU\h+Load";\s*)(.*)|ms';
		return $this->ReplaceRegexp($this->layoutsPath.'/cpus.layout', $re, '${1}'.$layout);
	}

	function SetSensors()
	{
		if (($sensorslist= $this->GetSensors()) !== FALSE) {
			$layout= '';
			if (isset($sensorslist['temp']) && count($sensorslist['temp']) > 0) {
				foreach ($sensorslist['temp'] as $s) {
					$layout.= "graph	rrdfile=$this->rrdsPath/sensor_$s.rrd, title=\"Temperature ($s)\";\n";
				}
			}
			
			$layout.= 'group	name="Fan";'."\n";
			if (isset($sensorslist['fan']) && count($sensorslist['fan']) > 0) {
				foreach ($sensorslist['fan'] as $s) {
					$layout.= "graph	rrdfile=$this->rrdsPath/sensor_$s.rrd, title=\"Fan ($s)\";\n";
				}
			}
		
			$re= '|(\s*group\h+name\h*=\h*"Temperature";\s*)(.*)|ms';
			return $this->ReplaceRegexp($this->layoutsPath.'/sensors.layout', $re, '${1}'.$layout);
		}
		return FALSE;
	}

	function SetPartitions()
	{
		$partitions= $this->_getPartitions();
		
		$layout= '';
		$disks= array();
		foreach ($partitions as $part => $mdir) {
			if (preg_match('|/dev/((\w+\d+)[a-z]+)|', $part, $match)) {
				$p= $match[1];
				$layout.= "graph	rrdfile=$this->rrdsPath/df_$p.rrd, title=\"Partition $p ($mdir)\";\n";
				
				if (!in_array($match[2], $disks)) {
					$disks[]= $match[2];
				}
			}
		}
		
		$re= '|(\s*group\h+name\h*=\h*"Partitions\h+Usages";\s*)(.*)|ms';
		$retval=  $this->ReplaceRegexp($this->layoutsPath.'/partitions.layout', $re, '${1}'.$layout);
	
		$layout= '';
		foreach ($disks as $d) {
			$layout.= "graph	rrdfile=$this->rrdsPath/io_$d.rrd, title=\"Disk $d\";\n";
		}
		
		$re= '|(\s*group\h+name\h*=\h*"Disk\h+I/O";\s*)(.*)|ms';
		$retval&= $this->ReplaceRegexp($this->layoutsPath.'/disks.layout', $re, '${1}'.$layout);

		return $retval;
	}
}
?>
