#!/bin/sh
# Copyright (C) 2004-2018 Soner Tari
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

#/** \file
# Generates gettext po file for the given locale.
#*/

CHECK_ARG_COUNT()
{
	# Check this function's arg count first
	if [ $# -lt 3 ]; then
		echo "$0: Not enough arguments [3]: $#"
		exit 1
	fi

	if [ $3 -lt $2 ]; then
		echo "$1: Not enough arguments [$2]: $3"
		exit 1
	fi
	return 0
}

CHECK_ARG_COUNT $0 3 $#

FOLDER=$1
LOCALE=$2
OUTPUT_FILE=$3

KEYWORD="-k --keyword=$4"
if [ "$4" = ALL -o ! "$4" ]; then
	# TODO: 'ALL' option is never used, remove.
	KEYWORD='--keyword=_CONTROL --keyword=_MENU --keyword=_NOTICE --keyword=_TITLE --keyword=_STATS --keyword=_HELPBOX --keyword=_HELPWINDOW --keyword=_'
	echo "KEYWORD= $KEYWORD"
fi

echo "Generating php files list"
find $FOLDER -name "*.php" > files.txt
find $FOLDER -name "*.inc" >> files.txt

LOCALE_DIR="./View/locale/$LOCALE/LC_MESSAGES"
LOCALE_FILE="$LOCALE_DIR/$OUTPUT_FILE"

if [ ! -d $LOCALE_DIR ]; then
	echo -n "No such directory: $LOCALE_DIR, creating... "
	if ! mkdir -p $LOCALE_DIR; then
		echo "FAILED."
		exit 1
	fi
	echo "Successful."
fi

if [ ! -e $LOCALE_FILE ]; then
	echo -n "No such file: $LOCALE_FILE, creating... "
	if ! touch $LOCALE_FILE; then
		echo "FAILED."
		exit 1
	fi
	echo "Successful."
fi

echo "Generating gettext po file for $LOCALE"
if ! xgettext -L "PHP" -s \
		$KEYWORD \
		--no-location \
		--omit-header \
		--foreign-user \
		--copyright-holder="Soner Tari, The UTMFW project" \
		--msgid-bugs-address="sonertari@gmail.com" \
		--package-name="UTMFW" \
		--package-version="6.3" \
		-j -o $LOCALE_FILE \
		-f files.txt; then
	echo "FAILED generating $LOCALE_FILE"
	exit 1
fi

echo "Successfully generated $LOCALE_FILE"
exit 0
