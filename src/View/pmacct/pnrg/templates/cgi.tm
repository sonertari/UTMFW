#!/usr/local/bin/rrdcgi 

<HTML>
<META HTTP-EQUIV="Expires" CONTENT="<RRD::TIME::NOW "%a %b %e %T %Z %Y">">
<META HTTP-EQUIV="Refresh" CONTENT=300>
<TITLE>__RRDROOT__</TITLE>
<HEAD>
<LINK HREF="site.css" rel="stylesheet" type="text/css">
</HEAD>
<BODY BGCOLOR="#E5E5E5">

Last update of __RRDROOT__ was on <RRD::TIME::LAST __RRDFILE__ "%A, %b %d %H:%M %Z %Y">
<BR><BR>
<P ALIGN="center">
<PRE>
<RRD::GRAPH __IMGROOT__.1hr.gif 
     --title "__RRDROOT__ as of <RRD::TIME::NOW '%A %b %d %H:%M %Z %Y'>"
     --width 600 --height 150
     --start -1hours
     --interlaced
     --lazy
     COMMENT:"last hour (one minute averages)\c"
     COMMENT:"\n"
     __DEFS__
     __PLOTS__
>

<RRD::GRAPH __IMGROOT__.8hr.gif 
     --title "__RRDROOT__ as of <RRD::TIME::NOW '%A %b %d %H:%M %Z %Y'>"
     --width 600 --height 150
     --start -8hours
     --interlaced
     --lazy
     COMMENT:"last eight hours (five minute averages)\c"
     COMMENT:"\n"
     __DEFS__
     __PLOTS__
>

<RRD::GRAPH __IMGROOT__.1day.gif 
     --title "__RRDROOT__ as of <RRD::TIME::NOW '%A %b %d %H:%M %Z %Y'>"
     --width 600 --height 150
     --start -1day
     --interlaced
     --lazy
     COMMENT:"last day (five minute averages)\c"
     COMMENT:"\n"
     __DEFS__
     __PLOTS__
>

<RRD::GRAPH __IMGROOT__.1wk.gif 
     --title "__RRDROOT__ as of <RRD::TIME::NOW '%A %b %d %H:%M %Z %Y'>"
     --width 600 --height 150
     --start -1week
     --interlaced
     --lazy
     COMMENT:"last week (30 minute averages)\c"
     COMMENT:"\n"
     __DEFS__
     __PLOTS__
>

<RRD::GRAPH __IMGROOT__.1mon.gif 
     --title "__RRDROOT__ as of <RRD::TIME::NOW '%A %b %d %H:%M %Z %Y'>"
     --width 600 --height 150
     --start -1month
     --interlaced
     --lazy
     COMMENT:"last month (two hour averages)\c"
     COMMENT:"\n"
     __DEFS__
     __PLOTS__
>

<RRD::GRAPH __IMGROOT__.1yr.gif 
     --title "__RRDROOT__ as of <RRD::TIME::NOW '%A %b %d %H:%M %Z %Y'>"
     --width 600 --height 150
     --start -1year
     --interlaced
     --lazy
     COMMENT:"last year (one day averages)\c"
     COMMENT:"\n"
     __DEFS__
     __PLOTS__
>
</PRE>
</P>
-- This web page has been created on <RRD::TIME::NOW "%a %b %e %T %Z %Y"> by pnrg
</BODY>
</HTML>
