<?php
/*
 * Copyright (C) 2004-2021 Soner Tari
 *
 * This file is part of UTMFW.
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

require_once('sslproxy.php');

$ruleCategoryNames = array(
	'proxyspecline' => _CONTROL('ProxySpecLine'),
	'proxyspecstruct' => _CONTROL('ProxySpecStruct'),
	'filter' => _CONTROL('Filter'),
	'filterstruct' => _CONTROL('FilterStruct'),
	'macro' => _CONTROL('Macro'),
	'option' => _CONTROL('Option'),
	'include' => _CONTROL('Include'),
	'comment' => _CONTROL('Comment'),
	'blank' => _CONTROL('Blank Line'),
	);

$ruleType2Class= array(
	'proxyspecline' => 'ProxySpecLine',
	'proxyspecstruct' => 'ProxySpecStruct',
	'filter' => 'Filter',
	'filterstruct' => 'FilterStruct',
	'macro' => 'Macro',
	'option' => 'Option',
	'include' => '_Include',
	'comment' => 'Comment',
	'blank' => 'Blank',
	);

$Options= array(
	'CACert',
	'CAKey',
	'ClientCert',
	'ClientKey',
	'CAChain',
	'LeafKey',
	'LeafCRLURL',
	'LeafCertDir',
	'DefaultLeafCert',
	'WriteGenCertsDir',
	'WriteAllCertsDir',
	'DenyOCSP',
	'Passthrough',
	'DHGroupParams',
	'ECDHCurve',
	'SSLCompression',
	'ForceSSLProto',
	'DisableSSLProto',
	'EnableSSLProto',
	'MinSSLProto',
	'MaxSSLProto',
	'Ciphers',
	'CipherSuites',
	'LeafKeyRSABits',
	'OpenSSLEngine',
	'NATEngine',
	'User',
	'Group',
	'Chroot',
	'PidFile',
	'ConnectLog',
	'ContentLog',
	'ContentLogDir',
	'ContentLogPathSpec',
	'LogProcInfo',
	'PcapLog',
	'PcapLogDir',
	'PcapLogPathSpec',
	'MirrorIf',
	'MirrorTarget',
	'MasterKeyLog',
	'Daemon',
	'Debug',
	'DebugLevel',
	'ConnIdleTimeout',
	'ExpiredConnCheckPeriod',
	'LogStats',
	'StatsPeriod',
	'RemoveHTTPAcceptEncoding',
	'RemoveHTTPReferer',
	'VerifyPeer',
	'AllowWrongHost',
	'UserAuth',
	'DivertUsers',
	'PassUsers',
	'UserDBPath',
	'UserTimeout',
	'UserAuthURL',
	'ValidateProto',
	'MaxHTTPHeaderSize',
	'OpenFilesLimit',
	'Divert',
	'PassSite',
);

$ProxySpecStructOptions= array(
	'Proto',
	'Addr',
	'Port',
	'Divert',
	'DivertAddr',
	'DivertPort',
	'ReturnAddr',
	'NatEngine',
	'SNIPort',
	'TargetAddr',
	'TargetPort',
	'DenyOCSP',
	'Passthrough',
	'CACert',
	'CAKey',
	'ClientCert',
	'ClientKey',
	'CAChain',
	'LeafCRLURL',
	'DHGroupParams',
	'ECDHCurve',
	'SSLCompression',
	'ForceSSLProto',
	'DisableSSLProto',
	'EnableSSLProto',
	'MinSSLProto',
	'MaxSSLProto',
	'Ciphers',
	'CipherSuites',
	'RemoveHTTPAcceptEncoding',
	'RemoveHTTPReferer',
	'VerifyPeer',
	'AllowWrongHost',
	'UserAuth',
	'DivertUsers',
	'PassUsers',
	'UserTimeout',
	'UserAuthURL',
	'ValidateProto',
	'MaxHTTPHeaderSize',
	'PassSite',
	'Define',
);

$FilterStructOptions= array(
	'Action',
	'User',
	'Desc',
	'SrcIp',
	'SNI',
	'CN',
	'Host',
	'URI',
	'DstIp',
	'DstPort',
	'Log',
	'ReconnectSSL',
	'DenyOCSP',
	'Passthrough',
	'CACert',
	'CAKey',
	'ClientCert',
	'ClientKey',
	'CAChain',
	'LeafCRLURL',
	'DHGroupParams',
	'ECDHCurve',
	'SSLCompression',
	'ForceSSLProto',
	'DisableSSLProto',
	'EnableSSLProto',
	'MinSSLProto',
	'MaxSSLProto',
	'Ciphers',
	'CipherSuites',
	'RemoveHTTPAcceptEncoding',
	'RemoveHTTPReferer',
	'VerifyPeer',
	'AllowWrongHost',
	'UserAuth',
	'UserTimeout',
	'UserAuthURL',
	'ValidateProto',
	'MaxHTTPHeaderSize',
);

if (filter_has_var(INPUT_GET, 'sender') && array_key_exists(filter_input(INPUT_GET, 'sender'), $ruleCategoryNames)) {
	$edit= filter_input(INPUT_GET, 'sender');
	$ruleNumber= filter_input(INPUT_GET, 'rulenumber');
	
	if (filter_has_var(INPUT_GET, 'state') && filter_input(INPUT_GET, 'state') == 'create') {
		// Get action has precedence
		// Accept only create action here
		$action= 'create';
	} elseif (filter_has_var(INPUT_POST, 'state') && filter_input(INPUT_POST, 'state') == 'create') {
		// Post action is used while saving new rules, create is the next state after add
		// Accept only create action here
		$action= 'create';
	} else {
		// Default action is edit, which takes care of unacceptable actions too
		$action= 'edit';
	}
}

$show= 'all';
if (isset($_SESSION['show'])) {
	$show= $_SESSION['show'];
}

if (filter_has_var(INPUT_POST, 'ruleNumber') && filter_input(INPUT_POST, 'ruleNumber') !== '') {
	if (filter_has_var(INPUT_POST, 'add')) {
		$edit= filter_input(INPUT_POST, 'category');
		$edit= $edit == 'all' ? 'filter' : $edit;
		$ruleNumber= filter_input(INPUT_POST, 'ruleNumber');
		$action= 'add';
	} elseif (filter_has_var(INPUT_POST, 'edit')) {
		$ruleNumber= filter_input(INPUT_POST, 'ruleNumber');
		if (array_key_exists($ruleNumber, $ruleSet->rules)) {
			$edit= array_search($ruleSet->rules[$ruleNumber]->cat, $ruleType2Class);
			$action= 'edit';
		} else {
			// Will add a new rule of category $edit otherwise
			$edit= filter_input(INPUT_POST, 'category');
			$edit= $edit == 'all' ? 'filter' : $edit;
			$action= 'add';
		}
	} elseif (filter_has_var(INPUT_POST, 'show')) {
		$show= filter_input(INPUT_POST, 'category');
		$_SESSION['show']= $show;
	}
}

$nested= FALSE;
// Set the nested var for the edit and add buttons only, because they open a new edit page, hence nested editing
// The other buttons stay on the same edit page, hence not nested editing
// Or set the nested var for all of the buttons on the plain edit page itself (editpage), if nested input is set
if (filter_has_var(INPUT_POST, 'nested') && (filter_has_var(INPUT_POST, 'edit') || filter_has_var(INPUT_POST, 'add') || filter_has_var(INPUT_POST, 'editpage'))) {
	$nested= TRUE;
}
// Only the edit button submits the nested field in dispEditLinks(), and only for the Include, ProxySpecStruct, and FilterStruct type of rules
if (filter_has_var(INPUT_GET, 'nested')) {
	$nested= TRUE;
}

if (isset($edit) || isset($_SESSION['saved_edit'])) {
	if (isset($edit)) {
		$cat= $ruleType2Class[$edit];
		if (($cat == 'ProxySpecStruct' || $cat == 'FilterStruct' || $cat == '_Include') && isset($_SESSION['saved_edit']) && !$nested) {
			$mainRuleset= $_SESSION['saved_edit']['ruleset'];
			$mainRuleNumber= $_SESSION['saved_edit']['ruleNumber'];
			$mainRuleset->setupEditSession($cat, $mainRuleset, $action, $mainRuleNumber);
		}
		else {
			$ruleSet->setupEditSession($cat, $ruleSet, $action, $ruleNumber);
		}
		$ruleObj= &$_SESSION['edit']['object'];
	}
	else {
		$ruleObj= &$_SESSION['saved_edit']['object'];
		$cat= $_SESSION['saved_edit']['cat'];
		$ruleNumber= $_SESSION['saved_edit']['ruleNumber'];
		$action= $_SESSION['saved_edit']['action'];
	}

	if (($cat == 'ProxySpecStruct' || $cat == 'FilterStruct') && !$nested) {
		unset($ruleCategoryNames['proxyspecline']);
		unset($ruleCategoryNames['proxyspecstruct']);
		unset($ruleCategoryNames['include']);

		if ($cat == 'ProxySpecStruct') {
			$baseCat= 'proxyspecstruct';
			$ruleSetFilename= 'ProxySpecStruct';
		}
		else {
			$baseCat= 'filterstruct';
			$ruleSetFilename= 'FilterStruct';
			unset($ruleCategoryNames['filter']);
			unset($ruleCategoryNames['filterstruct']);
		}

		require('conf.rulestruct.php');
	}
	else if ($cat == '_Include' && !$nested) {
		require('conf.include.php');
	}
	else {
		$baseCat= 'none';
		if (isset($_SESSION['saved_edit'])) {
			if ($_SESSION['saved_edit']['cat'] == 'ProxySpecStruct') {
				$Options= $ProxySpecStructOptions;
			}
			else if ($_SESSION['saved_edit']['cat'] == 'FilterStruct') {
				$Options= $FilterStructOptions;
			}
		}
		require('edit.php');
	}
}

if (filter_has_var(INPUT_GET, 'up')) {
	$ruleSet->up(filter_input(INPUT_GET, 'up'));
}

if (filter_has_var(INPUT_GET, 'down')) {
	$ruleSet->down(filter_input(INPUT_GET, 'down'));
}

if (filter_has_var(INPUT_GET, 'del')) {
	$ruleSet->del(filter_input(INPUT_GET, 'del'));
}

if (filter_has_var(INPUT_POST, 'move')) {
	if (filter_has_var(INPUT_POST, 'ruleNumber') && filter_input(INPUT_POST, 'ruleNumber') !== '' &&
		filter_has_var(INPUT_POST, 'moveTo') && filter_input(INPUT_POST, 'moveTo') !== '') {
		$ruleSet->move(filter_input(INPUT_POST, 'ruleNumber'), filter_input(INPUT_POST, 'moveTo'));
	}
}

if (filter_has_var(INPUT_POST, 'delete')) {
	$ruleSet->del(filter_input(INPUT_POST, 'ruleNumber'));
}

if (filter_has_var(INPUT_POST, 'deleteAll')) {
	$ruleSet->deleteRules();
	PrintHelpWindow(_NOTICE('Ruleset deleted'));
}

if (filter_has_var(INPUT_GET, 'comment')) {
	$ruleSet->comment(filter_input(INPUT_GET, 'comment'));
}

if (filter_has_var(INPUT_GET, 'uncomment')) {
	$ruleSet->uncomment(filter_input(INPUT_GET, 'uncomment'));
}

if (filter_has_var(INPUT_GET, 'separate')) {
	$ruleSet->separate(filter_input(INPUT_GET, 'separate'));
}

if (filter_has_var(INPUT_POST, 'parse')) {
	$ruleSet->parse();
}

$View->Controller($Output, 'TestRules', json_encode($ruleSet->rules));

require_once($VIEW_PATH.'/header.php');
?>
<fieldset>
	<form action="<?php echo filter_input(INPUT_SERVER, 'PHP_SELF'); ?>" method="post">
		<input type="submit" name="show" value="<?php echo _CONTROL('Show') ?>" />
		<select id="category" name="category">
			<option value="all"><?php echo _CONTROL('All') ?></option>
			<?php
			foreach ($ruleCategoryNames as $category => $name) {
				?>
				<option value="<?php echo $category; ?>" <?php echo (filter_input(INPUT_POST, 'category') == $category || $show == $category ? 'selected' : ''); ?>><?php echo $name; ?></option>
				<?php
			}
			?>
		</select>
		<input type="submit" name="add" value="<?php echo _CONTROL('Add') ?>" />
		<label for="ruleNumber"><?php echo _CONTROL('as rule number') ?>:</label>
		<input type="text" name="ruleNumber" id="ruleNumber" size="5" value="<?php echo $ruleSet->nextRuleNumber(); ?>" placeholder="<?php echo _CONTROL('number') ?>" />
		<input type="submit" name="edit" value="<?php echo _CONTROL('Edit') ?>" />
		<input type="submit" name="delete" value="<?php echo _CONTROL('Delete') ?>" onclick="return confirm('<?php echo _CONTROL('Are you sure you want to delete the rule?') ?>')"/>
		<input type="text" name="moveTo" id="moveTo" size="5" value="<?php echo filter_input(INPUT_POST, 'moveTo') ?>" placeholder="<?php echo _CONTROL('move to') ?>" />
		<input type="submit" name="move" value="<?php echo _CONTROL('Move') ?>" />
		<input type="submit" id="deleteAll" name="deleteAll" value="<?php echo _CONTROL('Delete All') ?>" onclick="return confirm('<?php echo _CONTROL('Are you sure you want to delete the entire ruleset?') ?>')"/>
		<input type="submit" name="parse" value="<?php echo _CONTROL('Parse') ?>"  title="<?php echo _TITLE('Merges separated comments') ?>"/>
	</form>
</fieldset>
<?php
echo _TITLE('Rules file') . ': ' . $ruleSet->filename . ($ruleSet->uploaded ? ' (' . _TITLE('uploaded') . ')' : '');
?>
<table id="logline">
	<tr>
		<th><?php echo _TITLE('Rule') ?></th>
		<th><?php echo _TITLE('Edit') ?></th>
		<th><?php echo _TITLE('Type') ?></th>
		<th><?php echo _TITLE('Line') ?></th>
		<th colspan="4"><?php echo _TITLE('Rule') ?></th>
		<th><?php echo _TITLE('Comment') ?></th>
	</tr>
	<?php
	$ruleNumber= 0;
	// Passed as a global var.
	$lineNumber= 1;
	$count = count($ruleSet->rules) - 1;
	foreach ($ruleSet->rules as $rule) {
		if ($show == 'all' || $ruleType2Class[$show] == $rule->cat) {
			$rule->display($ruleNumber, $count);
		}
		$ruleNumber++;
		$lineNumber++;
	}
	?>
</table>
<?php
require_once($VIEW_PATH . '/footer.php');
?>
