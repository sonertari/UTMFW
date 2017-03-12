# UTMFW

UTMFW is a UTM firewall running on OpenBSD 5.9.

## Features

## How to install

## How to build

The purpose in this section will be to build the installation iso file using the createiso script at the root of the project source tree. You are expected to be doing these on an OpenBSD 5.9 and have installed git on it.

The createiso script:

- Clones the git repo of the project to a tmp folder.
- Generates gettext translations.
- Prepares the site install set.
- And finally creates the iso file.

However, the source tree has links to OpenBSD install sets and packages, which should be broken, hence needs to be fixed when you first obtain the sources. Make sure you see those broken links now. So, before you can run createiso, you need to do a couple of things:

- Install sets:
	+ Obtain the sources of OpenBSD 5.9.
	+ Copy the UTMFW files under `openbsd/utmfw/distrib/miniroot` and `/openbsd/utmfw/sbin/disklabel` to the OpenBSD sources to replace the original files. You are advised to compare the original files with the UTMFW versions before replacing.
	+ Build an OpenBSD 5.9 release, as described in [faq5](http://www.openbsd.org/faq/faq5.html).
	+ Copy the required install sets to the appropriate locations to fix the broken links in the project.
- Packages:
	+ Download the required packages.
	+ Copy them to the appropriate locations to fix the broken links in the project.

Note that you can strip down xbase and xfont install sets to reduce the size of the iso file. See the UTMFW installation iso above for examples.

Now you can run the createiso script which should produce an iso file in the same folder as itself.

