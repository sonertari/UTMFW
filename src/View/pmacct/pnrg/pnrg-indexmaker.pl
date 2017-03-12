#!/usr/bin/perl -w

#
# pnrg is Copyright (C) 2006 by Pedro Sanchez
#
# This program is free software; you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation; either version 2 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
#

use Getopt::Long;
use File::Copy;

my $spooldir = '';
my $webdir = '';

GetOptions(
	"spool=s" => \$spooldir,
	"webdir=s" => \$webdir,
);

$spooldir = '.' if ( $spooldir eq '' );

my @cgis = ();
my $index_page;
my $menu_page;
my $css_page;
my $index_fname = $spooldir . "/index.html";
my $menu_fname = $spooldir . "/menu.html";
my $css_fname = $spooldir . "/site.css";

my @desc_array = ();
my $old_root = '';
my $idx;

opendir(LIST, $spooldir) || die "ERROR: Can't open $spooldir: $!";
@cgis = grep { /\.cgi$/ } readdir(LIST);
@cgis = sort (@cgis);
closedir(LIST);

$menu_page = "<HTML>\n";
$menu_page .= "<HEAD>\n";
$menu_page .= "<LINK HREF=\"site.css\" rel=\"stylesheet\" type=\"text/css\">\n";
$menu_page .= "<script language=\"JavaScript1.2\">\n";
$menu_page .= "<!--\n\n";

$menu_page .= "//Smart Folding Menu tree- By Dynamic Drive (rewritten 03/03/02)\n";
$menu_page .= "//For full source code and more DHTML scripts, visit http://www.dynamicdrive.com\n";
$menu_page .= "//This credit MUST stay intact for use\n";

$menu_page .= "var head=\"display:''\"\n";
$menu_page .= "img1=new Image()\n";
$menu_page .= "img1.src=\"list.gif\"\n";
$menu_page .= "img2=new Image()\n";
$menu_page .= "img2.src=\"list.gif\"\n\n";

$menu_page .= "var ns6=(document.getElementById&&!document.all||window.opera)\n";
$menu_page .= "var ie4=document.all&&navigator.userAgent.indexOf(\"Opera\")==-1\n\n";

$menu_page .= "function checkcontained(e) {\n";
$menu_page .= "var iscontained=0\n";
$menu_page .= "cur=ns6? e.target : event.srcElement\n";
$menu_page .= "i=0\n";
$menu_page .= "if (cur.id==\"foldheader\")\n";
$menu_page .= "iscontained=1\n";
$menu_page .= "else\n";
$menu_page .= "while (ns6&&cur.parentNode||(ie4&&cur.parentElement)) {\n";
$menu_page .= "if (cur.id==\"foldheader\"||cur.id==\"foldinglist\") {\n";
$menu_page .= "iscontained=(cur.id==\"foldheader\")? 1 : 0\n";
$menu_page .= "break\n";
$menu_page .= "}\n";
$menu_page .= "cur=ns6? cur.parentNode : cur.parentElement\n";
$menu_page .= "}\n\n";
$menu_page .= "if (iscontained) {\n";
$menu_page .= "var foldercontent=ns6? cur.nextSibling.nextSibling : cur.all.tags(\"UL\")[0]\n";
$menu_page .= "if (foldercontent.style.display==\"none\") {\n";
$menu_page .= "foldercontent.style.display=\"\"\n";
$menu_page .= "cur.style.listStyleImage=\"url(list.gif)\"\n";
$menu_page .= "}\n";
$menu_page .= "else {\n";
$menu_page .= "foldercontent.style.display=\"none\"\n";
$menu_page .= "cur.style.listStyleImage=\"url(list.gif)\"\n";
$menu_page .= "}\n";
$menu_page .= "}\n";
$menu_page .= "}\n\n";

$menu_page .= "if (ie4||ns6)\n";
$menu_page .= "document.onclick=checkcontained\n";
$menu_page .= "//-->\n";
$menu_page .= "</script>\n";
$menu_page .= "</HEAD>\n";
$menu_page .= "<BODY BGCOLOR=\"#000000\"><DIV ID=\"menu\"><BR>\n";
$menu_page .= "<B>UTMFW pnrg</B><BR><BR>\n";
# $menu_page .= "<UL>\n";

foreach my $entry (@cgis) {
	my $root = $entry;
	my $desc_fname;
	my $elem;

	$root =~ s/\.cgi$//;
	$desc_fname = $spooldir . "/" . $root . ".desc";

	if ( stat($desc_fname) ) { 
	        open ( DESC, "< $desc_fname" ) or die "ERROR: Can't open $desc_fname: $!";
		$root = <DESC>;
	        close(DESC);
	}

	chomp( $root );
	$elem = $root . "," . $entry;
	push( @desc_array, $elem );
}

@desc_array = sort(@desc_array);

