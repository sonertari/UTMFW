<?php
/*
 * Copyright (C) 2004-2025 Soner Tari
 *
 * This file is part of PFRE.
 *
 * PFRE is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PFRE is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PFRE.  If not, see <http://www.gnu.org/licenses/>.
 */

/** @file
 * Load, upload, download, save, and delete page.
 */

use View\RuleSet;

require_once ('pf.php');

if (filter_has_var(INPUT_POST, 'reload')) {
	$force= 0;
	/// @attention Need separate force* vars for each form, otherwise they all are checked
	if (filter_has_var(INPUT_POST, 'forceload')) {
		$force= 1;
	}

	$ruleSet= new RuleSet();
	if ($ruleSet->load('/etc/pf.conf', NULL, $force)) {
		$View->RuleSet= $ruleSet;
		PrintHelpWindow(_NOTICE('Main pf rules reloaded') . ': ' . $View->RuleSet->filename);
	} else {
		PrintHelpWindow('<br>' . _NOTICE('Failed loading main pf rules'), NULL, 'ERROR');
	}
	/// @attention Remove the session, otherwise the existing session confuses the user if s/he edits the same rule number in the session
	unset($_SESSION['edit']);
}

$loadfile= '';
if (filter_has_var(INPUT_POST, 'load')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forceload')) {
		$force= 1;
	}

	// Accept only file names, no paths
	$loadfile= basename(filter_input(INPUT_POST, 'filename'));
	$filepath= "$PF_CONFIG_PATH/$loadfile";
	
	$ruleSet= new RuleSet();
	if ($ruleSet->load($filepath, 0, $force)) {
		$View->RuleSet= $ruleSet;
		PrintHelpWindow(_NOTICE('Rules loaded') . ': ' . $View->RuleSet->filename);
	} else {
		PrintHelpWindow('<br>' . _NOTICE('Failed loading') . ": $filepath", NULL, 'ERROR');
	}
	unset($_SESSION['edit']);
}

$savefile= '';
if (filter_has_var(INPUT_POST, 'save')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forcesave')) {
		$force= 1;
	}

	// Accept only file names, no paths
	$savefile= basename(filter_input(INPUT_POST, 'saveFilename'));
	$filepath= "$PF_CONFIG_PATH/$savefile";

	if ($force || $View->Controller($Output, 'TestRules', json_encode($View->RuleSet->rules))) {
		/// @attention Use 0, not FALSE for boolean here, otherwise arg type check fails
		if ($View->Controller($Output, 'InstallRules', json_encode($View->RuleSet->rules), $filepath, 0, $force)) {
			$View->RuleSet->filename= $filepath;
			PrintHelpWindow(_NOTICE('Saved') . ": $filepath");
		} else {
			PrintHelpWindow('<br>' . _NOTICE('Failed saving') . ": $filepath", NULL, 'ERROR');
		}
	} else {
		PrintHelpWindow('<br>' . _NOTICE('Failed saving') . ": $filepath" . ', ' . _NOTICE('ruleset has errors'), NULL, 'ERROR');
	}
}

$deleteFile= '';
if (filter_has_var(INPUT_POST, 'remove')) {
	// Accept only file names, no paths
	$deleteFile= basename(filter_input(INPUT_POST, 'deleteFilename'));
	$filepath= "$PF_CONFIG_PATH/$deleteFile";
	
	if ($View->Controller($Output, 'DeleteRuleFile', $filepath)) {
		PrintHelpWindow(_NOTICE('Rules file deleted') . ": $filepath");
	} else {
		PrintHelpWindow('<br>' . _NOTICE('Failed deleting') . ": $filepath", NULL, 'ERROR');
	}
}

if (filter_has_var(INPUT_POST, 'upload')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forceupload')) {
		$force= 1;
	}

	if ($_FILES['file']['error'] == 0) {
		$ruleSet= new RuleSet();
		if ($ruleSet->load($_FILES['file']['tmp_name'], TRUE, $force, $_FILES['file']['name'])) {
			$View->RuleSet= $ruleSet;
			/// @todo Unlink the tmp file?
			PrintHelpWindow(_NOTICE('File uploaded') . ': ' . $_FILES['file']['name']);
		} else {
			PrintHelpWindow('<br>' . _NOTICE('Failed uploading') . ': ' . $_FILES['file']['name'], NULL, 'ERROR');
		}
	} else {
		PrintHelpWindow(_NOTICE('Failed uploading') . ': ' . $_FILES['file']['tmp_name'], NULL, 'ERROR');
	}
	unset($_SESSION['edit']);
}

