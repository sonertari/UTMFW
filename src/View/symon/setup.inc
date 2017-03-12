<?php
/* DEFAULT USER CONFIGURATION STARTS HERE */

/* rrdtool_path :   path to rrdtool executable
 * cache_dir :      where generated graphs (and their definitions) are
 *                  stored. garbage collection, ie file removal will happen
 *                  automatically; this directory cannot be shared with other
 *                  files.
 * cache_duration : number of seconds until files are refreshed
 * combine :        combine all 'type' rrdfiles for a host in one graph
 * host_tree :      where is the host tree located symon expects
 *                  a <host>/<rrds> directory structure.
 * isolate_hosts :  show a single host only when the default layout is selected
 * layout_dir :     location of layout files - remove this value to
 *                  disable user layouts
 *
 */

/* running OpenBSD, apache chrooted:
$symon['rrdtool_path']='/bin/rrdtool';
$symon['cache_dir']='/symon/cache';
$symon['host_tree']='/symon/rrds';
$symon['layout_dir']='/symon';
*/

/* running OpenBSD, apache not chrooted:*/
$symon['rrdtool_path']='/usr/local/bin/rrdtool';
$SYMON_PATH= dirname(__FILE__);
$symon['cache_dir']=$SYMON_PATH.'/cache';
$symon['host_tree']=$SYMON_PATH.'/rrds';
$symon['layout_dir']=$SYMON_PATH.'/layouts';

/* running FreeBSD, apache not chrooted: 
$symon['rrdtool_path']='/usr/local/bin/rrdtool';
$symon['cache_dir']='/usr/local/www/symon/cache';
$symon['host_tree']='/usr/local/www/symon/rrds';
$symon['layout_dir']='/usr/local/www/symon';
*/

$symon['cache_duration']=20;
$symon['isolate_hosts']=1;
$symon['combine']['pfq']=1;
$symon['combine']['io']=1;
$symon['options']['df']='bytes';
/* $symon['options']['df']='blocks'; */

/* DEFAULT USER CONFIGURATION ENDS HERE */

/* Troubleshooting section */
/* $symon['rrdtool_debug']=1; // make rrdtool dump state apache error log */
/* $symon['layout_debug']=1;  // make layout dump state on screen */
/* $symon['graph_debug']=1;   // make graph dump state on screen */
/* $symon['lexer_debug']=1;   // make lexer dump state on screen */

/* make php verbose */
// ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_reporting' , E_ALL);

/* maximum time for graph to wait for file to appear */
$symon['graph_max_wait']=300;

/* constraints for rrdtool command line arguments */
$symon['constraints'] = array(
    '--width'  => array('max' => 2048, 'min' => 32, 'default' => 300),
    '-w'       => array('max' => 2048, 'min' => 32, 'default' => 300),
    '--heigth' => array('max' => 2048, 'min' => 32, 'default' => 225),
    '-h'       => array('max' => 2048, 'min' => 32, 'default' => 225),
    '--start'  => array('max' => 4294967296,    'min' => -4294967296,  'default' => -86400),
    '-s'       => array('max' => 4294967296,    'min' => -4294967296,  'default' => -86400),
    '--end'    => array('max' => 4294967296,    'min' => -4294967296,  'default' => -1),
    '-e'       => array('max' => 4294967296,    'min' => -4294967296,  'default' => -1),
    );

/* setup defaults for form variables */
$symon['defaults'] = array(
    'size' => array(
	'type'        => 'named',
// 	'default'     => 'normal',
	'default'     => 'long',
	'namedvalues' => array(
	    'small'     => array( 'width' =>  200, 'heigth' => 125 ),
	    'normal'    => array( 'width' =>  300, 'heigth' => 225 ),
	    'big'       => array( 'width' =>  600, 'heigth' => 450 ),
	    'long'      => array( 'width' =>  700, 'heigth' => 250 ),
	    'huge'      => array( 'width' => 1024, 'heigth' => 640 ),
	    'custom'    => ''
	    )
	),
    'timespan' => array(
	'type'        => 'named',
// 	'default'     => 'last 24 hours',
	'default'     => 'last hour',
	'namedvalues' => array(
	    'last hour'       => array( 'start' => -3600 ),
	    'last 8 hours'    => array( 'start' => -28800 ),
	    'last 24 hours'   => array( 'start' => -86400 ),
	    'last week'       => array( 'start' => -604800 ),
	    'last month'      => array( 'start' => -2419200 ),
//	    'six_months' => array( 'start' => -14515200 ),
	    'custom'     => ''
	    )
	),
    'layout' => array(
	'type'        => 'named',
	'default'     => 'default',
	'namedvalues' => array(
	    'default'    => ''
	    )
	),
    'start' => array(
	'type'        => 'integer',
	'default'     => $symon['constraints']['--start']['default'],
	'bounds'      => $symon['constraints']['--start'],
	),
    'end' => array(
	'type'        => 'integer',
	'default'     => $symon['constraints']['--end']['default'],
	'bounds'      => $symon['constraints']['--end'],
	),
    'width' => array(
	'type'        => 'integer',
	'default'     => $symon['constraints']['--width']['default'],
	'bounds'      => $symon['constraints']['--width'],
	),
    'heigth' => array(
	'type'        => 'integer',
	'default'     => $symon['constraints']['--heigth']['default'],
	'bounds'      => $symon['constraints']['--heigth'],
	),
    );
/* maximum buffer for file loads */
$symon['loadbuffer'] = 81920;

/* color codes for combine graphs */
$symon['color'] = array(
    'io'  => array('#570043',
		   '#6B1A52',
	           '#481D85',
		   '#6B2F98',
		   '#76325E',
		   '#78449D',
		   '#87486D',
		   '#8F5975',
		   '#8F5DAE',
		   '#9F73B6',
		   '#A17386',
		   '#A57C72',
		   '#B18AC2',
		   '#C2AACD'),
    'pfq' => array('#005743',
		   '#1A6B52',
	           '#1D4885',
		   '#2F6B98',
		   '#32765E',
		   '#44789D',
		   '#48876D',
		   '#598F75',
		   '#5D8FAE',
		   '#739FB6',
		   '#73A186',
		   '#7CA572',
		   '#8AB1C2',
		   '#AAC2CD'));
?>
