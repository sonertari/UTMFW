<?php
/*
 * Copyright (c) 2003-2005 Willem Dijkstra
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    - Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    - Redistributions in binary form must reproduce the above
 *      copyright notice, this list of conditions and the following
 *      disclaimer in the documentation and/or other materials provided
 *      with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT HOLDERS OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 */

/*
 * Default rrdtool graph definitions
 */

function get_template(&$vars) {
    if ($vars->defp('rrdfile0')) {
	return get_combined_template($vars);
    } else {
	if (!$vars->defp('rrdtype')) {
	    if (isset($symon['graph_debug'])) {
		warning('graph: cannot load graph template; rrdtype not set');
	    }
	} else {
	    return get_single_template($vars->get('rrdtype'));
	}
    }
}

function get_single_template($graph_type) {
    global $symon;

    switch (strtolower($graph_type)) {
    case "cpu0":
    case "cpu1":
    case "cpu2":
    case "cpu3":
	return '
-u 100
--rigid
-v % cpu
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:A=%rrdfile%:user:AVERAGE
DEF:B=%rrdfile%:nice:AVERAGE
DEF:C=%rrdfile%:system:AVERAGE
DEF:D=%rrdfile%:interrupt:AVERAGE
DEF:E=%rrdfile%:idle:AVERAGE
CDEF:nodata=A,UN,0,*
COMMENT:                 min       avg       max       last\n
LINE1:nodata#FF0000
AREA:A#00FF00:user     
GPRINT:A:MIN: %6.2lf %%
GPRINT:A:AVERAGE:%6.2lf %%
GPRINT:A:MAX:%6.2lf %%
GPRINT:A:LAST:%6.2lf %%\n
STACK:B#00FFFF:nice     
GPRINT:B:MIN: %6.2lf %%
GPRINT:B:AVERAGE:%6.2lf %%
GPRINT:B:MAX:%6.2lf %%
GPRINT:B:LAST:%6.2lf %%\n
STACK:C#DDA0DD:system   
GPRINT:C:MIN: %6.2lf %%
GPRINT:C:AVERAGE:%6.2lf %%
GPRINT:C:MAX:%6.2lf %%
GPRINT:C:LAST:%6.2lf %%\n
STACK:D#9932CC:interrupt
GPRINT:D:MIN: %6.2lf %%
GPRINT:D:AVERAGE:%6.2lf %%
GPRINT:D:MAX:%6.2lf %%
GPRINT:D:LAST:%6.2lf %%\n
STACK:E#F5FFFA:idle     
GPRINT:E:MIN: %6.2lf %%
GPRINT:E:AVERAGE:%6.2lf %%
GPRINT:E:MAX:%6.2lf %%
GPRINT:E:LAST:%6.2lf %%\n
';
	break;

    case 'io1':
	return '
-v bytes/s
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:A=%rrdfile%:transfers:AVERAGE
DEF:B=%rrdfile%:seeks:AVERAGE
DEF:C=%rrdfile%:bytes:AVERAGE
CDEF:nodata=A,UN,0,*
COMMENT:               min          avg          max          last\n
LINE1:nodata#FF0000
AREA:C#00FF00:bytes    
GPRINT:C:MIN:%6.2lf %sBps
GPRINT:C:AVERAGE:%6.2lf %sBps
GPRINT:C:MAX:%6.2lf %sBps
GPRINT:C:LAST:%6.2lf %sBps\n
LINE1:B#0000FF:seeks    
GPRINT:B:MIN:%6.2lf     
GPRINT:B:AVERAGE:%6.2lf     
GPRINT:B:MAX:%6.2lf     
GPRINT:B:LAST:%6.2lf\n
LINE1:A#FFF000:transfers
GPRINT:A:MIN:%6.2lf     
GPRINT:A:AVERAGE:%6.2lf     
GPRINT:A:MAX:%6.2lf     
GPRINT:A:LAST:%6.2lf\n
';
	break;

    case 'io':
	return '
-v bytes/s
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:rx=%rrdfile%:rxfer:AVERAGE
DEF:wx=%rrdfile%:wxfer:AVERAGE
DEF:seeks=%rrdfile%:seeks:AVERAGE
DEF:rb=%rrdfile%:rbytes:AVERAGE
DEF:wb=%rrdfile%:wbytes:AVERAGE
CDEF:nwb=wb,-1,*
CDEF:nwx=wx,-1,*
CDEF:nodata=rx,UN,0,*
COMMENT:               min          avg          max          last\n
LINE1:nodata#FF0000

AREA:rb#00FF00:rbytes   
GPRINT:rb:MIN:%6.2lf %sBps
GPRINT:rb:AVERAGE:%6.2lf %sBps
GPRINT:rb:MAX:%6.2lf %sBps
GPRINT:rb:LAST:%6.2lf %sBps\n
LINE1:rx#9932CC:rxfer    
GPRINT:rx:MIN:%6.2lf     
GPRINT:rx:AVERAGE:%6.2lf     
GPRINT:rx:MAX:%6.2lf     
GPRINT:rx:LAST:%6.2lf\n
AREA:nwb#00FFFF:wbytes   
GPRINT:wb:MIN:%6.2lf %sBps
GPRINT:wb:AVERAGE:%6.2lf %sBps
GPRINT:wb:MAX:%6.2lf %sBps
GPRINT:wb:LAST:%6.2lf %sBps\n
LINE1:nwx#DDA0DD:wxfer    
GPRINT:wx:MIN:%6.2lf     
GPRINT:wx:AVERAGE:%6.2lf     
GPRINT:wx:MAX:%6.2lf     
GPRINT:wx:LAST:%6.2lf\n
LINE1:seeks#F5FFFA:seeks    
GPRINT:seeks:MIN:%6.2lf     
GPRINT:seeks:AVERAGE:%6.2lf     
GPRINT:seeks:MAX:%6.2lf     
GPRINT:seeks:LAST:%6.2lf\n
';
	break;

    case 'if':
	return '
-v bits/s
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:in=%rrdfile%:ibytes:AVERAGE
DEF:out=%rrdfile%:obytes:AVERAGE
DEF:inp=%rrdfile%:ipackets:AVERAGE
DEF:outp=%rrdfile%:opackets:AVERAGE
DEF:coll=%rrdfile%:collisions:AVERAGE
CDEF:nodata=in,UN,0,*
CDEF:inb=in,8,*
CDEF:outb=out,8,*
CDEF:noutb=outb,-1,*
CDEF:pmax=inb,100,/,102,*
CDEF:nmax=noutb,100,/,102,*
CDEF:totp=inp,outp,+
CDEF:per=coll,totp,/,100,*
CDEF:p0=per,0,EQ,INF,0,IF
CDEF:p10=per,10,LE,INF,0,IF,per,1,GT,INF,0,IF,MIN
CDEF:p20=per,20,LE,INF,0,IF,per,10,GT,INF,0,IF,MIN
CDEF:p30=per,30,LE,INF,0,IF,per,20,GT,INF,0,IF,MIN
CDEF:p40=per,40,LE,INF,0,IF,per,30,GT,INF,0,IF,MIN
CDEF:p50=per,50,LE,INF,0,IF,per,40,GT,INF,0,IF,MIN
CDEF:p60=per,60,LE,INF,0,IF,per,50,GT,INF,0,IF,MIN
CDEF:p70=per,70,LE,INF,0,IF,per,60,GT,INF,0,IF,MIN
CDEF:p80=per,80,LE,INF,0,IF,per,70,GT,INF,0,IF,MIN
CDEF:p90=per,80,LE,INF,0,IF,per,80,GT,INF,0,IF,MIN
CDEF:p100=per,100,LE,INF,0,IF,per,90,GT,INF,0,IF,MIN
CDEF:n0=p0,-1,*
CDEF:n10=p10,-1,*
CDEF:n20=p20,-1,*
CDEF:n30=p30,-1,*
CDEF:n40=p40,-1,*
CDEF:n50=p50,-1,*
CDEF:n60=p60,-1,*
CDEF:n70=p70,-1,*
CDEF:n80=p80,-1,*
CDEF:n90=p90,-1,*
CDEF:n100=p100,-1,*
LINE1:pmax
LINE1:nmax
COMMENT:              min           avg          max          last\n
LINE1:nodata#FF0000
AREA:inb#00FF00:in     
GPRINT:inb:MIN: %6.2lf %sbps
GPRINT:inb:AVERAGE:%6.2lf %sbps
GPRINT:inb:MAX:%6.2lf %sbps
GPRINT:inb:LAST:%6.2lf %sbps\n
STACK:p0#FAFFFA
STACK:p10#FFFFE6
STACK:p20#FFD900
STACK:p30#FD6724
STACK:p40#E61800
STACK:p50#AB2934
STACK:p60#B2888B
STACK:p70#CC91BA
STACK:p80#6A2990
STACK:p90#0571B0
STACK:p100#000000
AREA:noutb#00FFFF:out     
GPRINT:outb:MIN:%6.2lf %sbps
GPRINT:outb:AVERAGE:%6.2lf %sbps
GPRINT:outb:MAX:%6.2lf %sbps
GPRINT:outb:LAST:%6.2lf %sbps\n
COMMENT:packetloss
GPRINT:per:MIN:%6.2lf%%    
GPRINT:per:AVERAGE:%6.2lf%%    
GPRINT:per:MAX:%6.2lf%%    
GPRINT:per:LAST:%6.2lf%%\n
STACK:n0#FFFFFF: = 0%
STACK:n10#F0E0E0: <10%
STACK:n20#FFD900: <20%
STACK:n30#FD6724: <30%
STACK:n40#E61800: <40%
STACK:n50#AB2934: <50%\n
STACK:n60#B2888B: <60%
STACK:n70#CC91BA: <70%
STACK:n80#6A2990: <80%
STACK:n90#0571B0: <90%
STACK:n100#000000: <100%
';
	break;

    case 'mem':
	return '
-v bytes
-b 1024
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:A=%rrdfile%:real_active:AVERAGE
DEF:B=%rrdfile%:real_total:AVERAGE
DEF:C=%rrdfile%:free:AVERAGE
DEF:D=%rrdfile%:swap_used:AVERAGE
DEF:E=%rrdfile%:swap_total:AVERAGE
CDEF:nodata=A,UN,0,*
COMMENT:                  min        avg        max        last\n
LINE1:nodata#FF0000
AREA:B#008B8B:real      
GPRINT:B:MIN: %6.2lf %sb
GPRINT:B:AVERAGE:%6.2lf %sb
GPRINT:B:MAX:%6.2lf %sb
GPRINT:B:LAST:%6.2lf %sb\n
STACK:C#3CB371:free      
GPRINT:C:MIN: %6.2lf %sb
GPRINT:C:AVERAGE:%6.2lf %sb
GPRINT:C:MAX:%6.2lf %sb
GPRINT:C:LAST:%6.2lf %sb\n
LINE1:A#00FFFF:active    
GPRINT:A:MIN: %6.2lf %sb
GPRINT:A:AVERAGE:%6.2lf %sb
GPRINT:A:MAX:%6.2lf %sb
GPRINT:A:LAST:%6.2lf %sb\n
LINE1:D#888C00:swap used 
GPRINT:D:MIN: %6.2lf %sb
GPRINT:D:AVERAGE:%6.2lf %sb
GPRINT:D:MAX:%6.2lf %sb
GPRINT:D:LAST:%6.2lf %sb\n
LINE2:E#FF8C00:swap total
GPRINT:E:MIN: %6.2lf %sb
GPRINT:E:AVERAGE:%6.2lf %sb
GPRINT:E:MAX:%6.2lf %sb
GPRINT:E:LAST:%6.2lf %sb\n
';
	break;

    case 'pf':
	return '
-t %title%
-v bits/s
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:A=%rrdfile%:bytes_v4_in:AVERAGE
DEF:B=%rrdfile%:bytes_v4_out:AVERAGE
DEF:C=%rrdfile%:bytes_v6_in:AVERAGE
DEF:D=%rrdfile%:bytes_v6_out:AVERAGE
CDEF:inb=A,C,+,8,*
CDEF:outb=B,D,+,8,*
CDEF:nodata=A,UN,0,*
COMMENT:                min          avg          max          last\n
LINE1:nodata#FF0000
AREA:inb#00FF00:incoming
GPRINT:inb:MIN: %6.2lf %sbps
GPRINT:inb:AVERAGE:%6.2lf %sbps
GPRINT:inb:MAX:%6.2lf %sbps
GPRINT:inb:LAST:%6.2lf %sbps\n
LINE1:outb#00FFFF:outgoing 
GPRINT:outb:MIN:%6.2lf %sbps
GPRINT:outb:AVERAGE:%6.2lf %sbps
GPRINT:outb:MAX:%6.2lf %sbps
GPRINT:outb:LAST:%6.2lf %sbps\n
';
	break;

    case 'pfq':
	return '
-t %title%
-v bits/s
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:A=%rrdfile%:sent_bytes:AVERAGE
DEF:B=%rrdfile%:drop_bytes:AVERAGE
CDEF:sent=A,8,*
CDEF:dropped=B,8,*
CDEF:ndropped=dropped,-1,*
CDEF:nodata=A,UN,0,*
COMMENT:                min          avg          max          last\n
LINE1:nodata#FF0000
AREA:sent#00FF00:sent    
GPRINT:sent:MIN: %6.2lf %sbps
GPRINT:sent:AVERAGE:%6.2lf %sbps
GPRINT:sent:MAX:%6.2lf %sbps
GPRINT:sent:LAST:%6.2lf %sbps\n
AREA:ndropped#00FFFF:dropped  
GPRINT:dropped:MIN:%6.2lf %sbps
GPRINT:dropped:AVERAGE:%6.2lf %sbps
GPRINT:dropped:MAX:%6.2lf %sbps
GPRINT:dropped:LAST:%6.2lf %sbps\n
';
	break;

    case 'proc':
	return '
-t %title%
-v ticks/s
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:number=%rrdfile%:number:AVERAGE
DEF:uticks=%rrdfile%:uticks:AVERAGE
DEF:sticks=%rrdfile%:sticks:AVERAGE
DEF:iticks=%rrdfile%:iticks:AVERAGE
DEF:cpusec=%rrdfile%:cpusec:AVERAGE
DEF:procsz=%rrdfile%:procsz:AVERAGE
DEF:rsssz=%rrdfile%:rsssz:AVERAGE
CDEF:null=number,number,-
CDEF:iprocsz=0,procsz,-
CDEF:irsssz=0,rsssz,-
CDEF:nodata=number,UN,0,*
COMMENT:             min       avg       max       last\n
LINE1:nodata#FF0000
AREA:number#0000FF:number
GPRINT:number:MIN:%6.2lf  
GPRINT:number:AVERAGE:%6.2lf  
GPRINT:number:MAX:%6.2lf  
GPRINT:number:LAST:%6.2lf \n
AREA:uticks#00FF00:uticks
GPRINT:uticks:MIN:%6.2lf %s
GPRINT:uticks:AVERAGE:%6.2lf %s
GPRINT:uticks:MAX:%6.2lf %s
GPRINT:uticks:LAST:%6.2lf %s\n
STACK:sticks#DDA0DD:sticks
GPRINT:sticks:MIN:%6.2lf %s
GPRINT:sticks:AVERAGE:%6.2lf %s
GPRINT:sticks:MAX:%6.2lf %s
GPRINT:sticks:LAST:%6.2lf %s\n
STACK:iticks#9932CC:iticks
GPRINT:iticks:MIN:%6.2lf %s
GPRINT:iticks:AVERAGE:%6.2lf %s
GPRINT:iticks:MAX:%6.2lf %s
GPRINT:iticks:LAST:%6.2lf %s\n
COMMENT: cpusec
GPRINT:cpusec:MIN: %6.2lf %s
GPRINT:cpusec:AVERAGE:%6.2lf %s
GPRINT:cpusec:MAX:%6.2lf %s
GPRINT:cpusec:LAST:%6.2lf %s\n
COMMENT: procsz
GPRINT:procsz:MIN: %6.2lf %s
GPRINT:procsz:AVERAGE:%6.2lf %s
GPRINT:procsz:MAX:%6.2lf %s
GPRINT:procsz:LAST:%6.2lf %s\n
COMMENT: rsssz
GPRINT:rsssz:MIN:  %6.2lf %s
GPRINT:rsssz:AVERAGE:%6.2lf %s
GPRINT:rsssz:MAX:%6.2lf %s
GPRINT:rsssz:LAST:%6.2lf %s\n
';
    case 'mbuf':
	return '
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:totmbufs=%rrdfile%:totmbufs:AVERAGE
DEF:mt_data=%rrdfile%:mt_data:AVERAGE
DEF:mt_oobdata=%rrdfile%:mt_oobdata:AVERAGE
DEF:mt_control=%rrdfile%:mt_control:AVERAGE
DEF:mt_header=%rrdfile%:mt_header:AVERAGE
DEF:mt_ftable=%rrdfile%:mt_ftable:AVERAGE
DEF:mt_soname=%rrdfile%:mt_soname:AVERAGE
DEF:mt_soopts=%rrdfile%:mt_soopts:AVERAGE
DEF:pgused=%rrdfile%:pgused:AVERAGE
DEF:pgtotal=%rrdfile%:pgtotal:AVERAGE
DEF:totmem=%rrdfile%:totmem:AVERAGE
DEF:totpct=%rrdfile%:totpct:AVERAGE
DEF:m_drops=%rrdfile%:m_drops:AVERAGE
DEF:m_wait=%rrdfile%:m_wait:AVERAGE
DEF:m_drain=%rrdfile%:m_drain:AVERAGE
CDEF:nodata=totmbufs,UN,0,*
COMMENT:                   min       avg       max       last\n
LINE1:nodata#FF0000
AREA:mt_data#00FF00:mt_data   
GPRINT:mt_data:MIN:  %6.2lf %s
GPRINT:mt_data:AVERAGE:%6.2lf %s
GPRINT:mt_data:MAX:%6.2lf %s
GPRINT:mt_data:LAST:%6.2lf %s\n
STACK:mt_oobdata#888C00:mt_oobdata
GPRINT:mt_oobdata:MIN:  %6.2lf %s
GPRINT:mt_oobdata:AVERAGE:%6.2lf %s
GPRINT:mt_oobdata:MAX:%6.2lf %s
GPRINT:mt_oobdata:LAST:%6.2lf %s\n
STACK:mt_control#FF8C00:mt_control
GPRINT:mt_control:MIN:  %6.2lf %s
GPRINT:mt_control:AVERAGE:%6.2lf %s
GPRINT:mt_control:MAX:%6.2lf %s
GPRINT:mt_control:LAST:%6.2lf %s\n
STACK:mt_header#DDA0DD:mt_header 
GPRINT:mt_header:MIN:  %6.2lf %s
GPRINT:mt_header:AVERAGE:%6.2lf %s
GPRINT:mt_header:MAX:%6.2lf %s
GPRINT:mt_header:LAST:%6.2lf %s\n
STACK:mt_ftable#CC3299:mt_ftable 
GPRINT:mt_ftable:MIN:  %6.2lf %s
GPRINT:mt_ftable:AVERAGE:%6.2lf %s
GPRINT:mt_ftable:MAX:%6.2lf %s
GPRINT:mt_ftable:LAST:%6.2lf %s\n
STACK:mt_soname#9932CC:mt_soname 
GPRINT:mt_soname:MIN:  %6.2lf %s
GPRINT:mt_soname:AVERAGE:%6.2lf %s
GPRINT:mt_soname:MAX:%6.2lf %s
GPRINT:mt_soname:LAST:%6.2lf %s\n
STACK:mt_soopts#3299CC:mt_soopts 
GPRINT:mt_soopts:MIN:  %6.2lf %s
GPRINT:mt_soopts:AVERAGE:%6.2lf %s
GPRINT:mt_soopts:MAX:%6.2lf %s
GPRINT:mt_soopts:LAST:%6.2lf %s\n
LINE1:totmbufs#000000:totmbufs  
GPRINT:totmbufs:MIN:  %6.2lf %s
GPRINT:totmbufs:AVERAGE:%6.2lf %s
GPRINT:totmbufs:MAX:%6.2lf %s
GPRINT:totmbufs:LAST:%6.2lf %s\n
LINE1:m_drops#ff0000:m_drops   
GPRINT:m_drops:MIN:  %6.2lf %s
GPRINT:m_drops:AVERAGE:%6.2lf %s
GPRINT:m_drops:MAX:%6.2lf %s
GPRINT:m_drops:LAST:%6.2lf %s\n
LINE1:m_drain#00ff00:m_drain   
GPRINT:m_drain:MIN:  %6.2lf %s
GPRINT:m_drain:AVERAGE:%6.2lf %s
GPRINT:m_drain:MAX:%6.2lf %s
GPRINT:m_drain:LAST:%6.2lf %s\n
LINE1:m_wait#0000ff:m_wait    
GPRINT:m_wait:MIN:  %6.2lf %s
GPRINT:m_wait:AVERAGE:%6.2lf %s
GPRINT:m_wait:MAX:%6.2lf %s
GPRINT:m_wait:LAST:%6.2lf %s\n
';
	break;

//     case 'sensor':
// 	return '
// -t %title%
// -w %width%
// -h %heigth%
// -s %start%
// -e %end%
// DEF:value=%rrdfile%:value:AVERAGE
// CDEF:nodata=value,UN,0,*
// COMMENT:                 min       avg       max       last\n
// LINE1:nodata#FF0000
// LINE1:value#00FF00:value
// GPRINT:value:MIN:  %3.2lf %s
// GPRINT:value:AVERAGE:%3.2lf %s
// GPRINT:value:MAX:%3.2lf %s
// GPRINT:value:LAST:%3.2lf %s\n
// ';

    case 'sensor':
	return '
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:value=%rrdfile%:value:AVERAGE
CDEF:nodata=value,UN,0,*
COMMENT:                 min       avg       max       last\n
LINE1:nodata#FF0000
AREA:value#00FF00:value
GPRINT:value:MIN:  %3.2lf %s
GPRINT:value:AVERAGE:%3.2lf %s
GPRINT:value:MAX:%3.2lf %s
GPRINT:value:LAST:%3.2lf %s\n
';
	break;

    case 'df':
	$show = 'blocks';
	if (isset($symon['options']['df'])) {
	    if ($symon['options']['df'] != 'blocks') {
		$show = 'bytes';
	    }
	}

	if ($show == 'blocks') {
	return '
-v blocks
-b 1024
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:blocks=%rrdfile%:blocks:AVERAGE
DEF:free=%rrdfile%:bfree:AVERAGE
DEF:avail=%rrdfile%:bavail:AVERAGE
CDEF:nodata=blocks,UN,0,*
CDEF:Bavail=blocks,free,avail,-,-
CDEF:Bused=blocks,free,-
COMMENT:                  min        avg        max        last\n
LINE1:nodata#FF0000
LINE2:blocks#FF0000:total
GPRINT:blocks:MIN:        %4.1lf %s
GPRINT:blocks:AVERAGE: %4.1lf %s
GPRINT:blocks:MAX: %4.1lf %s
GPRINT:blocks:LAST: %4.1lf %s\n
LINE1:Bavail#55FF55:avail
GPRINT:Bavail:MIN:        %4.1lf %s
GPRINT:Bavail:AVERAGE: %4.1lf %s
GPRINT:Bavail:MAX: %4.1lf %s
GPRINT:Bavail:LAST: %4.1lf %s\n
AREA:Bused#D0D0FF:used
GPRINT:Bused:MIN:         %4.1lf %s
GPRINT:Bused:AVERAGE: %4.1lf %s
GPRINT:Bused:MAX: %4.1lf %s
GPRINT:Bused:LAST: %4.1lf %s\n
';
	} else {
	return '
-v bytes
-b 1024
-t %title%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:b=%rrdfile%:blocks:AVERAGE
DEF:f=%rrdfile%:bfree:AVERAGE
DEF:a=%rrdfile%:bavail:AVERAGE
CDEF:nodata=b,UN,0,*
CDEF:Btotal=b,512,*
CDEF:Bavail=b,f,a,-,-,512,*
CDEF:Bused=b,f,-,512,*
COMMENT:                  min        avg        max        last\n
LINE1:nodata#FF0000
LINE2:Btotal#FF0000:total
GPRINT:Btotal:MIN:        %4.1lf %s
GPRINT:Btotal:AVERAGE: %4.1lf %s
GPRINT:Btotal:MAX: %4.1lf %s
GPRINT:Btotal:LAST: %4.1lf %s\n
LINE1:Bavail#55FF55:avail
GPRINT:Bavail:MIN:        %4.1lf %s
GPRINT:Bavail:AVERAGE: %4.1lf %s
GPRINT:Bavail:MAX: %4.1lf %s
GPRINT:Bavail:LAST: %4.1lf %s\n
AREA:Bused#D0D0FF:used
GPRINT:Bused:MIN:         %4.1lf %s
GPRINT:Bused:AVERAGE: %4.1lf %s
GPRINT:Bused:MAX: %4.1lf %s
GPRINT:Bused:LAST: %4.1lf %s\n
';
	}

	break;

    default:
	return "";
	break;
    }
}
function get_combined_template($graph_type, $number) {
    global $symon;

    switch (strtolower($graph_type)) {
    case 'io':
	$size = count($symon['color']['io']);
	$color = $symon['color']['io'][0];
	$ncolor = $symon['color']['io'][$size - 1];
	$def= '
-v bytes/s
-t io of %name%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:rb0=%rrdfile0%:rbytes:AVERAGE
DEF:wb0=%rrdfile0%:wbytes:AVERAGE
CDEF:nwb0=wb0,-1,*
CDEF:nodata=rb0,UN,0,*
COMMENT:                   min          avg          max          last\n
LINE1:nodata#FF0000
';
	$top = 'AREA:rb0' . $color .':%rrdwhat0%%rrdwhich0% read    
GPRINT:rb0:MIN:%6.2lf %sBps
GPRINT:rb0:AVERAGE:%6.2lf %sBps
GPRINT:rb0:MAX:%6.2lf %sBps
GPRINT:rb0:LAST:%6.2lf %sBps\n
';
	$bottom = 'AREA:nwb0'. $ncolor .':%rrdwhat0%%rrdwhich0% write   
GPRINT:wb0:MIN:%6.2lf %sBps
GPRINT:wb0:AVERAGE:%6.2lf %sBps
GPRINT:wb0:MAX:%6.2lf %sBps
GPRINT:wb0:LAST:%6.2lf %sBps\n
';
	for ($item = 1; $item < $number; $item++) {
	    $color = $symon['color']['pfq'][$item % $size];
	    $ncolor = $symon['color']['pfq'][($size - $item) % $size];

	    $def .= 'DEF:rb'. $item. '=%rrdfile'. $item. '%:rbytes:AVERAGE
DEF:wb'. $item. '=%rrdfile'. $item. '%:wbytes:AVERAGE
CDEF:nwb'. $item. '=wb'. $item. ',-1,*
';
	    $top .= 'STACK:rb' . $item . $color .':%rrdwhat'. $item .'%%rrdwhich' . $item .'% read    
GPRINT:rb'. $item. ':MIN:%6.2lf %sBps
GPRINT:rb'. $item. ':AVERAGE:%6.2lf %sBps
GPRINT:rb'. $item. ':MAX:%6.2lf %sBps
GPRINT:rb'. $item. ':LAST:%6.2lf %sBps\n
';
	    $bottom .= 'STACK:nwb'. $item . $ncolor .':%rrdwhat'. $item. '%%rrdwhich'. $item. '% write   
GPRINT:wb'. $item. ':MIN:%6.2lf %sBps
GPRINT:wb'. $item. ':AVERAGE:%6.2lf %sBps
GPRINT:wb'. $item. ':MAX:%6.2lf %sBps
GPRINT:wb'. $item. ':LAST:%6.2lf %sBps\n
';
	}
	return $def . $top . $bottom;
	break;

    case "pfq":
	$size = count($symon['color']['pfq']);
	$color = $symon['color']['pfq'][0];
	$ncolor = $symon['color']['pfq'][$size - 1];
	$def = '
-v bytes/s
-t pfq of %name%
-w %width%
-h %heigth%
-s %start%
-e %end%
DEF:sb0=%rrdfile0%:sent_bytes:AVERAGE
DEF:db0=%rrdfile0%:drop_bytes:AVERAGE
CDEF:idb0=0,db0,-
CDEF:nodata=sb0,UN,0,*
COMMENT:                min          avg          max         last\n
';
	$top = 'AREA:sb0'. $color. ':%rrdwhat0%%rrdwhich0% sent
GPRINT:sb0:MIN:%6.2lf %sbps
GPRINT:sb0:AVERAGE:%6.2lf %sbps
GPRINT:sb0:MAX:%6.2lf %sbps
GPRINT:sb0:LAST:%6.2lf %sbps\n
';
	$bottom = 'AREA:idb0'. $ncolor. ':%rrdwhat0%%rrdwhich0% drop
GPRINT:db0:MIN:%6.2lf %sbps
GPRINT:db0:AVERAGE:%6.2lf %sbps
GPRINT:db0:MAX:%6.2lf %sbps
GPRINT:db0:LAST:%6.2lf %sbps\n
';

	for ($item = 1; $item < $number; $item++) {
	    $color = $symon['color']['pfq'][$item % $size];
	    $ncolor = $symon['color']['pfq'][($size - $item) % $size];

	    $def .= 'DEF:sb'. $item. '=%rrdfile'. $item. '%:sent_bytes:AVERAGE
DEF:db'. $item. '=%rrdfile'. $item. '%:drop_bytes:AVERAGE
CDEF:idb'. $item. '=0,db'. $item .',-
';

	    $top .= 'STACK:sb'. $item . $color .':%rrdwhat'. $item .'%%rrdwhich'. $item. '% sent
GPRINT:sb'. $item .':MIN:%6.2lf %sbps
GPRINT:sb'. $item .':AVERAGE:%6.2lf %sbps
GPRINT:sb'. $item .':MAX:%6.2lf %sbps
GPRINT:sb'. $item .':LAST:%6.2lf %sbps\n
';
	    $bottom .= 'STACK:idb'. $item . $ncolor .':%rrdwhat'. $item. '%%rrdwhich'. $item. '% drop
GPRINT:db'. $item .':MIN:%6.2lf %sbps
GPRINT:db'. $item .':AVERAGE:%6.2lf %sbps
GPRINT:db'. $item .':MAX:%6.2lf %sbps
GPRINT:db'. $item .':LAST:%6.2lf %sbps\n
';
	}
	return $def . $top . $bottom;
	break;
    }
}

?>