if (filter_has_var(INPUT_POST, 'download')) {
	$force= 0;
	if (filter_has_var(INPUT_POST, 'forcedownload')) {
		$force= 1;
	}

	if ($View->Controller($Output, 'GenerateRules', json_encode($View->RuleSet->rules), 0, $force) || $force) {
		if (filter_has_var(INPUT_SERVER, 'HTTP_USER_AGENT') && preg_match("/MSIE/", filter_input(INPUT_SERVER, 'HTTP_USER_AGENT'))) {
			// For IE
			ini_set('zlib.output_compression', 'Off');
		}

		header('Content-Type: application/octet-stream');

		if ($View->RuleSet->filename != '') {
			$filename= basename($View->RuleSet->filename);
		} else {
			$filename= 'pf.conf';
		}

		header('Content-Disposition: attachment; filename="' . $filename . '"');

		echo implode("\n", $Output);
		exit;
	} else {
		PrintHelpWindow('<br>' . _NOTICE('Failed downloading, cannot generate pf rules'), NULL, 'ERROR');
	}
}

$View->Controller($Output, 'GetRuleFiles');
$ruleFiles= $Output;

require_once($VIEW_PATH.'/header.php');
?>
<table class="shadowbox">
	<tr>
		<td>
			<h2><?php echo _TITLE('Load ruleset') ?></h2>
			<br />
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
				<input type="submit" id="reload" name="reload" value="<?php echo _CONTROL('Reload') ?>" />
				<label for="reload"><?php echo _CONTROL('Reload main ruleset') . ': /etc/pf.conf' ?></label>
				<br />
				<select id="filename" name="filename">
					<option value="" label=""></option>
					<?php
					foreach ($ruleFiles as $file) {
						?>
						<option value="<?php echo $file; ?>" label="<?php echo $file; ?>" <?php echo ($loadfile == $file ? 'selected' : ''); ?>><?php echo $file; ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" id="load" name="load" value="<?php echo _CONTROL('Load') ?>" />
				<label for="load"><?php echo _CONTROL('Load rules from file') ?></label>
				<input type="checkbox" id="forceload" name="forceload" <?php echo filter_has_var(INPUT_POST, 'forceload') ? 'checked' : ''; ?> />
				<label for="forceload"><?php echo _CONTROL('Load with errors') ?></label>
			</form>

			<p>&nbsp;</p>

			<h2><?php echo _TITLE('Save ruleset') ?></h2>
			<br />
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
				<input type="text" name="saveFilename" id="saveFilename" value="<?php echo $savefile; ?>" />
				<input type="submit" id="save" name="save" value="<?php echo _CONTROL('Save') ?>" />
				<label for="save"><?php echo _CONTROL('Save ruleset to file') ?></label>
				<input type="checkbox" id="forcesave" name="forcesave" <?php echo filter_has_var(INPUT_POST, 'forcesave') ? 'checked' : ''; ?> />
				<label for="forcesave"><?php echo _CONTROL('Save with errors') ?></label>
			</form>

			<p>&nbsp;</p>

			<h2><?php echo _TITLE('Delete ruleset') ?></h2>
			<br />
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
				<select id="deleteFilename" name="deleteFilename">
					<option value="" label=""></option>
					<?php
					foreach ($ruleFiles as $file) {
						?>
						<option value="<?php echo $file; ?>" label="<?php echo $file; ?>" <?php echo ($deleteFile == $file ? 'selected' : ''); ?>><?php echo $file; ?></option>
						<?php
					}
					?>
				</select>
				<input type="submit" id="remove" name="remove" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _CONTROL('Are you sure you want to delete the rules file?') ?>')"/>
				<label for="remove"><?php echo _CONTROL('Delete rules file') ?></label>
			</form>

			<p>&nbsp;</p>

			<h2><?php echo _TITLE('Upload ruleset') ?></h2>
			<br />
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" enctype="multipart/form-data" method="post">
				<input type="submit" id="upload" name="upload" value="<?php echo _CONTROL('Upload') ?>" />
				<input type="hidden" name="max_file_size" value="300000" />
				<?php echo _TITLE('Upload rules file') ?>: <input name="file" type="file" />
				<input type="checkbox" id="forceupload" name="forceupload" <?php echo filter_has_var(INPUT_POST, 'forceupload') ? 'checked' : ''; ?> />
				<label for="forceupload"><?php echo _CONTROL('Upload with errors') ?></label>
			</form>

			<p>&nbsp;</p>

			<h2><?php echo _TITLE('Download ruleset') ?></h2>
			<br />
			<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
				<input type="submit" id="download" name="download" value="<?php echo _CONTROL('Download') ?>" />
				<label for="download"><?php echo _CONTROL('Download ruleset') ?></label>
				<input type="checkbox" id="forcedownload" name="forcedownload" <?php echo filter_has_var(INPUT_POST, 'forcedownload') ? 'checked' : ''; ?> />
				<label for="forcedownload"><?php echo _CONTROL('Download with errors') ?></label>
			</form>
		</td>
	</tr>
</table>
<?php
require_once($VIEW_PATH.'/footer.php');
?>