$idx = 0;
foreach my $entry (@desc_array) {
	my ( $desc, $cgi_fname ) = split( /,/, $entry );
	my $root;
	my $last = 0;

	if ($desc =~ /\+/) {
		$root = $desc;
		$root =~ s/\+(.*)$//;

		# XXX: support for maximum one level
		if ( $1 =~ /\+/ ) {
			print "ERROR: multiple nesting levels are currently not supported ( $desc ).\n";
			exit(1);
		}

		$desc = $1;
	}
	else {
		$root = '';
	}

	$last = 1 if ($idx == $#desc_array);
	$menu_page .= build_tree($old_root, $root, $cgi_fname, $desc, $last);

	$old_root = $root;
	$idx++;
}

$menu_page .= "</DIV></BODY></HTML>\n";

# Let's write down the MENU 
open ( MENU, "> $menu_fname" ) or die "ERROR: Can't open $menu_fname: $!";
print MENU $menu_page;
close(MENU);

print "INFO: Successfully created MENU: $menu_fname\n";


$index_page =  "<HTML><HEAD>";
$index_page .= "<META NAME=\"ROBOTS\" CONTENT=\"NOINDEX, NOFOLLOW\"><TITLE>pnrg</TITLE>";
$index_page .= "<LINK HREF=\"site.css\" rel=\"stylesheet\" type=\"text/css\"></HEAD>\n";
$index_page .= "<FRAMESET BORDER=\"0\" FRAMEBORDER=\"0\" FRAMESPACING=\"0\" COLS=\"200,*\"><FRAME SRC=\"menu.html\" NAME=\"side\" TARGET=\"main\">\n";
$index_page .= "<FRAME NAME=\"main\"></FRAMESET>\n";
$index_page .= "<NOFRAMES>";
$index_page .= "</NOFRAMES></HTML>\n";


# Let's write down the INDEX 
open ( INDEX, "> $index_fname" ) or die "ERROR: Can't open $index_fname: $!";
print INDEX $index_page;
close(INDEX);

print "INFO: Successfully created INDEX: $index_fname\n";


open ( CSS, "> $css_fname" ) or die "ERROR: Can't open $css_fname: $!";
print CSS <<EOT;
body {font-family: Arial, Verdana, sans; margin-top: 20px;}
#menu  {font-family: Arial, Verdana, sans;font-size: 100%;color:#FFFFFF;}
#main  {font-family: Arial, Verdana, sans; margin-top: 100%;color:#000000;}
#menu ul{ margin-top: 5px; }
#menu li{ margin-left: -17px;padding-right: 3px;}
/* */
#main SPAN.titleyw a {padding-left: 8px; text-decoration:none;font-family: Verdana;font-size:11px;font-weight:bold;color:#663300;}
#main SPAN.titleyw a:visited {text-decoration:none;}
#main SPAN.titleyw a:hover { text-decoration:none;color:#996600;}
#main SPAN.titlebl a {padding-left: 8px; text-decoration:none;font-family: Verdana;font-size:11px;font-weight:bold;color:#000099;}
#main SPAN.titlebl a:visited {text-decoration:none;}
#main SPAN.titlebl a:hover { text-decoration:none;color:#0066FF;}
/* LINK */
A { color: #00B900; text-decoration:underline;} 
A:VISITED {color:#11FF11;  text-decoration:underline;}
A:ACTIVE {color:#000000; text-decoration:underline;}
A:HOVER {color: #FFFFFF; text-decoration:underline;}
/* LIST */
#foldheader{padding-left:1em; cursor:pointer; cursor:hand ; font-weight:bold ; margin-left:1px; 
list-style-image:url(list.gif)}
/* #foldinglist{padding-left:1em ; margin-left:1px ; list-style-image:url(list.gif)} */
#foldinglist{padding-left:1em ; margin-left:1px }
EOT
close(CSS);

print "INFO: Successfully created CSS: $css_fname\n";

# copy("www/list.gif", $spooldir."/list.gif") or die "ERROR: Can't copy list.gif\n";
print "INFO: Successfully copied GIF files\n";

sub build_tree {
	my $old_root = shift;
	my $root = shift;
	my $cgi_fname = shift;
	my $entry = shift;
	my $last = shift;
	my $page;

	if ( not ( $old_root eq $root ) ) {
		if ( not ( $old_root eq '' ) ) {
			$page .= "</UL></UL>\n";
		}
		if ( not ( $root eq '' ) ) {
			$page .= "<UL><LI ID=\"foldheader\">$root</LI>\n"; 
			# $page .= "<UL ID=\"foldinglist\" STYLE=\"display:none\" STYLE=&{head};><LI ID=\"foldheader\">$root</LI>\n";
			$page .= "<UL ID=\"foldinglist\" STYLE=\"display:none\" STYLE=&{head};>\n"; # <LI ID=\"foldheader\">$root</LI>\n";
		}
	}

	$page .= "<LI><A HREF=\"" . $cgi_fname ."\" TARGET=\"main\">" . $entry . "</A></LI>\n";

	$page .= "</UL></UL>\n" if ($last == 1 and not ( $root  eq '' ) );

	return $page;
}
