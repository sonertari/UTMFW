#!/bin/sh
# Copyright (C) 2004-2021 Soner Tari
#
# This file is part of UTMFW.
#
# UTMFW is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# UTMFW is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
#
# Some of the rrd graph options used here are borrowed from symon and pmacct 
# rrd graph scripts.

if [ $# -lt 6 ]; then
	echo "$0: Not enough arguments [6]: $#"
	exit 1
fi

GRAPHS_FOLDER="/tmp/utmfw/dashboard"
VIEW_GRAPHS_FOLDER="/var/www/htdocs/utmfw/View/system"

# Make sure the graphs output folder exists
[[ ! -d $GRAPHS_FOLDER ]] && mkdir -p $GRAPHS_FOLDER && ln -fs $GRAPHS_FOLDER $VIEW_GRAPHS_FOLDER
# Go to the graphs output folder
cd $GRAPHS_FOLDER

if [[ -f cpu.png ]]; then
    eval $(stat -s cpu.png)
    TIMEDIFF=$(($(date "+%s")-$st_mtime))

    if [[ $TIMEDIFF -lt 10 ]]; then
	    echo "Will NOT generate dashboard graphs too frequently, last generate time < 10 sec: $TIMEDIFF"
		exit 1
    fi
fi

START=$1
PING_GATEWAY_ADDR=$2
PING_REMOTE_ADDR=$3
INT_IF=$4
EXT_IF=$5
DISK=$6

# -i|--interlaced: "If images are interlaced they become visible on browsers more quickly."
# -E|--slope-mode: "Some people favor a more 'organic' look for their graphs"
# -x none: Do not print x-axis labels
# -y none: Do not print y-axis labels
# -g|--no-legend: "Suppress generation of the legend; only render the graph."
# --border width: "Width in pixels for the 3d border drawn around the image."
# -D|---full-size-mode: "the width and height specify the final dimensions of 
#   the output image and the canvas is automatically resized to fit"
# Grey out canvas, background, and arrow to make them invisible
GENERAL_OPTS="-i -E -x none -y none -g --border 0 -D -c CANVAS#eaedef11 -c BACK#eaedef11 -c ARROW#eaedef11"

# Default graph dimensions, except for the ping graph
SIZE="-w 358 -h 130"

COLLECTD_RRD_ROOT="/var/collectd/localhost"
SYMON_RRD_ROOT="/var/www/htdocs/utmfw/View/symon/rrds/localhost"
PMACCT_RRD_ROOT="/var/www/htdocs/utmfw/View/pmacct/protograph"

RRDTOOL="/usr/local/bin/rrdtool"

# Ping plugin
GATEWAY_RRD="$COLLECTD_RRD_ROOT/ping/ping-$PING_GATEWAY_ADDR.rrd"
REMOTE_RRD="$COLLECTD_RRD_ROOT/ping/ping-$PING_REMOTE_ADDR.rrd"

if [[ ! -f $REMOTE_RRD ]]; then
    echo "Cannot find $REMOTE_RRD, the remote ping host may be unreachable"

    $RRDTOOL graph ping.png $GENERAL_OPTS -w 358 -h 75 -s $START \
        -t "Ping Times" \
        DEF:gateway=$GATEWAY_RRD:value:AVERAGE \
        AREA:gateway#FF0000:gateway >/dev/null
else
    $RRDTOOL graph ping.png $GENERAL_OPTS -w 358 -h 75 -s $START \
        -t "Ping Times" \
        DEF:gateway=$GATEWAY_RRD:value:AVERAGE \
        DEF:remote=$REMOTE_RRD:value:AVERAGE \
        AREA:remote#FF8C00:remote \
        STACK:gateway#FF0000:gateway >/dev/null
fi

# System
$RRDTOOL graph cpu.png $GENERAL_OPTS $SIZE -s $START \
    -t "CPU" \
    -u 100 \
    --rigid \
    DEF:A=$COLLECTD_RRD_ROOT/cpu/percent-user.rrd:value:AVERAGE \
    DEF:B=$COLLECTD_RRD_ROOT/cpu/percent-nice.rrd:value:AVERAGE \
    DEF:C=$COLLECTD_RRD_ROOT/cpu/percent-system.rrd:value:AVERAGE \
    DEF:D=$COLLECTD_RRD_ROOT/cpu/percent-interrupt.rrd:value:AVERAGE \
    DEF:E=$COLLECTD_RRD_ROOT/cpu/percent-idle.rrd:value:AVERAGE \
    CDEF:nodata=A,UN,0,* \
    LINE1:nodata#FF0000 \
    AREA:A#008194:user \
    STACK:B#00FFFF:nice \
    STACK:C#da5400:system \
    STACK:D#9932CC:interrupt \
    STACK:E#F5FFFA:idle >/dev/null

$RRDTOOL graph memory.png $GENERAL_OPTS $SIZE -s $START \
    -t "Memory" \
    -b 1024 \
    DEF:A=$SYMON_RRD_ROOT/mem.rrd:real_active:AVERAGE \
    DEF:B=$SYMON_RRD_ROOT/mem.rrd:real_total:AVERAGE \
    DEF:C=$SYMON_RRD_ROOT/mem.rrd:free:AVERAGE \
    DEF:D=$SYMON_RRD_ROOT/mem.rrd:swap_used:AVERAGE \
    DEF:E=$SYMON_RRD_ROOT/mem.rrd:swap_total:AVERAGE \
    CDEF:nodata=A,UN,0,* \
    LINE1:nodata#FF0000 \
    AREA:B#008B8B:real \
    STACK:C#3CB371:free \
    LINE1:A#00FFFF:active \
    LINE1:D#888C00:"swap used" \
    LINE2:E#FF8C00:"swap total" >/dev/null

$RRDTOOL graph diskio.png $GENERAL_OPTS $SIZE -s $START \
    -t "Disk I/O" \
    DEF:rx=$SYMON_RRD_ROOT/io_$DISK.rrd:rxfer:AVERAGE \
    DEF:wx=$SYMON_RRD_ROOT/io_$DISK.rrd:wxfer:AVERAGE \
    DEF:seeks=$SYMON_RRD_ROOT/io_$DISK.rrd:seeks:AVERAGE \
    DEF:rb=$SYMON_RRD_ROOT/io_$DISK.rrd:rbytes:AVERAGE \
    DEF:wb=$SYMON_RRD_ROOT/io_$DISK.rrd:wbytes:AVERAGE \
    CDEF:nwb=wb,-1,* \
    CDEF:nwx=wx,-1,* \
    CDEF:nodata=rx,UN,0,* \
    LINE1:nodata#FF0000 \
    AREA:rb#008194:rbytes \
    LINE1:rx#9932CC:rxfer \
    AREA:nwb#da5400:wbytes \
    LINE1:nwx#DDA0DD:wxfer \
    LINE1:seeks#F5FFFA:seeks >/dev/null

# Pmacct protocols
$RRDTOOL graph protocols.png $GENERAL_OPTS $SIZE -s $START \
    -t "Protocols" \
    DEF:alltraf=$PMACCT_RRD_ROOT/utmfw.rrd:all:AVERAGE \
    DEF:ftptraf=$PMACCT_RRD_ROOT/utmfw.rrd:ftp:AVERAGE \
    DEF:sshtraf=$PMACCT_RRD_ROOT/utmfw.rrd:ssh:AVERAGE \
    DEF:smtptraf=$PMACCT_RRD_ROOT/utmfw.rrd:smtp:AVERAGE \
    DEF:dnstraf=$PMACCT_RRD_ROOT/utmfw.rrd:dns:AVERAGE \
    DEF:httptraf=$PMACCT_RRD_ROOT/utmfw.rrd:http:AVERAGE \
    DEF:unknowntraf=$PMACCT_RRD_ROOT/utmfw.rrd:unknown:AVERAGE \
    CDEF:allbits=alltraf,8,*,60,/ \
    CDEF:ftpbits=ftptraf,8,*,60,/ \
    CDEF:sshbits=sshtraf,8,*,60,/ \
    CDEF:smtpbits=smtptraf,8,*,60,/ \
    CDEF:dnsbits=dnstraf,8,*,60,/ \
    CDEF:httpbits=httptraf,8,*,60,/ \
    CDEF:unknownbits=unknowntraf,8,*,60,/ \
    AREA:allbits#D0D0D0:all \
    LINE1:ftpbits#FF0000:ftp \
    LINE1:sshbits#00FF00:ssh \
    LINE1:smtpbits#0000FF:smtp \
    LINE1:dnsbits#FFFF00:dns \
    LINE1:httpbits#FF00FF:http \
    LINE1:unknownbits#F49028:unknown >/dev/null

# Packet Filter
$RRDTOOL graph pf.png $GENERAL_OPTS $SIZE -s $START \
    -t "States" \
    DEF:s=$SYMON_RRD_ROOT/pf.rrd:states_entries:AVERAGE \
    DEF:si=$SYMON_RRD_ROOT/pf.rrd:states_inserts:AVERAGE \
    DEF:sr=$SYMON_RRD_ROOT/pf.rrd:states_removals:AVERAGE \
    DEF:ss=$SYMON_RRD_ROOT/pf.rrd:states_searches:AVERAGE \
    CDEF:msr=-1,sr,* \
    AREA:s#a800ae:entries \
    LINE1:msr#FF0000:removals \
    LINE1:si#0000FF:inserts \
    LINE2:ss#008194:state_searches >/dev/null

$RRDTOOL graph dataxfer.png $GENERAL_OPTS $SIZE -s $START \
    -t "Data Transfer" \
    DEF:A=$SYMON_RRD_ROOT/pf.rrd:bytes_v4_in:AVERAGE \
    DEF:B=$SYMON_RRD_ROOT/pf.rrd:bytes_v4_out:AVERAGE \
    DEF:C=$SYMON_RRD_ROOT/pf.rrd:bytes_v6_in:AVERAGE \
    DEF:D=$SYMON_RRD_ROOT/pf.rrd:bytes_v6_out:AVERAGE \
    CDEF:inb=A,C,+,8,* \
    CDEF:outb=B,D,+,8,* \
    CDEF:nodata=A,UN,0,* \
    LINE1:nodata#FF0000 \
    AREA:inb#008194:incoming \
    LINE1:outb#da5400:outgoing >/dev/null

$RRDTOOL graph intif.png $GENERAL_OPTS $SIZE -s $START \
    -t "Internal Interface" \
    DEF:in=$SYMON_RRD_ROOT/if_$INT_IF.rrd:ibytes:AVERAGE \
    DEF:out=$SYMON_RRD_ROOT/if_$INT_IF.rrd:obytes:AVERAGE \
    DEF:inp=$SYMON_RRD_ROOT/if_$INT_IF.rrd:ipackets:AVERAGE \
    DEF:outp=$SYMON_RRD_ROOT/if_$INT_IF.rrd:opackets:AVERAGE \
    DEF:coll=$SYMON_RRD_ROOT/if_$INT_IF.rrd:collisions:AVERAGE \
    CDEF:nodata=in,UN,0,* \
    CDEF:inb=in,8,* \
    CDEF:outb=out,8,* \
    CDEF:noutb=outb,-1,* \
    CDEF:pmax=inb,100,/,102,* \
    CDEF:nmax=noutb,100,/,102,* \
    CDEF:totp=inp,outp,+ \
    CDEF:per=coll,totp,/,100,* \
    CDEF:p0=per,0,EQ,INF,0,IF \
    CDEF:p10=per,10,LE,INF,0,IF,per,1,GT,INF,0,IF,MIN \
    CDEF:p20=per,20,LE,INF,0,IF,per,10,GT,INF,0,IF,MIN \
    CDEF:p30=per,30,LE,INF,0,IF,per,20,GT,INF,0,IF,MIN \
    CDEF:p40=per,40,LE,INF,0,IF,per,30,GT,INF,0,IF,MIN \
    CDEF:p50=per,50,LE,INF,0,IF,per,40,GT,INF,0,IF,MIN \
    CDEF:p60=per,60,LE,INF,0,IF,per,50,GT,INF,0,IF,MIN \
    CDEF:p70=per,70,LE,INF,0,IF,per,60,GT,INF,0,IF,MIN \
    CDEF:p80=per,80,LE,INF,0,IF,per,70,GT,INF,0,IF,MIN \
    CDEF:p90=per,80,LE,INF,0,IF,per,80,GT,INF,0,IF,MIN \
    CDEF:p100=per,100,LE,INF,0,IF,per,90,GT,INF,0,IF,MIN \
    CDEF:n0=p0,-1,* \
    CDEF:n10=p10,-1,* \
    CDEF:n20=p20,-1,* \
    CDEF:n30=p30,-1,* \
    CDEF:n40=p40,-1,* \
    CDEF:n50=p50,-1,* \
    CDEF:n60=p60,-1,* \
    CDEF:n70=p70,-1,* \
    CDEF:n80=p80,-1,* \
    CDEF:n90=p90,-1,* \
    CDEF:n100=p100,-1,* \
    LINE1:pmax \
    LINE1:nmax \
    LINE1:nodata#FF0000 \
    AREA:inb#008194:in \
    STACK:p0#FAFFFA \
    STACK:p10#FFFFE6 \
    STACK:p20#FFD900 \
    STACK:p30#FD6724 \
    STACK:p40#E61800 \
    STACK:p50#AB2934 \
    STACK:p60#B2888B \
    STACK:p70#CC91BA \
    STACK:p80#6A2990 \
    STACK:p90#0571B0 \
    STACK:p100#000000 \
    AREA:noutb#da5400:out \
    STACK:n0#FFFFFF:" = 0%" \
    STACK:n10#F0E0E0:" <10%" \
    STACK:n20#FFD900:" <20%" \
    STACK:n30#FD6724:" <30%" \
    STACK:n40#E61800:" <40%" \
    STACK:n50#AB2934:" <50%" \
    STACK:n60#B2888B:" <60%" \
    STACK:n70#CC91BA:" <70%" \
    STACK:n80#6A2990:" <80%" \
    STACK:n90#0571B0:" <90%" \
    STACK:n100#000000:" <100%" >/dev/null

$RRDTOOL graph extif.png $GENERAL_OPTS $SIZE -s $START \
    -t "External Interface" \
    DEF:in=$SYMON_RRD_ROOT/if_$EXT_IF.rrd:ibytes:AVERAGE \
    DEF:out=$SYMON_RRD_ROOT/if_$EXT_IF.rrd:obytes:AVERAGE \
    DEF:inp=$SYMON_RRD_ROOT/if_$EXT_IF.rrd:ipackets:AVERAGE \
    DEF:outp=$SYMON_RRD_ROOT/if_$EXT_IF.rrd:opackets:AVERAGE \
    DEF:coll=$SYMON_RRD_ROOT/if_$EXT_IF.rrd:collisions:AVERAGE \
    CDEF:nodata=in,UN,0,* \
    CDEF:inb=in,8,* \
    CDEF:outb=out,8,* \
    CDEF:noutb=outb,-1,* \
    CDEF:pmax=inb,100,/,102,* \
    CDEF:nmax=noutb,100,/,102,* \
    CDEF:totp=inp,outp,+ \
    CDEF:per=coll,totp,/,100,* \
    CDEF:p0=per,0,EQ,INF,0,IF \
    CDEF:p10=per,10,LE,INF,0,IF,per,1,GT,INF,0,IF,MIN \
    CDEF:p20=per,20,LE,INF,0,IF,per,10,GT,INF,0,IF,MIN \
    CDEF:p30=per,30,LE,INF,0,IF,per,20,GT,INF,0,IF,MIN \
    CDEF:p40=per,40,LE,INF,0,IF,per,30,GT,INF,0,IF,MIN \
    CDEF:p50=per,50,LE,INF,0,IF,per,40,GT,INF,0,IF,MIN \
    CDEF:p60=per,60,LE,INF,0,IF,per,50,GT,INF,0,IF,MIN \
    CDEF:p70=per,70,LE,INF,0,IF,per,60,GT,INF,0,IF,MIN \
    CDEF:p80=per,80,LE,INF,0,IF,per,70,GT,INF,0,IF,MIN \
    CDEF:p90=per,80,LE,INF,0,IF,per,80,GT,INF,0,IF,MIN \
    CDEF:p100=per,100,LE,INF,0,IF,per,90,GT,INF,0,IF,MIN \
    CDEF:n0=p0,-1,* \
    CDEF:n10=p10,-1,* \
    CDEF:n20=p20,-1,* \
    CDEF:n30=p30,-1,* \
    CDEF:n40=p40,-1,* \
    CDEF:n50=p50,-1,* \
    CDEF:n60=p60,-1,* \
    CDEF:n70=p70,-1,* \
    CDEF:n80=p80,-1,* \
    CDEF:n90=p90,-1,* \
    CDEF:n100=p100,-1,* \
    LINE1:pmax \
    LINE1:nmax \
    LINE1:nodata#FF0000 \
    AREA:inb#008194:in \
    STACK:p0#FAFFFA \
    STACK:p10#FFFFE6 \
    STACK:p20#FFD900 \
    STACK:p30#FD6724 \
    STACK:p40#E61800 \
    STACK:p50#AB2934 \
    STACK:p60#B2888B \
    STACK:p70#CC91BA \
    STACK:p80#6A2990 \
    STACK:p90#0571B0 \
    STACK:p100#000000 \
    AREA:noutb#da5400:out \
    STACK:n0#FFFFFF:" = 0%" \
    STACK:n10#F0E0E0:" <10%" \
    STACK:n20#FFD900:" <20%" \
    STACK:n30#FD6724:" <30%" \
    STACK:n40#E61800:" <40%" \
    STACK:n50#AB2934:" <50%" \
    STACK:n60#B2888B:" <60%" \
    STACK:n70#CC91BA:" <70%" \
    STACK:n80#6A2990:" <80%" \
    STACK:n90#0571B0:" <90%" \
    STACK:n100#000000:" <100%" >/dev/null

$RRDTOOL graph loif.png $GENERAL_OPTS $SIZE -s $START \
    -t "Loopback Interface" \
    DEF:in=$SYMON_RRD_ROOT/if_lo0.rrd:ibytes:AVERAGE \
    DEF:out=$SYMON_RRD_ROOT/if_lo0.rrd:obytes:AVERAGE \
    DEF:inp=$SYMON_RRD_ROOT/if_lo0.rrd:ipackets:AVERAGE \
    DEF:outp=$SYMON_RRD_ROOT/if_lo0.rrd:opackets:AVERAGE \
    DEF:coll=$SYMON_RRD_ROOT/if_lo0.rrd:collisions:AVERAGE \
    CDEF:nodata=in,UN,0,* \
    CDEF:inb=in,8,* \
    CDEF:outb=out,8,* \
    CDEF:noutb=outb,-1,* \
    CDEF:pmax=inb,100,/,102,* \
    CDEF:nmax=noutb,100,/,102,* \
    CDEF:totp=inp,outp,+ \
    CDEF:per=coll,totp,/,100,* \
    CDEF:p0=per,0,EQ,INF,0,IF \
    CDEF:p10=per,10,LE,INF,0,IF,per,1,GT,INF,0,IF,MIN \
    CDEF:p20=per,20,LE,INF,0,IF,per,10,GT,INF,0,IF,MIN \
    CDEF:p30=per,30,LE,INF,0,IF,per,20,GT,INF,0,IF,MIN \
    CDEF:p40=per,40,LE,INF,0,IF,per,30,GT,INF,0,IF,MIN \
    CDEF:p50=per,50,LE,INF,0,IF,per,40,GT,INF,0,IF,MIN \
    CDEF:p60=per,60,LE,INF,0,IF,per,50,GT,INF,0,IF,MIN \
    CDEF:p70=per,70,LE,INF,0,IF,per,60,GT,INF,0,IF,MIN \
    CDEF:p80=per,80,LE,INF,0,IF,per,70,GT,INF,0,IF,MIN \
    CDEF:p90=per,80,LE,INF,0,IF,per,80,GT,INF,0,IF,MIN \
    CDEF:p100=per,100,LE,INF,0,IF,per,90,GT,INF,0,IF,MIN \
    CDEF:n0=p0,-1,* \
    CDEF:n10=p10,-1,* \
    CDEF:n20=p20,-1,* \
    CDEF:n30=p30,-1,* \
    CDEF:n40=p40,-1,* \
    CDEF:n50=p50,-1,* \
    CDEF:n60=p60,-1,* \
    CDEF:n70=p70,-1,* \
    CDEF:n80=p80,-1,* \
    CDEF:n90=p90,-1,* \
    CDEF:n100=p100,-1,* \
    LINE1:pmax \
    LINE1:nmax \
    LINE1:nodata#FF0000 \
    AREA:inb#008194:in \
    STACK:p0#FAFFFA \
    STACK:p10#FFFFE6 \
    STACK:p20#FFD900 \
    STACK:p30#FD6724 \
    STACK:p40#E61800 \
    STACK:p50#AB2934 \
    STACK:p60#B2888B \
    STACK:p70#CC91BA \
    STACK:p80#6A2990 \
    STACK:p90#0571B0 \
    STACK:p100#000000 \
    AREA:noutb#da5400:out \
    STACK:n0#FFFFFF:" = 0%" \
    STACK:n10#F0E0E0:" <10%" \
    STACK:n20#FFD900:" <20%" \
    STACK:n30#FD6724:" <30%" \
    STACK:n40#E61800:" <40%" \
    STACK:n50#AB2934:" <50%" \
    STACK:n60#B2888B:" <60%" \
    STACK:n70#CC91BA:" <70%" \
    STACK:n80#6A2990:" <80%" \
    STACK:n90#0571B0:" <90%" \
    STACK:n100#000000:" <100%" >/dev/null

# SSL Proxy
$RRDTOOL graph sslproxy.png $GENERAL_OPTS $SIZE -s $START \
    -t "Connections" \
    DEF:all=$COLLECTD_RRD_ROOT/tail-sslproxy/derive-all.rrd:value:AVERAGE \
    DEF:idle=$COLLECTD_RRD_ROOT/tail-sslproxy/derive-idle.rrd:value:AVERAGE \
    DEF:expired=$COLLECTD_RRD_ROOT/tail-sslproxy/derive-expired.rrd:value:AVERAGE \
    AREA:all#0039b1:all \
    STACK:idle#bf8700:idle \
    STACK:expired#00b91e:expired >/dev/null

# DNS Server
$RRDTOOL graph dns.png $GENERAL_OPTS $SIZE -s $START \
    -t "Queries" \
    DEF:all=$COLLECTD_RRD_ROOT/tail-named/derive-all.rrd:value:AVERAGE \
    DEF:failed=$COLLECTD_RRD_ROOT/tail-named/derive-failed.rrd:value:AVERAGE \
    AREA:all#008194:all \
    STACK:failed#FF0000:failed >/dev/null

# FTP Proxy
$RRDTOOL graph ftp-proxy.png $GENERAL_OPTS $SIZE -s $START \
    -t "Connections" \
    DEF:all=$COLLECTD_RRD_ROOT/tail-ftp-proxy/derive-all.rrd:value:AVERAGE \
    AREA:all#da5400:all >/dev/null

# Web Filter
$RRDTOOL graph e2guardian.png $GENERAL_OPTS $SIZE -s $START \
    -t "Requests" \
    DEF:all=$COLLECTD_RRD_ROOT/tail-e2guardian/derive-all.rrd:value:AVERAGE \
    DEF:scanned=$COLLECTD_RRD_ROOT/tail-e2guardian/derive-scanned.rrd:value:AVERAGE \
    DEF:denied=$COLLECTD_RRD_ROOT/tail-e2guardian/derive-denied.rrd:value:AVERAGE \
    DEF:bypassed=$COLLECTD_RRD_ROOT/tail-e2guardian/derive-bypassed.rrd:value:AVERAGE \
    DEF:exception=$COLLECTD_RRD_ROOT/tail-e2guardian/derive-exception.rrd:value:AVERAGE \
    LINE1:all#0039b1:all \
    AREA:scanned#00b91e:scan \
    AREA:denied#bf4040:denied \
    STACK:bypassed#bf8700:bypass \
    AREA:exception#b200bf:except >/dev/null

# Virus Filter
$RRDTOOL graph clamd.png $GENERAL_OPTS $SIZE -s $START \
    -t "Scan Results" \
    DEF:all=$COLLECTD_RRD_ROOT/tail-clamd/derive-all.rrd:value:AVERAGE \
    DEF:ok=$COLLECTD_RRD_ROOT/tail-clamd/derive-ok.rrd:value:AVERAGE \
    DEF:found=$COLLECTD_RRD_ROOT/tail-clamd/derive-found.rrd:value:AVERAGE \
    AREA:all#0039b1:all \
    STACK:ok#bf8700:ok \
    STACK:found#00b91e:found >/dev/null

# SPAM Filter
$RRDTOOL graph spamassassin.png $GENERAL_OPTS $SIZE -s $START \
    -t "Scan Results" \
    DEF:spam=$COLLECTD_RRD_ROOT/tail-spamassassin/derive-spam.rrd:value:AVERAGE \
    DEF:ham=$COLLECTD_RRD_ROOT/tail-spamassassin/derive-ham.rrd:value:AVERAGE \
    LINE1:spam#0039b1:spam \
    AREA:ham#bf8700:ham >/dev/null

# IDS
$RRDTOOL graph snort.png $GENERAL_OPTS $SIZE -s $START \
    -t "Alerts" \
    DEF:alert=$COLLECTD_RRD_ROOT/tail-snort/derive-alert.rrd:value:AVERAGE \
    AREA:alert#da5400:alert >/dev/null

# POP3 Proxy
$RRDTOOL graph p3scan.png $GENERAL_OPTS $SIZE -s $START \
    -t "Requests" \
    DEF:requests=$COLLECTD_RRD_ROOT/tail-p3scan/derive-requests.rrd:value:AVERAGE \
    DEF:infected=$COLLECTD_RRD_ROOT/tail-p3scan/derive-infected.rrd:value:AVERAGE \
    AREA:requests#008194:requests \
    LINE1:infected#da5400:infected >/dev/null

# SMTP Proxy
$RRDTOOL graph smtp-gated.png $GENERAL_OPTS $SIZE -s $START \
    -t "Requests" \
    DEF:requests=$COLLECTD_RRD_ROOT/tail-smtp-gated/derive-requests.rrd:value:AVERAGE \
    DEF:rejected=$COLLECTD_RRD_ROOT/tail-smtp-gated/derive-rejected.rrd:value:AVERAGE \
    AREA:requests#008194:requests \
    LINE1:rejected#da5400:rejected >/dev/null

# Web User Interface
$RRDTOOL graph httpd_cpu.png $GENERAL_OPTS $SIZE -s $START \
    -t "CPU Load" \
    DEF:uticks=$SYMON_RRD_ROOT/proc_httpd.rrd:uticks:AVERAGE \
    DEF:sticks=$SYMON_RRD_ROOT/proc_httpd.rrd:sticks:AVERAGE \
    DEF:iticks=$SYMON_RRD_ROOT/proc_httpd.rrd:iticks:AVERAGE \
    AREA:uticks#008194:uticks \
    STACK:sticks#da5400:sticks \
    STACK:iticks#9932CC:iticks >/dev/null

exit 0
