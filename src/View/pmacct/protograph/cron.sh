#!/bin/sh
HOME=/var/www/htdocs/utmfw/View/pmacct/protograph/
RRDFOLDER=/var/log/utmfw/pmacct/protograph/
HOST=utmfw
(echo -n "/usr/local/bin/rrdtool update ${RRDFOLDER}/${HOST}.rrd N:" ; for i in `${HOME}/commands.sh`; do echo -n ${i}:; done; echo asd) | cut -d ':' -f 1-`wc -l ${HOME}/commands.sh | awk '{print $1+1}'` | ksh; ${HOME}/gengraph.sh
