# UTMFW

UTMFW is a UTM firewall running on OpenBSD. UTMFW is expected to be used on production systems. The UTMFW project provides a Web User Interface (WUI) for monitoring and configuration. You can also use [A4PFFW](https://github.com/sonertari/A4PFFW) and [W4PFFW](https://github.com/sonertari/W4PFFW) for monitoring.

You can find a couple of screenshots on the [wiki](https://github.com/sonertari/UTMFW/wiki).

The installation iso file for the amd64 arch is available for download at [utmfw68\_20201217\_amd64.iso](https://drive.google.com/file/d/1Jk5TVTIKaZXVtGadhZ9T_rtZxBA4YuRt/view?usp=sharing). Make sure the SHA256 checksum is correct: f9c5428cd395f77f8093b08b6107e15cd0b8705e37c25d4de6972263ffdab9fd.

UTMFW is an updated version of ComixWall. However, there are a few major changes, such as SSLproxy, Snort Inline IPS, PFRE, E2Guardian, many fixes and improvements to the system and the WUI, Firebase push notifications, and network user authentication. Also note that UTMFW 6.8 comes with OpenBSD 6.8-stable including all updates until December 12th, 2020.

UTMFW supports deep SSL inspection of HTTP, POP3, and SMTP protocols. SSL/TLS encrypted traffic is decrypted by [SSLproxy](https://github.com/sonertari/SSLproxy) and fed into the UTM services: Web Filter, POP3 Proxy, SMTP Proxy, and Inline IPS (and indirectly into Virus Scanner and Spam Filter through those UTM software). These UTM software have been modified to support the mode of operation required by SSLproxy.

## Features

UTMFW includes the following software, alongside what is already available on a basic OpenBSD installation:

- SSLproxy: Transparent SSL/TLS proxy for deep SSL inspection
- PFRE: Packet Filter Rule Editor
- E2Guardian: Web filter, anti-virus using ClamAV, blacklists
- Snort: Intrusion detection and inline prevention system, with the latest rules
- SnortIPS: Passive intrusion prevention software
- ClamAV: Virus scanner with periodic virus signature updates
- SpamAssassin: Spam scanner
- P3scan: Anti-virus/anti-spam transparent POP3 proxy
- Smtp-gated: Anti-virus/anti-spam transparent SMTP proxy
- Dante: SOCKS proxy
- IMSpector: IM proxy which supports IRC and others.
- OpenVPN: Virtual private networking
- Symon system monitoring software
- Pmacct: Network monitoring via graphs
- ISC DNS server
- PHP

![Console](https://github.com/sonertari/UTMFW/blob/master/screenshots/Console.png)

The web user interface of UTMFW helps you manage your firewall:

- Dashboard provides an overview of system status. If enabled, Notifier sends the system status as Firebase push notifications to the Android application, [A4PFFW](https://github.com/sonertari/A4PFFW).
- System, network, and service configuration can be achieved on the web interface.
- Pf rules are maintained using PFRE.
- Information on hosts, interfaces, pf rules, states, and queues are provided in a tabular form.
- System, pf, network, and internal clients can be monitored via graphs.
- Logs can be viewed and downloaded on the web interface. Compressed log files are supported.
- Statistics collected over logs are displayed in bar charts and top lists. Bar charts and top lists are clickable, so you don't need to touch your keyboard to search anything on the statistics pages. You can view the top lists on pie charts too. Statistics over compressed log files are supported.
- The web interface provides many help boxes and windows, which can be disabled.
- Man pages of OpenBSD and installed software can be accessed and searched on the web interface.
- There are two users who can log in to the web interface. Unprivileged user does not have access rights to configuration pages, thus cannot interfere with system settings, and cannot even change user password (i.e. you can safely give the unprivileged user's password to your boss).
- The web interface supports languages other than English: Turkish, Chinese, Dutch, Russian, French, Spanish.
- The web interface configuration pages are designed such that changes you may have made to the configuration files on the command line (such as comments you might have added) remain intact after you configure a module using the web interface.

![Dashboard](https://github.com/sonertari/UTMFW/blob/master/screenshots/Dashboard.png)

## How to install

Download the installation iso file mentioned above and follow the instructions in the installation guide available in the iso file. Below are the same instructions.

A few notes about UTMFW installation:

- Thanks to a modified auto-partitioner of OpenBSD, the disk can be partitioned with a recommended layout for UTMFW, so most users don't need to use the label editor at all.
- All install sets including siteXY.tgz are selected by default, so you cannot 'not' install UTMFW by mistake.
- OpenBSD installation questions are modified according to the needs of UTMFW. For example, X11 related questions are never asked.
- Make sure you have at least 2GB RAM. And an 8GB HD should be enough.

UTMFW installation is very intuitive and easy, just follow the instructions on the screen and answer the questions asked. You are advised to accept the default answers to all the questions. In fact, the installation can be completed by accepting default answers all the way from the first question until the last. The only obvious exceptions are network configuration and password setup.

Auto allocator will provide a partition layout recommended for your disk. Suggested partitioning should be suitable for most installations, simply accept it.

Make sure you configure two network interfaces. You will be asked to choose internal and external interfaces later on.

All of the install sets and software packages are selected by default, simply accept the selections.

If the installation script finds an already existing file which needs to be updated, it saves the old file as filename.orig.

Installation logs can be found under the /root directory.

You can access the web administration interface using the IP address of the system's internal interface you have selected during installation. You can log in to the system over ssh from internal network.

Web interface user names are admin and user. Network user is utmfw. All are set to the same password you provide during installation.

References:

1. INSTALL.amd64 in the installation iso file.
2. [Supported hardware](https://www.openbsd.org/amd64.html).
3. [OpenBSD installation guide](https://www.openbsd.org/faq/faq4.html).

## How to build

The purpose in this section is to build the installation iso file using the createiso script at the root of the project source tree. You are expected to be doing these on an OpenBSD 6.8 and have installed git, gettext, and doxygen on it.

### Build summary

The createiso script:

- Clones the git repo of the project to a tmp folder.
- Generates gettext translations and doxygen documentation.
- Prepares the webif and config packages and the site install set.
- And finally creates the iso file.

However, the source tree has links to OpenBSD install sets and packages, which should be broken, hence need to be fixed when you first obtain the sources. Make sure you see those broken links now. So, before you can run createiso, you need to do a couple of things:

- Install sets:
	+ Obtain the sources of OpenBSD.
	+ Patch the OpenBSD sources using the `patch-*` files under `openbsd/utmfw`.
	+ Create the UTMFW secret and public key pair to sign and verify the SHA256 checksums of the install sets, and copy them to their appropriate locations.
	+ Build an OpenBSD release, as described in [release(8)](https://man.openbsd.org/release) or [faq5](https://www.openbsd.org/faq/faq5.html).
	+ Copy the required install sets to the appropriate locations to fix the broken links in the sources.
- Packages:
	+ Download the required packages available on the OpenBSD mirrors.
	+ Create the packages which are not available on the OpenBSD mirrors and/or have been modified for UTMFW: sslproxy, e2guardian, p3scan, smtp-gated, snort, imspector, snortips, and libevent 2.1.11 (see `ports` and `ports/distfiles`).
	+ Copy them to the appropriate locations to fix the broken links in the sources.

Note that you can strip down xbase and xfont install sets to reduce the size of the iso file. Copy or link them to the appropriate locations under `openbsd/utmfw`.

Now you can run the createiso script which should produce an iso file in the same folder as itself.

### Build steps

The following are steps you can follow to build UTMFW yourself. Some of these steps can be automated by a script. You can modify these steps to suit your needs.

- Install OpenBSD:
	+ Download installXY.iso from an OpenBSD mirror
	+ Create a new VM with 60GB disk, choose a size based on your needs
	+ Add a separate 8GB disk for /dest, which will be needed to make release(8)
	+ Start VM and install OpenBSD
	+ Create a local user
	+ During installation mount the dest disk to /dest
	+ Add noperm to /dest in /etc/fstab
    + Make /dest owned by build:wodj and set its perms to 700
    + Create /dest/dest/ and /dest/rel/ folders

- Fetch UTMFW sources and update if upgrading:
	+ Install git
	+ Clone UTMFW to your home folder

	+ Bump version number X.Y in the sources, if upgrading
		+ cd/amd64/etc/boot.conf
		+ meta/createiso
		+ meta/install.sub
		+ src/create_po.sh
		+ Doxyfile
		+ README.md
		+ src/lib/defs.php

	+ Bump version number XY in the sources, if upgrading
		+ README.md

	+ Update based on release date, project changes, and news, if upgrading
		+ config/etc/motd
		+ meta/root.mail
		+ README.md

	+ Update copyright if necessary

- Generate signify key pair:
    + Save .pub and .sec to docs/signify
    + Copy .pub to meta/etc/signify/
    + Copy .pub to /etc/signify/

- Update packages:
	+ Install OpenBSD packages
		+ Set the download mirror, use the existing cache if any
            ```
            PKG_PATH=/var/db/pkg_cache/:https://cdn.openbsd.org/pub/OpenBSD/X.Y/packages/amd64/
            ```
		+ Save the depends under PKG_CACHE, which will be used later on to update the packages in the iso file
            ```
            export PKG_CACHE=/var/db/pkg_utmfw/
            ```
		+ isc-bind
		+ clamav
		+ p5-Mail-SpamAssassin
		+ snort
		+ openvpn
		+ dante
		+ symon
		+ symux
		+ pmacct
		+ pftop
		+ php

	+ Build and create UTMFW packages:
		+ Extract ports.tar.gz under /usr/
		+ Copy port folders of the UTMFW packages under ports to /usr/ports/{net,security}
		+ Copy the source tar balls of the UTMFW packages to /user/ports/distfiles
		+ Append daemon users of UTMFW packages to /usr/ports/infrastructure/db/user.list
            ```
            900 _p3scan             _p3scan         net/p3scan
            901 _smtp-gated         _smtp-gated     net/smtp-gated
            903 _imspector          _imspector      net/imspector
            904 _sslproxy           _sslproxy       security/sslproxy
            ```
		+ Install pkg depends of each UTMFW package before making them, so port does not try to build and install itself
		+ Obtain the snort sources, apply the snort diff under ports/distfiles, compress as tarball with the same name as the original tarball of the sources  
		+ Make the UTMFW packages
		    + libevent, if different from OpenBSD packages
			+ sslproxy
			+ p3scan
			+ smtp-gated: use the source tarball under ports/distfiles
			+ imspector: use the source tarball under ports/distfiles
			+ e2guardian
			+ snortips
			+ snort: use the source tarball generated above
		+ Sign all of the UTMFW packages using signify, for example:
            ```
            signify -Sz -s utmfw-XY.sec -m /usr/ports/packages/amd64/all/sslproxy-0.8.2.tgz -x ~/sslproxy-0.8.2.tgz
            ```
	+ Update the links under cd/amd64/X.Y/packages/ with the UTMFW packages made above
	+ Keep the links for blacklists.tar.gz, clamavdb.tar.gz, e2guardian, imspector, p3scan, smtp-gated, snortips, sslproxy, snort, libevent

	+ Install UTMFW packages using their signed packages
		+ Save the depends under PKG_CACHE
            ```
            export PKG_CACHE=/var/db/pkg_utmfw/
            ```
		+ libevent, if different from OpenBSD packages
		+ sslproxy
		+ p3scan
		+ smtp-gated
		+ e2guardian
		+ snortips
		+ imspector
		+ snort

	+ Update the links under cd/amd64/X.Y/packages/ with the OpenBSD packages saved under PKG_CACHE

- Update meta/install.sub:
    + Update the versions of the packages listed in THESETS

- Make release(8):
    + Extract src.tar.gz and and sys.tar.gz under /usr/src/
    + Apply the patches under openbsd/utmfw
	+ Follow the instructions in release(8), this step takes about 6 hours on a relatively fast computer
		+ Use export DESTDIR=/dest/dest/ RELEASEDIR=/dest/rel/
		+ Build kernel and reboot
		+ Build system
		+ Make release
    + Copy install sets under /dest/rel/ to ~/OpenBSD/X.Y/amd64/

- Update install sets:
	+ Update the links for install sets under cd/amd64/X.Y/amd64 using the install sets under ~/OpenBSD/X.Y/amd64/ made above
	+ Remove the old links
	+ Copy the xbaseXY.tgz install set from installXY.iso to docs/expat/amd64/xbaseXY.tgz
	+ Copy the xfontXY.tgz install set from installXY.iso to docs/fonts/amd64/xfontXY.tgz

- Update configuration files under config to the new versions of packages:
    + Also update Doxyfile if doxygen version changed

- Update PFRE:
    + Update PFRE to current version, support changes in pf if any
    + Create man2web package and install
    + Produce pf.conf.html from pf.conf(2) using man2web
    + Merge PFRE changes from the previous pf.conf.html, most importantly the anchors

- Update phpseclib to its new version if any:
    + Merge UTMFW changes from the previous version

- Update d3js to its new version if any:
    + Fix any issues caused by API changes if any

- Update the registered snortrules.tar.gz:
	+ Make sure the directory structure is the same as the one in the old snortrules.tar.gz
	+ Add black and white list files
	+ Compress

- Update blacklists.tar.gz:
	+ Download the black list
	+ Run the cats.php script to prepend category descriptions to each file
	+ Compress

- Update clamavdb.tar.gz:
	+ Download virus db files
	+ Compress

- Strip xbase and xfont:
	+ Make sure the contents are the same as in the one in the old iso file, except for the version numbers
	+ SECURITY: Be very careful about the permissions of the directories and files in these install sets, they should be the same as the original files

- Run the createiso script:
	+ Install gettext-tools and doxygen for translations and documentation
	+ Run ./createiso under ~/utmfw/
