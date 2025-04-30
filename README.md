# UTMFW

UTMFW is a UTM firewall running on OpenBSD. UTMFW is expected to be used on production systems. The UTMFW project provides a Web User Interface (WUI) for monitoring and configuration. You can also use the Android application [A4PFFW](https://github.com/sonertari/A4PFFW) and the Windows application [W4PFFW](https://github.com/sonertari/W4PFFW) for monitoring.

UTMFW is an updated version of ComixWall. However, there are a few major changes, such as [SSLproxy](https://github.com/sonertari/SSLproxy), Snort Inline IPS, [PFRE](https://github.com/sonertari/PFRE), E2Guardian, many fixes and improvements to the system and the WUI, and network user authentication. Also note that UTMFW 7.7 comes with OpenBSD 7.7-stable including all updates until May 7th, 2025.

UTMFW supports deep SSL inspection of HTTP, POP3, and SMTP protocols. SSL/TLS encrypted traffic is decrypted by [SSLproxy](https://github.com/sonertari/SSLproxy) and fed into the UTM services: Web Filter, POP3 Proxy, SMTP Proxy, and Inline IPS (and indirectly into Virus Scanner and Spam Filter through those UTM software). These UTM software have been modified to support the mode of operation required by SSLproxy.

![Dashboard](https://github.com/sonertari/UTMFW/blob/master/screenshots/Dashboard.png)

You can find a couple of screenshots on the [wiki](https://github.com/sonertari/UTMFW/wiki).

## Download

UTMFW runs on amd64 and arm64 architectures. So, the UTMFW project releases installation files in iso and img formats for those architectures.

Amd64:

- [utmfw77\_20250507\_amd64.iso](https://drive.google.com/file/d/11heEKehSGvoGacotinlYL8wIFsdrApDn/view?usp=sharing)
	+ SHA256 checksum: eeebfb075bb529b713f3363feb0a0c0d590372699dda4805cd2f995276d6a975
	+ Tested on VMware

- [utmfw77\_20250507\_amd64.img](https://drive.google.com/file/d/1dJ741u3Hl20sXE743Jtq5F1TIv_rNLU3/view?usp=sharing)
	+ SHA256 checksum: 0d86879d679876ba99d469befc8d60baf5a4e5f19bbaaf2d8cb3ef12b56f40db
	+ Tested on bare hardware

Arm64:

- [utmfw77\_20250507\_arm64.iso](https://drive.google.com/file/d/1Toij1KigptUZq96Mj2wQ420Yworpu8UD/view?usp=sharing)
	+ SHA256 checksum: e4116d42e956d50cb69042612f9f340811f091022ba6717c42ddc4219721b4dc
	+ Tested on UTM for macOS

- [utmfw77\_20250507\_arm64.img](https://drive.google.com/file/d/1JtA0PRz9mwC9CgUJgt_bTGw6G7BoiBNB/view?usp=sharing)
	+ SHA256 checksum: d564eee12d6584facbe9e35d7096c5ba634588138c943719ce04f71527a12d4a
	+ Tested on Raspberry Pi 4 Model B

Make sure the SHA256 checksums are correct.

## Features

UTMFW includes the following software, alongside what is already available on a basic OpenBSD installation:

- [SSLproxy](https://github.com/sonertari/SSLproxy): Transparent SSL/TLS proxy for deep SSL inspection
- [PFRE](https://github.com/sonertari/PFRE): Packet Filter Rule Editor
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
- Symon: System monitoring software
- Pmacct: Network monitoring via graphs
- Collectd: System metrics collection engine
- Dnsmasq: DNS forwarder
- PHP

![Console](https://github.com/sonertari/UTMFW/blob/master/screenshots/Console.png)

The web user interface of UTMFW helps you manage your firewall:

- Dashboard displays an overview of system status using graphs and statistics counters. You can click on those graphs and counters to go to their details on the web user interface.
- System, network, and service configuration can be achieved on the web user interface.
- Pf rules are maintained using [PFRE](https://github.com/sonertari/PFRE).
- Information on hosts, interfaces, pf rules, states, and queues are provided in tabular form.
- System, pf, network, and internal clients can be monitored via graphs.
- Logs can be viewed and downloaded on the web user interface. Compressed log files are supported.
- Statistics collected over logs are displayed in bar charts and top lists. Bar charts and top lists are clickable, so you don't need to touch your keyboard to search anything on the statistics pages. You can view the top lists on pie charts too. Statistics over compressed log files are supported.
- The web user interface provides many help boxes and windows, which can be disabled.
- Man pages of OpenBSD and installed software can be accessed and searched on the web user interface.
- There are two users who can log in to the web user interface. Unprivileged user does not have access rights to configuration pages, thus cannot interfere with system settings, and cannot even change user password (i.e. you can safely give the unprivileged user's password to your boss).
- The web user interface supports languages other than English: Turkish, Chinese, Dutch, Russian, French, Spanish.
- The web user interface configuration pages are designed such that changes you may have made to the configuration files on the command line (such as comments you might have added) remain intact after you configure a module using the web user interface.

UTMFW uses the same design decisions and implementation as the [PFRE](https://github.com/sonertari/PFRE) project. See its README for details.

![UI Design](https://github.com/sonertari/UTMFW/blob/master/screenshots/UIDesign.png)

## How to install

Download the installation iso or img file for your platform and follow the instructions in the installation guide available in the file. Below are the same instructions.

You can also find [the output of a sample installation on the wiki](https://github.com/sonertari/UTMFW/wiki/Sample-Installation).

### Installation Guide

UTMFW installation is very intuitive and easy, just follow the instructions on the screen and answer the questions asked. You are advised to accept the default answers to all the questions. In fact, the installation can be completed by accepting default answers all the way from the first question until the last. The only exceptions are network configuration, password setup, and installation disk selection.

Auto allocator will provide a partition layout recommended for your disk. Suggested partitioning should be suitable for most installations, simply accept it. Do not delete or modify the msdos partition (for arm64 installation).

Make sure you configure two network interfaces. You will be asked to choose internal and external interfaces later on. You can configure the internal wifi interface in Host AP mode.

All of the install sets and software packages are selected by default, simply accept the selections.

While installing using the img file, when the installation script asks the location for the install sets or the packages, you should choose the disk option and that the disk partition is not mounted yet, and then select the device name for the installation disk (usually sd0 or sd1, but type ? to see device info first). The default path for install sets and packages the script offers is the same path as in the img file too, so you just hit Enter at that point.

If the installation script finds an already existing file which needs to be updated, it saves the old file as filename.orig.

Installation logs can be found under the /root directory.

You can access the web administration interface using the IP address of the system's internal interface you have selected during installation. You can log in to the system over ssh from internal network.

Web interface user names are admin and user. Network user is utmfw. All are set to the same password you provide during installation.

References:

1. INSTALL.amd64 in the installation iso file and INSTALL.arm64 in the installation img file.
2. [Supported hardware for amd64](https://www.openbsd.org/amd64.html) and [supported hardware for arm64](https://www.openbsd.org/arm64.html).
3. [OpenBSD installation guide](https://www.openbsd.org/faq/faq4.html).

### Installation Tips

A few notes about UTMFW installation:

- Thanks to a modified auto-partitioner of OpenBSD, the disk can be partitioned with a recommended layout for UTMFW, so most users don't need to use the label editor at all.
- All install sets including siteXY.tgz are selected by default, so you cannot 'not' install UTMFW by mistake.
- OpenBSD installation questions are modified according to the needs of UTMFW. For example, X11 related questions are never asked.
- Make sure you have at least 2GB RAM, ideally 4GB if you enable MFS. And a 16GB HD should be enough.
- If you install on an SD card, make sure it is fast enough. If you install on a slow disk, but you have enough RAM, you can enable memory-based file system (MFS), which is the default.
- After installation:
	+ When you first try to log in to the WUI, ignore the certificate warning issued by your web browser and proceed to the WUI.
	+ Download the ca.crt from the SSLproxy Config page on the WUI, and install it on your web browser or other client application as a trusted CA certificate. You can install the ca.crt in the trust store on Android phones, but Android applications may not use that trust store. So you may need to use the PassSite option of SSLproxy for such applications.
	+ Enable the pf rule for FCM ports (see /etc/pf.conf or go to the PFRE Editor page on the WUI), if you want to receive Firebase push notifications sent by UTMFW to your Android phone on the local network and on which you have installed and are running [A4PFFW](https://github.com/sonertari/A4PFFW).
- Make sure the date and time of the system is correct during both installation and normal operation, and select the correct timezone during installation. Otherwise:
	+ The "Not Valid Before" date of the CA certificate generated for SSLproxy during installation may be wrong, causing clients to reject the certificates forged by SSLproxy, at least until the start date. To fix the "Not Valid Before" date, you may need to regenerate the CA certificate on the WUI, after fixing the system date and time.
	+ The certificates forged by SSLproxy will be rejected by client applications, hence the connections will fail.
	+ SSLproxy will not verify server certificates with date and time in the future or in the past, hence the connections will fail.
	+ After fixing the date and time of the system during normal operation, the system statistics and monitoring programs may stop updating the RRD files due to significant time difference since last update. So you may need to delete the statistics files and reinit the RRD files using the WUI, and restart either the statistics and monitoring programs or the system.

## How to build

The purpose in this section is to build the installation iso or img file using the createiso or createimg script, respectively, at the root of the project source tree. You are expected to be doing these on an OpenBSD 7.7 and have installed git, gettext, and doxygen on it.

### Build summary

The create script:

- Clones the git repo of the project to a tmp folder.
- Generates gettext translations and doxygen documentation.
- Prepares the webif and config packages and the site install set.
- And finally creates the iso or img file for the amd64 or the arm64 arch.

However, the source tree has links to OpenBSD install sets and packages, which should be broken, hence need to be fixed when you first obtain the sources. Make sure you see those broken links now. So, before you can run the create script, you need to do a couple of things:

- Install sets:
	+ Obtain the sources of OpenBSD.
	+ Patch the OpenBSD sources using the `patch-*` files under `openbsd/utmfw`.
	+ Create the UTMFW secret and public key pair to sign and verify the SHA256 checksums of the install sets, and copy them to their appropriate locations.
	+ Build an OpenBSD release, as described in [release(8)](https://man.openbsd.org/release) or [faq5](https://www.openbsd.org/faq/faq5.html).
	+ Copy the required install sets to the appropriate locations to fix the broken links in the sources.
- Packages:
	+ Download the required packages available on the OpenBSD mirrors.
	+ Create the packages which are not available on the OpenBSD mirrors and/or have been modified for UTMFW: sslproxy, e2guardian, p3scan, smtp-gated, snort, imspector, snortips, and collectd (see `ports` and `ports/distfiles`).
	+ Copy them to the appropriate locations to fix the broken links in the sources.

Note that you can strip down xbase and xfont install sets to reduce the size of the iso and img files. Copy or link them to the appropriate locations under `openbsd/utmfw`.

Now you can run the createiso or createimg script, which should produce an iso or img file, respectively, in the same folder as itself.

### Build steps

The following are steps you can follow to build UTMFW yourself. Some of these steps can be automated by a script. You can modify these steps to suit your needs.

- Install OpenBSD amd64:
	+ Download installXY.iso from an OpenBSD mirror
	+ Create a new VM with 60GB disk, or choose a size based on your needs
	+ Start the VM and install OpenBSD

- Install OpenBSD arm64:
	+ Download installXY.img or installXY.iso from an OpenBSD mirror
	+ Use a 32GB SD card, USB flash memory, or disk, or choose a size based on your needs
	+ Start the Raspberry Pi 4 or qemu-system-aarch64 or UTM for macOS, and install OpenBSD

- Configure OpenBSD:
	+ Create a local user, after reboot add it to /etc/doas.conf
	+ Create a separate partition mounted on /dest, which will be needed to make release(8)
	+ Add noperm to /dest in /etc/fstab
	+ Create the /dest/dest/ and /dest/rel/ folders
	+ Make /dest owned by build:wobj and set its perms to 700
		```
		doas chown -R build:wobj /dest
		doas chmod -R 700 /dest
		```

- Fetch the UTMFW sources and update if upgrading:
	+ Install git
	+ Clone UTMFW to your home folder

	+ Bump the version number X.Y in the sources, if upgrading
		+ cd/amd64/etc/boot.conf
		+ cd/arm64/etc/boot.conf
		+ meta/create
		+ meta/install.sub
		+ src/create_po.sh
		+ Doxyfile
		+ README.md
		+ src/lib/defs.php
		+ cd/amd64/X.Y/
		+ cd/arm64/X.Y/
		+ openbsd/X.Y/
		+ .po files under src/View/locale/

	+ Bump the version number XY in the sources, if upgrading
		+ README.md
		+ openbsd/utmfw/expat/amd64/xbaseXY.tgz
		+ openbsd/utmfw/expat/arm64/xbaseXY.tgz
		+ openbsd/utmfw/fonts/amd64/xfontXY.tgz
		+ openbsd/utmfw/fonts/arm64/xfontXY.tgz

	+ Update the version number, release date, project changes, and news, if upgrading
		+ config/etc/motd
		+ meta/root.mail
		+ README.md

	+ Update the copyright year if necessary

- Generate the signify key pair:
	+ `signify -G -p utmfw-XY.pub -s utmfw-XY.sec`
	+ Save utmfw-XY.pub and utmfw-XY.sec to docs/signify
	+ Copy utmfw-XY.pub to docs/signify/utmfw-XY-pkg.pub, note the new name with the pkg suffix, otherwise the pkg utility complains if we use the same file name for packages too
	+ Copy utmfw-XY.sec to docs/signify/utmfw-XY-pkg.sec
	+ Copy utmfw-XY.pub and utmfw-XY-pkg.pub to meta/etc/signify/
	+ Copy utmfw-XY.pub and utmfw-XY-pkg.pub to /etc/signify/, the utmfw-XY.pub file is copied into the bsd.rd file while making release(8), which is used to verify the install sets during installation, and the utmfw-XY-pkg.pub file is used to sign and verify the packages created below

- Update the packages for the amd64 arch, then do the same for the arm64 arch replacing amd64 with arm64 (or aarch64 for PKG_PATH) below:
	+ Install the OpenBSD packages
		+ Set the download mirror, use the existing cache if any
			```
			export PKG_PATH=/var/db/pkg_cache/:https://cdn.openbsd.org/pub/OpenBSD/X.Y/packages/amd64/
			```
		+ Save the depends under PKG_CACHE, which will be used later on to update the packages in the iso and img files
			```
			export PKG_CACHE=/var/db/pkg_utmfw/
			```
		+ dnsmasq
		+ clamav
		+ p5-Mail-SpamAssassin
		+ snort, to download its dependencies, otherwise we have our own patched version
		+ openvpn
		+ dante
		+ symon
		+ symux
		+ pmacct
		+ pftop
		+ php, php-cgi, php-curl, php-pcntl, php-sqlite3
		+ rsync

	+ Build and create the UTMFW packages
		+ Extract ports.tar.gz under /usr/ports/
		+ Copy the port folders of the UTMFW packages under ports to /usr/ports/{net,security,www,devel,sysutils}
		+ Obtain the snort sources, apply the snort diff under ports/distfiles, and compress as tarball with the same name as the original tarball of the sources
		+ Copy the source tarballs of the UTMFW packages to /usr/ports/distfiles
		+ Append the daemon users of UTMFW packages to /usr/ports/infrastructure/db/user.list, but note that bsd.port.mk does not like blank lines at the bottom of user.list
			```
			950 _p3scan             _p3scan         net/p3scan
			951 _smtp-gated         _smtp-gated     net/smtp-gated
			953 _imspector          _imspector      net/imspector
			954 _sslproxy           _sslproxy       security/sslproxy
			```
		+ Install the pkg dependencies of each UTMFW package before making them, so that the ports system does not try to build and install them itself
		+ Make the UTMFW packages
			+ libevent, if not using the OpenBSD package
			+ sslproxy
			+ p3scan
			+ smtp-gated: use the source tarball under ports/distfiles
			+ e2guardian
			+ imspector: use the source tarball under ports/distfiles
			+ snortips
			+ snort: use the source tarball generated above
			+ collectd
		+ Sign all of the UTMFW packages using signify, note that you should use the private key file utmfw-XY-pkg.sec for pkg, for example:
			```
			signify -Sz -s utmfw-XY-pkg.sec -m /usr/ports/packages/amd64/all/sslproxy-0.9.8.tgz -x ~/sslproxy-0.9.8.tgz
			```
	+ Update the links under cd/amd64/X.Y/packages/ with the UTMFW packages made above

	+ Install the UTMFW packages using their signed packages, to download their dependencies
		+ Save the depends under PKG_CACHE
			```
			export PKG_CACHE=/var/db/pkg_utmfw/
			```
		+ libevent, if not using the OpenBSD package
		+ sslproxy
		+ p3scan
		+ smtp-gated
		+ e2guardian
		+ imspector
		+ snortips
		+ snort
		+ collectd

	+ Update the links under cd/amd64/X.Y/packages/ with the OpenBSD packages saved under PKG_CACHE

	+ Keep the links for the following packages, or update their versions
		+ blacklists.tar.gz
		+ clamavdb.tar.gz
		+ snortrules.tar.gz
		+ imspector
		+ p3scan
		+ smtp-gated
		+ snortips
		+ sslproxy
		+ snort
		+ e2guardian
		+ libevent, if not using the OpenBSD package
		+ collectd

- Update meta/install.sub:
	+ Update the versions of the packages listed in THESETS

- Make release(8) for the amd64 arch, then do the same for the arm64 arch replacing amd64 with arm64 below:
	+ Extract src.tar.gz and and sys.tar.gz under /usr/src/
	+ Apply the unified patch-src or the individual patches under openbsd/utmfw
		```
		patch -p1 <patch-src
		```
	+ Update the sources with the stable branch changes if any
		```
		cvs -d anoncvs@anoncvs.spacehopper.org:/cvs -q up -Pd -rOPENBSD_X_Y
		```
	+ Download and copy [the Broadcom wifi drivers](https://github.com/pftf/RPi4/tree/master/firmware/brcm) for Raspberry Pi 4 to /etc/firmware/
	+ Follow the instructions in release(8), this step takes about 6 hours on a relatively fast amd64 computer and longer than 60 hours on a Raspberry Pi 4
		+ Build the kernel and reboot
		+ Build the base system
		+ Make the release, use the dest and rel folders created above:
			```
			export DESTDIR=/dest/dest/ RELEASEDIR=/dest/rel/
			```
	+ Copy the install sets under /dest/rel/ to ~/OpenBSD/X.Y/amd64/

- Update the install sets:
	+ Update the links for install sets under cd/amd64/X.Y/amd64 using the install sets under ~/OpenBSD/X.Y/amd64/ made above
	+ Update the links for install sets under cd/arm64/X.Y/arm64 using the install sets under ~/OpenBSD/X.Y/arm64/ made above
	+ Copy eficdboot from installXY.iso or cdXY.iso for the amd64 arch and create a link to it in cd/amd64/X.Y/amd64
	+ Copy cdbr from installXY.iso or cdXY.iso for the arm64 arch and create a link to it in cd/arm64/X.Y/arm64
	+ Remove the old links
	+ Copy the xbaseXY.tgz install set from installXY.iso to docs/expat/amd64/xbaseXY.tgz
	+ Copy the xbaseXY.tgz install set from installXY.img to docs/expat/arm64/xbaseXY.tgz
	+ Copy the xfontXY.tgz install set from installXY.iso to docs/fonts/amd64/xfontXY.tgz
	+ Copy the xfontXY.tgz install set from installXY.img to docs/fonts/arm64/xfontXY.tgz
	+ Copy BOOTIA32.EFI and BOOTX64.EFI for the amd64 arch to ~/OpenBSD/X.Y/amd64/BOOT/efi/boot/
	+ Copy the files under the BOOT partition of installXY.img for the arm64 arch to ~/OpenBSD/X.Y/arm64/BOOT/
	+ Download and copy [the Broadcom wifi drivers](https://github.com/pftf/RPi4/tree/master/firmware/brcm) for Raspberry Pi 4 to ~/OpenBSD/X.Y/arm64/firmware/

- Update the configuration files under config with the ones in the new versions of packages:
	+ Also update Doxyfile if the doxygen version has changed

- Update PFRE and SPRE:
	+ Update PFRE and SPRE to their current versions, support changes in pf and sslproxy if any
	+ Create and install the man2web package
	+ Produce pf.conf.html from pf.conf(5), sslproxy.html from sslproxy(1), and sslproxy.conf.html from sslproxy.conf(5) using man2web
	+ Merge the PFRE and SPRE changes from the previous html files, most importantly the anchors

- Update the PHP version numbers in the sources, both php and php-fpm, if upgrading PHP:
	+ config/etc/php-X.Y/
	+ config/etc/php-X.Y.ini
	+ config/utmfw.files
	+ config/utmfw.mtree
	+ meta/install.sub
	+ config/etc/rc.local
	+ config/etc/syslog.conf
	+ src/Model/system.php
	+ src/View/system/conf.startup.php

- Update phpseclib to its new version if any:
	+ Merge the UTMFW changes from the previous version

- Update d3js to its new version if any:
	+ Fix any issues caused by any API changes

- Update the registered snortrules.tar.gz:
	+ Make sure the directory structure is the same as the one in the old snortrules.tar.gz
	+ Add the black and white list files
	+ Compress

- Update blacklists.tar.gz:
	+ Download the black list database, such as from https://dsi.ut-capitole.fr/blacklists/download/blacklists_for_dansguardian.tar.gz
	+ Run the cats.php script to prepend category descriptions to each file
	+ Compress

- Update clamavdb.tar.gz:
	+ Download the virus db files
	+ Compress

- Strip xbase and xfont:
	+ Make sure the contents are the same as in the files in the old iso and img files, except for the version numbers
	+ SECURITY: Be very careful with the permissions of the directories and files in these install sets, they should be the same as the original files

- Run the create script:
	+ Install gettext-tools and doxygen for translations and documentation
	+ Run ./createiso or ./createimg under ~/utmfw/
