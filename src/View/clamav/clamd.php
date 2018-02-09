<?php
/*
 * Copyright (C) 2004-2018 Soner Tari
 *
 * This file is part of UTMFW.
 *
 * UTMFW is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * UTMFW is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with UTMFW.  If not, see <http://www.gnu.org/licenses/>.
 */

require_once('include.php');

$LogConf = array(
    'clamd' => array(
        'Fields' => array(
            'Date' => _TITLE('Date'),
            'Time' => _TITLE('Time'),
            'Process' => _TITLE('Process'),
            'Prio' => _TITLE('Prio'),
            'Log' => _TITLE('Log'),
    		),
        'HighlightLogs' => array(
            'REs' => array(
                'red' => array('FOUND', 'ERROR'),
                'yellow' => array('Started', 'Database modification detected'),
                'green' => array('Database status OK', 'Database correctly reloaded'),
        		),
    		),
		),
	);

class Clamd extends View
{
	public $Model= 'clamd';

	public $Layout= 'clamd';

	function __construct()
	{
		$this->Module= basename(dirname($_SERVER['PHP_SELF']));
		$this->Caption= _TITLE('Virus Filter');

		$this->LogsHelpMsg= _HELPWINDOW('Clamd logs virus scan results, virus database checks, and database reloads.');
		$this->GraphHelpMsg= _HELPWINDOW('Since Freshclam wakes up periodically, this page displays graphs for Clamd process only.');
		$this->ConfHelpMsg= _HELPWINDOW('By default, Clamd accepts virus scan requests from processes running on the system only, such as the web filter. Default settings should be suitable for most purposes.');
	
		$this->Config = array(
			'LogClean' => array(
				'title' => _TITLE2('Log Clean'),
				'info' => _HELPBOX2('Also log clean files. Useful in debugging but drastically increases the log size.
		Default: disabled'),
				),
			'LogVerbose' => array(
				'title' => _TITLE2('Log Verbose'),
				'info' => _HELPBOX2('Enable verbose logging.
		Default: disabled'),
				),
			'MaxThreads' => array(
				'title' => _TITLE2('Max Threads'),
				'info' => _HELPBOX2('Maximal number of threads running at the same time.
		Default: 10'),
				),
			'MaxDirectoryRecursion' => array(
				'title' => _TITLE2('Max Directory Recursion'),
				'info' => _HELPBOX2('Maximal depth directories are scanned at.
		Default: 15'),
				),
			'FollowDirectorySymlinks' => array(
				'title' => _TITLE2('Follow Directory Symlinks'),
				'info' => _HELPBOX2('Follow directory symlinks.
		Default: disabled'),
				),
			'FollowFileSymlinks' => array(
				'title' => _TITLE2('Follow File Symlinks'),
				'info' => _HELPBOX2('Follow regular file symlinks.
		Default: disabled'),
				),
			'SelfCheck' => array(
				'title' => _TITLE2('Self Check'),
				'info' => _HELPBOX2('Perform internal sanity check (database integrity and freshness).
		Default: 1800 (30 min)'),
				),
			'Debug' => array(
				'title' => _TITLE2('Debug'),
				'info' => _HELPBOX2('Enable debug messages in libclamav.
		Default: disabled'),
				),
			'LeaveTemporaryFiles' => array(
				'title' => _TITLE2('Leave Temporary Files'),
				'info' => _HELPBOX2('Do not remove temporary files (for debug purposes).
		Default: disabled'),
				),
			'ScanPE' => array(
				'title' => _TITLE2('Scan PE'),
				'info' => _HELPBOX2('PE stands for Portable Executable - it\'s an executable file format used in all 32-bit versions of Windows operating systems. This option allows ClamAV to perform a deeper analysis of executable files and it\'s also required for decompression of popular executable packers such as UPX, FSG, and Petite.
		Default: enabled'),
				),
			'DetectBrokenExecutables' => array(
				'title' => _TITLE2('Detect Broken Executables'),
				'info' => _HELPBOX2('With this option clamav will try to detect broken executables and mark them as Broken.Executable
		Default: disabled'),
				),
			'ScanOLE2' => array(
				'title' => _TITLE2('Scan OLE2'),
				'info' => _HELPBOX2('This option enables scanning of Microsoft Office document macros.
		Default: enabled'),
				),
			'ScanMail' => array(
				'title' => _TITLE2('Scan Mail'),
				'info' => _HELPBOX2('Enable internal e-mail scanner.
		Default: enabled'),
				),
			'MailFollowURLs' => array(
				'title' => _TITLE2('Mail Follow URLs'),
				'info' => _HELPBOX2('If an email contains URLs ClamAV can download and scan them.
		WARNING: This option may open your system to a DoS attack.
		Never use it on loaded servers.
		Default: disabled'),
				),
			'ScanHTML' => array(
				'title' => _TITLE2('Scan HTML'),
				'info' => _HELPBOX2('Perform HTML normalisation and decryption of MS Script Encoder code.
		Default: enabled'),
				),
			'ScanArchive' => array(
				'title' => _TITLE2('Scan Archive'),
				'info' => _HELPBOX2('ClamAV can scan within archives and compressed files.
		Default: enabled'),
				),
			'ScanRAR' => array(
				'title' => _TITLE2('Scan RAR'),
				'info' => _HELPBOX2('Due to license issues libclamav does not support RAR 3.0 archives (only the old 2.0 format is supported). Because some users report stability problems with unrarlib it\'s disabled by default and you must uncomment the directive below to enable RAR 2.0 support.
		Default: disabled'),
				),
			'ArchiveMaxFileSize' => array(
				'title' => _TITLE2('Archive Max File Size'),
				'info' => _HELPBOX2('The options below protect your system against Denial of Service attacks using archive bombs.

		Files in archives larger than this limit won\'t be scanned.
		Value of 0 disables the limit.
		Default: 10M'),
				),
			'ArchiveMaxRecursion' => array(
				'title' => _TITLE2('Archive Max Recursion'),
				'info' => _HELPBOX2('Nested archives are scanned recursively, e.g. if a Zip archive contains a RAR file, all files within it will also be scanned. This options specifies how deep the process should be continued.
		Value of 0 disables the limit.
		Default: 8'),
				),
			'ArchiveMaxFiles' => array(
				'title' => _TITLE2('Archive Max Files'),
				'info' => _HELPBOX2('Number of files to be scanned within an archive.
		Value of 0 disables the limit.
		Default: 1000'),
				),
			'ArchiveMaxCompressionRatio' => array(
				'title' => _TITLE2('Archive Max Compression Ratio'),
				'info' => _HELPBOX2('If a file in an archive is compressed more than ArchiveMaxCompressionRatio times it will be marked as a virus (Oversized.ArchiveType, e.g. Oversized.Zip)
		Value of 0 disables the limit.
		Default: 250'),
				),
			'ArchiveLimitMemoryUsage' => array(
				'title' => _TITLE2('Archive Limit Memory Usage'),
				'info' => _HELPBOX2('Use slower but memory efficient decompression algorithm.
		Only affects the bzip2 decompressor.
		Default: disabled'),
				),
			'ArchiveBlockEncrypted' => array(
				'title' => _TITLE2('Archive Block Encrypted'),
				'info' => _HELPBOX2('Mark encrypted archives as viruses (Encrypted.Zip, Encrypted.RAR).
		Default: disabled'),
				),
			'ArchiveBlockMax' => array(
				'title' => _TITLE2('Archive Block Max'),
				'info' => _HELPBOX2('Mark archives as viruses (e.g. RAR.ExceededFileSize, Zip.ExceededFilesLimit) if ArchiveMaxFiles, ArchiveMaxFileSize, or ArchiveMaxRecursion limit is reached.
		Default: disabled'),
				),
			);
	}
}

$View= new Clamd();
?>
