<?php
require_once("setup.inc");
require_once("tools.inc");

if (isset($symon['layout_dir'])) {
    $layout_dir = dir($symon['layout_dir']);
    $layouts = array();
    while ($layout = $layout_dir->read()) {
	$file_layout = $symon['layout_dir'].'/'.$layout;
	$extension = get_extension($layout);
	$layout = basename($layout, '.layout');
	if (is_file($file_layout) && $extension == 'layout') {
	    $symon['defaults']['layout']['namedvalues'][$layout] = $layout;
	}
    }
    $symon['defaults']['layout']['namedvalues']['default'] = 'default';
}

/* force full error reporting */
ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("error_reporting" , E_ALL);

$symon['rrdtool_debug']=1;
$symon['layout_debug']=1;
$symon['graph_debug']=1;


/* Test PHP configuration */
print '<pre>Printing apache configuration: ';
print "\xa".' server = ';
if (isset($HTTP_SERVER_VARS["SERVER_SIGNATURE"])) {
	if (preg_match("/.*Apache\/([0-9.]+)/i", $HTTP_SERVER_VARS["SERVER_SIGNATURE"], $match)) {
		print "apache v".$match[1];
	} else {
		print "unknown (".$HTTP_SERVER_VARS["SERVER_SIGNATURE"].")";
	}
} else {
	print "unknown";
}
print "\xa".' php = ' . PHP_VERSION;
print "\xa".' document_root = ';
if (isset($HTTP_SERVER_VARS["DOCUMENT_ROOT"])) {
	print $HTTP_SERVER_VARS["DOCUMENT_ROOT"].' ';
	if ($HTTP_SERVER_VARS["DOCUMENT_ROOT"] == '/htdocs') {
		print "(chrooted?)";
	}
}
print "\xa".'done</pre>';

print "<pre>Testing session: ";
require_once("class_session.inc");
$session->_test();
$session->getform('start');
$session->getform('end');
$session->getform('width');
$session->getform('heigth');
$session->getform('layout');
$session->getform('timespan');
$session->getform('size');
print_r($session);
print "\xa"."done</pre>";

print "<pre>Testing rrdtool: ";
require_once("class_rrdtool.inc");
$r = new RRDTool();
$r->_test();
print "\xa"."done</pre>";

print "<pre>Testing cache: ";
require_once("class_cache.inc");
$cache->_test();
print "\xa"."done</pre>";

print "<pre>Testing lexer: ";
require_once("class_lexer.inc");
$lp = new Lexer();
$lp->_test();
print "\xa"."done</pre>";

print "<pre>Testing layout: ";
require_once("class_layout.inc");
$layout = $session->get('layout');
print $layout;
if ($layout == 'default') {
    $l = new Layout('');
} else {
    $l = new Layout($layout);
}
$l->_test();
print "\xa"."done</pre>";
?>
