<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2005 Urs Weiss (urs@tupa-dns.org)
*  All rights reserved
*
*  This file is part of TUPA.
*
*  TUPA is free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*  TUPA is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * Domain functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_domains {

	/**
	 * Creates form to add or edit domain record.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function addEditDomain($conf) {
		global $TBE_TEMPLATE, $TUPA_CONF_VARS, $USER, $LANG;

		$content = '';
		$row = '';
		$soa = '';
		$selectedOwnerId = '';
		$hiddenFields = '';
		$markerArray = lib_div::setDefaultMarkerArray();
		$editFieldList = '';

		if (isset($conf['data']['id']) && $conf['data']['id']) {
			$id = $conf['data']['id'];
			settype($id, 'integer');
			// ID set, check edit permissions
			if (!($USER->hasPerm('domains_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('domains_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true))) {
				lib_logging::addLogMessage('domains', 'edit', 'permission', 'logMsgNoPermission');
				return $TBE_TEMPLATE->noPermissionMessage();
			}
			$content .= $TBE_TEMPLATE->header($LANG->getLang('editDomainTitle'));
			$cmd = 'edit';
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('domains.*, domain_owners.usr_id', 'domains, domain_owners', 'domains.id=domain_owners.dom_id AND id='. lib_DB::fullQuoteStr($id), '', '', '1');
			if (mysql_error()) {
				lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			$row = mysql_fetch_assoc($res);

			// get the domain name for regEx
			$domainName = preg_quote($row['name'], '/');

			lib_div::stripSlashesOnArray($row);
			lib_div::htmlspecialcharOnArray($row);

			// Get owner ID of record
			$selectedOwnerId = $row['usr_id'];

			// Get the SOA record of the domain
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'records', 'domain_id='. lib_DB::fullQuoteStr($row['id']) .' AND type=\'SOA\'', '', '', '');
			if (mysql_error()) {
				lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			if (mysql_num_rows($res) == '1') {
				$srow = mysql_fetch_assoc($res);
				$soa = explode(' ', $srow['content']);
			}

			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'records', 'domain_id='. lib_DB::fullQuoteStr($row['id']) .' AND type<>\'SOA\'', '', 'tupasorting', '');
			if (mysql_error()) {
				lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			$editFieldArr = array();

			while ($rrow = mysql_fetch_assoc($res)) {
				$keyValuePairs = array();
				foreach ($rrow as $key => $value) {
					// When $key ist equal 'name' cut the domain name from $value for display
					if ($key == 'name') {
						$value = preg_replace('/^(.*)'. $domainName .'$/', '\1', $value);
						$value = preg_replace('/^\.+/', '', $value);
						$value = preg_replace('/\.+$/', '', $value);
					} elseif ($key == 'prio' && $value == '0') {
						$value = '';
					}

					$keyValuePairs[] = $key .'='. $value;
				}
				$editFieldArr[] = implode(',', $keyValuePairs);
			}
			$editFieldList = implode('|', $editFieldArr);

			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('editDomainButtonChange') .'" class="'. STYLE_BUTTON .'" />';
		// else => add record
		} else {
			// Check permissions
			if (!$USER->hasPerm('domains_add, domains_add_group')) {
				lib_logging::addLogMessage('domains', 'add', 'permission', 'logMsgNoPermission');
				return  $TBE_TEMPLATE->noPermissionMessage();
			}
			$content .= $TBE_TEMPLATE->header($LANG->getLang('addDomainSingleTitle'));
			$cmd = 'add';
			$hiddenFields .= '<input type="hidden" name="soa_serial" value="'. date("Ymd") .'00" />';
			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('addDomainButtonAdd') .'" class="'. STYLE_BUTTON .'" />';
		}

		// Start table and set records table headers
		$markerArray['domain_records_table'] = '
			<table cellpadding="0" cellspacing="0" border="0" id="records-table"><tbody><tr>
				<td>'. $LANG->getLang('labelHostname') .'</td>
				<td>&nbsp;</td>
				<td>'. $LANG->getLang('labelTTL') .'</td>
				<td>&nbsp;</td>
				<td>'. $LANG->getLang('labelType') .'</td>
				<td>'. $LANG->getLang('labelPrio') .'</td>
				<td>'. $LANG->getLang('labelContent') .'</td>
				<td colspan="6">&nbsp;</td>
			</tr></tbody></table>';

		// New template? add first row
		if ($cmd == 'add' OR ($cmd == 'edit' && !$editFieldList)) {
			$TBE_TEMPLATE->addMessage('', '', 'addRow(\'records-table\', \'0\', \'1\')');
		} elseif ($cmd == 'edit') {
//			$TBE_TEMPLATE->addMessage('', '', 'addRowsToEdit(\'records-table\', \'name='. $editFieldList .'\', \''. $domainName .'\')');
			$TBE_TEMPLATE->addMessage('', '', 'addRowsToEdit(\'records-table\', \''. $editFieldList .'\', \''. $domainName .'\')');
		}

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'domains\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'domains.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###DOMAIN_ADD_EDIT###');

		// Template name
		$markerArray['label_domain'] = $LANG->getLang('labelDomain');
		$markerArray['input_domain'] = '<input type="text" name="name" class="'. STYLE_FIELD .'" size="30" alt="custom" pattern="'. $TUPA_CONF_VARS['REGEX']['domain'] .'" emsg="'. $LANG->getLang('domainError') .'" '. (lib_div::isset_value($row, 'name') ? 'value="'. $row['name'] .'"' : '') .' />'. $LANG->getHelp('helpDomainsDomain');

		// Owner selection
		if ($USER->hasPerm('domains_'. $cmd .'_group') && $USER->hasPerm('users_show_group')) {
			$markerArray['label_domain_owner'] = $LANG->getLang('labelDomainOwner');
			$markerArray['select_domain_owner'] = $TBE_TEMPLATE->ownerSelectBoxes('domains', $selectedOwnerId). $LANG->getHelp('helpDomainsOwner');
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###DOMAIN_OWNER###', '');
		}

		$markerArray['template_soa_subtitle'] = $LANG->getLang('templateSoaSubtitle');
		$markerArray['label_soa_primary'] = $LANG->getLang('labelSoaPrimary');
		$markerArray['input_soa_primary'] = '<input type="text" name="soa_primary" class="'. STYLE_FIELD .'" size="30" alt="custom" pattern="'. $TUPA_CONF_VARS['REGEX']['templateDomain'] .'" emsg="'. $LANG->getLang('soaPrimaryError') .'" '. (lib_div::isset_value($soa, '0') ? 'value="'. $soa['0'] .'"' : 'value="'. $TUPA_CONF_VARS['DNS']['defaultSoaPrimary']) .'" />'. $LANG->getHelp('helpSoaPrimary');
//		$markerArray['label_soa_hostmaster'] = $LANG->getLang('labelSoaHostmaster');
//		$markerArray['input_soa_hostmaster'] = '<input type="text" name="soa_hostmaster" class="'. STYLE_FIELD .'" size="30" alt="email" emsg="'. $LANG->getLang('soaHostmasterError') .'" '. (lib_div::isset_value($soa, '1') ? 'value="'. $soa['1'] .'"' : 'value="'. $TUPA_CONF_VARS['DNS']['defaultSoaHostmaster']) .'" />';
		$markerArray['label_soa_refresh'] = $LANG->getLang('labelSoaRefresh');
		$tmpVarArr = array('min'=>$TUPA_CONF_VARS['DNS']['minSoaRefresh'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaRefresh']);
		$markerArray['input_soa_refresh'] = '<input type="text" name="soa_refresh" class="'. STYLE_FIELD .'" size="10" alt="number|0|'. $TUPA_CONF_VARS['DNS']['minSoaRefresh'] .'|'. $TUPA_CONF_VARS['DNS']['maxSoaRefresh'] .'" emsg="'. $LANG->getLang('soaRefreshError', $tmpVarArr) .'" '. (lib_div::isset_value($soa, '3') ? 'value="'. $soa['3'] .'"' : 'value="'. $TUPA_CONF_VARS['DNS']['defaultSoaRefresh']) .'" />'. $LANG->getHelp('helpSoaRefresh', $tmpVarArr);
		$markerArray['label_soa_retry'] = $LANG->getLang('labelSoaRetry');
		$tmpVarArr = array('min'=>$TUPA_CONF_VARS['DNS']['minSoaRetry'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaRetry']);
		$markerArray['input_soa_retry'] = '<input type="text" name="soa_retry" class="'. STYLE_FIELD .'" size="10" alt="number|0|'. $TUPA_CONF_VARS['DNS']['minSoaRetry'] .'|'. $TUPA_CONF_VARS['DNS']['maxSoaRetry'] .'" emsg="'. $LANG->getLang('soaRetryError', $tmpVarArr) .'" '. (lib_div::isset_value($soa, '4') ? 'value="'. $soa['4'] .'"' : 'value="'. $TUPA_CONF_VARS['DNS']['defaultSoaRetry']) .'" />'. $LANG->getHelp('helpSoaRetry', $tmpVarArr);
		$markerArray['label_soa_expire'] = $LANG->getLang('labelSoaExpire');
		$tmpVarArr = array('min'=>$TUPA_CONF_VARS['DNS']['minSoaExpire'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaExpire']);
		$markerArray['input_soa_expire'] = '<input type="text" name="soa_expire" class="'. STYLE_FIELD .'" size="10" alt="number|0|'. $TUPA_CONF_VARS['DNS']['minSoaExpire'] .'|'. $TUPA_CONF_VARS['DNS']['maxSoaExpire'] .'" emsg="'. $LANG->getLang('soaExpireError', $tmpVarArr) .'" '. (lib_div::isset_value($soa, '5') ? 'value="'. $soa['5'] .'"' : 'value="'. $TUPA_CONF_VARS['DNS']['defaultSoaExpire']) .'" />'. $LANG->getHelp('helpSoaExpire', $tmpVarArr);
		$markerArray['label_soa_ttl'] = $LANG->getLang('labelSoaTtl');
		$tmpVarArr = array('min'=>$TUPA_CONF_VARS['DNS']['minSoaTTL'], 'max'=>$TUPA_CONF_VARS['DNS']['maxSoaTTL']);
		$markerArray['input_soa_ttl'] = '<input type="text" name="soa_ttl" class="'. STYLE_FIELD .'" size="10" alt="number|0|'. $TUPA_CONF_VARS['DNS']['minSoaTTL'] .'|'. $TUPA_CONF_VARS['DNS']['maxSoaTTL'] .'" emsg="'. $LANG->getLang('soaTtlError', $tmpVarArr) .'" '. (lib_div::isset_value($soa, '6') ? 'value="'. $soa['6'] .'"' : 'value="'. $TUPA_CONF_VARS['DNS']['defaultSoaTTL']) .'" />'. $LANG->getHelp('helpSoaTTL', $tmpVarArr);

		$markerArray['domain_records_subtitle'] = $LANG->getLang('domainRecordsSubtitle');

		$hiddenFields .= '<input type="hidden" name="cmd" value="'. $cmd .'" />';
		$cmd == 'edit' ? $hiddenFields .= '<input type="hidden" name="id" value="'. $id .'" />' : '';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInFormTags($subpart, 'processDomain(document.forms[0]);');

		return $content;
	}


	/**
	 * Creates form to add domains with template.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function addDomainWithTemplate($conf) {
		global $TBE_TEMPLATE, $TUPA_CONF_VARS, $USER, $LANG;
		// Check permissions
		if (!($USER->hasPerm('domains_add, domains_add_group') && $USER->hasPerm('domains_add_with_template'))) {
			lib_logging::addLogMessage('domains', 'add', 'permission', 'logMsgNoPermission');
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$markerArray = lib_div::setDefaultMarkerArray();
		$content = '';
		$hiddenFields = '';
		$cmd = 'tadd';

		$content .= $TBE_TEMPLATE->header($LANG->getLang('addDomainWithTemplateTitle'));
		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('addDomainButtonAdd') .'" class="'. STYLE_BUTTON .'" />';

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'domains\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'domains.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###DOMAIN_ADD_WITH_TEMPLATE###');

		// Get list of templates
		if ($USER->hasPerm('domain_admin')) {
			$userIdList = '';
		} elseif ($USER->hasPerm('domains_add_group')) {
			$userIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetUserIdsOfGroup($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']));
			if (mysql_error()) {
				lib_logging::addLogMessage('domains', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
		} else {
			$userIdList = $_SESSION['uid'];
		}
		$templateOptions = $TBE_TEMPLATE->templateSelectOptions($userIdList);

		// Template selector
		$markerArray['label_domain_template'] = $LANG->getLang('labelDomainTemplate');
		$markerArray['select_domain_template'] = '<select name="domain_template" onchange="getDomainExample(this.value);" alt="select" emsg="'. $LANG->getLang('domainTemplateError') .'" class="'. STYLE_FIELD .'">' . $templateOptions .'</select>'. $LANG->getHelp('helpDomainsTemplate');

		// Owner selection
		if ($USER->hasPerm('domains_add_group')) {
			$markerArray['label_domain_owner'] = $LANG->getLang('labelDomainOwner');
			$markerArray['select_domain_owner'] = $TBE_TEMPLATE->ownerSelectBoxes('domains'). $LANG->getHelp('helpDomainsDomainOwner');
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###DOMAIN_OWNER###', '');
		}

		// Domain list textarea
		$markerArray['label_domains'] = $LANG->getLang('labelDomainList');
		$markerArray['textarea_domains'] = '<textarea name="domains" alt="custom" pattern="'. $TBE_TEMPLATE->substituteMarker($TUPA_CONF_VARS['REGEX']['textareaMultiDomains'], '%maxTmplDomains%', $TUPA_CONF_VARS['DOMAINS']['maxTmplDomains']) .'" emsg="'. $LANG->getLang('domainListError', array('limit' => $TUPA_CONF_VARS['DOMAINS']['maxTmplDomains'])) .'" class="'. STYLE_FIELD .'" rows="6" cols="40" wrap="on"></textarea>'. $LANG->getHelp('helpDomainsDomainList');

		$markerArray['domain_ex_records_subtitle'] = $LANG->getLang('domainExampleRecords');
		$markerArray['domain_ex_records'] = $TBE_TEMPLATE->wrapInDiv('div-domain-ex-records');

		$hiddenFields .= '<input type="hidden" name="cmd" value="'. $cmd .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInFormTags($subpart, 'processDomain(document.forms[0]);');

		return $content;
	}


	/**
	 * Gets the template records to display as an example.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function getDomainExample($conf) {
		global $TBE_TEMPLATE, $USER;
		// Check permissions
		if (!($USER->hasPerm('domains_add, domains_add_group') && $USER->hasPerm('domains_add_with_template'))) {
			lib_logging::addLogMessage('domains', 'add', 'permission', 'logMsgNoPermission');
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$content = '';
		$id = $conf['data']['id'];
		settype($id, 'integer');

		// Get template records from submitted id
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'template_records', 'tmpl_id='. lib_DB::fullQuoteStr($id), '', 'tupasorting', '');
		if (mysql_error()) {
			lib_logging::addLogMessage('domains', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
		}

		$content .= '<table width="100%" cellspacing="0" cellpadding="0">';
		while ($row = mysql_fetch_assoc($res)) {
			$content .= '<tr>';
			$content .= '<td>'. $row['name'] .'</td><td>.domain.com</td><td>'. $row['ttl'] .'</td><td>IN</td><td>'. $row['type'] .'</td><td>'. $row['prio'] .'</td><td>'. $row['content'] .'</td>';
			$content .= '</tr>';
		}
		$content .= '</table>';

		return $content;
	}


	/**
	 * Creates form to delete domain.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function deleteDomain($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;
		$ids = lib_div::trimExplode(',', $conf['data']['id']);
		$idCount = count($ids);
		$domainNames = array();
		$error = false;

		// check permissions for every record
		foreach ($ids as $id) {
			settype($id, 'integer');
			// Check permissions
			if (!($USER->hasPerm('domains_delete') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('domains_delete_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true))) {
				$TBE_TEMPLATE->noPermissionMessage(true);
				lib_logging::addLogMessage('domains', 'delete', 'permission', 'logMsgNoPermission');
				$error = true;
				break;
			}
			$domainNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetDomainName($id);
		}
		if ($error) return;

		// Delete templates an records, and owner-domain relation
		// Order to delete: template records => templates
		// delete records:
		$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('records', 'domain_id IN ('. lib_DB::fullQuoteStrList($conf['data']['id']) .')');
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteDomainRecordsDbError'), 'mysqlError' => mysql_error())));
			lib_logging::addLogMessage('domains', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return ;
		}
		// delete domain:
		$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('domains', 'id IN ('. lib_DB::fullQuoteStrList($conf['data']['id']) .')');
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteDomainDbError'), 'mysqlError' => mysql_error())));
			lib_logging::addLogMessage('domains', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return ;
		}
		// delete owner-domain relation:
		$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('domain_owners', 'usr_id IN ('. lib_DB::fullQuoteStrList($conf['data']['id']) .')');
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteDomainRelationDbError'), 'mysqlError' => mysql_error())));
			lib_logging::addLogMessage('domains', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return ;
		}

		// add Log messages for all domains
		foreach ($domainNames as $domainName) {
			lib_logging::addLogMessage('domains', 'delete', 'info', 'logMsgDomainDelete', array('domainName' => $domainName));
		}

		if ($idCount <= 1) {
			$domainName = $domainNames[0];
			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteDomainSuccess', array('name' => $domainName)));
		} else {
			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteDomainsSuccess', array('count' => $idCount, 'name' => $domainName)));
		}
		$TBE_TEMPLATE->addMessage('', '', 'updateData("domains")');
	}



	/**
	 * Creates form to move domain.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function moveDomain($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;
		$id = $conf['data']['id'];
		$idCount = count(lib_div::trimExplode(',', $id));

		// Check permissions
		//if (!$USER->hasPerm('domains_move')) {
		//if (!($USER->hasPerm('users_show_group') && ($USER->hasPerm('domains_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('domains_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true)))) {
		if (!($USER->hasPerm('users_show_group') && $USER->hasPerm('domains_edit, domains_edit_group'))) {
			lib_logging::addLogMessage('domains', 'move', 'permission', 'logMsgNoPermission');
			return $TBE_TEMPLATE->noPermissionMessage(true);
		}

		$content = '';
		$hiddenFields = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		$domainName = $GLOBALS['TUPA_DB']->exec_SELECTgetDomainName($id);

		if ($idCount <= 1) {
			$content .= $TBE_TEMPLATE->header($LANG->getLang('moveDomainTitle', array('domainName'=>$domainName)));
		} else {
			$content .= $TBE_TEMPLATE->header($LANG->getLang('moveDomainsTitle', array('count'=>$idCount)));
		}

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'domains.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###DOMAIN_MOVE###');

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'domains\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		$markerArray['label_move_domains'] = $LANG->getLang('labelMoveDomain');

		if ($USER->hasPerm('users_admin')) {
			// Remove unneeded part from template
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###DOMAIN_MOVE_USER###', '');

			// Get list of groups
			$groupSelectOptions = $TBE_TEMPLATE->groupSelectOptions();

			// Get list of users
			//$groupUserOptions = '<option value="0">'. $LANG->getLang('selectGroupFirst') .'</option>';
			$groupUserOptions = $TBE_TEMPLATE->userSelectOptions();

			$markerArray['select_move_domains_grp'] = '<select name="move_domains_grp" class="'. STYLE_FIELD .'" onchange="getUsersOfGroup(this.form.name, \'move_domains\', this.value, true)" >' . $groupSelectOptions .'</select>';
			$markerArray['select_move_domains'] = '<select name="move_domains" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
		} else {
			// Remove unneeded part from template
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###DOMAIN_MOVE_ADMIN###', '');

			// Get list of users
			$groupUserOptions = $TBE_TEMPLATE->userSelectOptions($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser());

			$markerArray['select_move_domains'] = '<select name="move_domains" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
		}

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('moveDomainButtonMove') .'" class="'. STYLE_BUTTON .'" />';

		$hiddenFields .= '<input type="hidden" name="cmd" value="move" />';
		$hiddenFields .= '<input type="hidden" name="id" value="'. $id .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInDiv('move-form', $TBE_TEMPLATE->wrapInFormTags($subpart, 'processDomain(document.forms[0]);'));

		return $content;
	}



	/**
	 * Process the submitted domain data.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function processDomain($conf) {
		global $TBE_TEMPLATE, $TUPA_CONF_VARS, $USER, $LANG;

		$fd = $conf['formdata'];
		$dataArr = array();
		$error = '';
		$soa_serial = '0';

		switch ($fd['hidden']['cmd']) {
			case 'add':
				tupa_domains::insertDomainData($fd, $conf);
				break;

			case 'tadd':	// Insert multiple domains with template
				// Check permissions
				if (!($USER->hasPerm('domains_add, domains_add_group') && $USER->hasPerm('domains_add_with_template'))) {
					lib_logging::addLogMessage('domains', 'add', 'permission', 'logMsgNoPermission');
					$TBE_TEMPLATE->noPermissionMessage();
					break;
				}
				// Check if limit exceeded
				if (lib_div::limitExceeded($conf)) break;

				// Check domain_template value
				if (!lib_div::checkSelectValue($fd['data']['domain_template'])) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('domainTemplateError'));
					break;
				}

				// Split Domains into an array and check for non domains
				$domArr = explode("\n", $fd['data']['domains']);
				$regexDomain = '/'. $TUPA_CONF_VARS['REGEX']['domain'] .'/';

				// Check amount of domains
				$domArrCount = count($domArr);
				if ($domArrCount > $TUPA_CONF_VARS['DOMAINS']['maxTmplDomains']) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('domainListError', array('limit' => $TUPA_CONF_VARS['DOMAINS']['maxTmplDomains'])));
					break;
				}

				while (list(,$domain) = each($domArr)) {
					if (!preg_match($regexDomain, $domain)) {
						$error = $LANG->getLang('domainListError', array('limit' => $TUPA_CONF_VARS['DOMAINS']['maxTmplDomains']));
						break;
					}
				}
				if ($error) {
					$TBE_TEMPLATE->addMessage('error', $error);
					break;
				}

				$tmplRecordsArr = array();
				$tmplRecordsArr['hidden']['cmd'] = $fd['hidden']['cmd'];

				// Get template records
				$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('name, type, content, ttl, prio, tupasorting', 'template_records', 'tmpl_id='. lib_DB::fullQuoteStr($fd['data']['domain_template']), '', 'tupasorting', '');
				if (mysql_error()) {
					lib_logging::addLogMessage('domains', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				while ($row = mysql_fetch_assoc($res)) {
					// If it's an SOA-record set the serial
					if ($row['type'] == 'SOA') {
						$soa = explode(' ', $row['content']);
						$tmplRecordsArr['data']['soa_primary'] = $soa[0];
						$tmplRecordsArr['data']['soa_hostmaster'] = $soa[1];
						$tmplRecordsArr['hidden']['soa_serial'] = date("Ymd") .'00';
						$tmplRecordsArr['data']['soa_refresh'] = $soa[3];
						$tmplRecordsArr['data']['soa_retry'] = $soa[4];
						$tmplRecordsArr['data']['soa_expire'] = $soa[5];
						$tmplRecordsArr['data']['soa_ttl'] = $soa[6];
					} else {
						foreach ($row as $key => $value) {
							if ($key == 'tupasorting') {
								$tmplRecordsArr['hidden'][$key .'_'. $row['tupasorting']] = $row[$key];
							} else {
								$tmplRecordsArr['data'][$key .'_'. $row['tupasorting']] = $row[$key];
							}
						}
					}
				}
				$tmplRecordsArr['data']['owner'] = $fd['data']['owner'];
				//$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($tmplRecordsArr, true)));
				foreach ($domArr as $value) {
					$tmplRecordsArr['data']['name'] = $value;
					tupa_domains::insertDomainData($tmplRecordsArr, $conf);
				}
				// Clean example div
				$TBE_TEMPLATE->addMessage('', '', 'clear(\'div-domain-ex-records\')');
				break;

			case 'edit':
				$id = $fd['hidden']['id'];
				settype($id, 'integer');
				// Check permissions
				if (!($USER->hasPerm('domains_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('domains_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true))) {
					$TBE_TEMPLATE->noPermissionMessage();
					lib_logging::addLogMessage('domains', 'edit', 'permission', 'logMsgNoPermission');
					break;
				}

				// Clean the data a bit (Remove HTML/PHP tags, add slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				// Substitute owner ID and unset from array.
				$ownerId = isset($fd['data']['owner']) ? $fd['data']['owner'] : $_SESSION['uid'];
				unset($fd['data']['owner'], $fd['data']['owner_group']);

				// Check if the submitted domain already exists in database
				$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'domains', 'id<>'. lib_DB::fullQuoteStr($id) .' AND name='. lib_DB::fullQuoteStr($fd['data']['name'])));
				if (mysql_error()) {
					lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($res) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('domainDuplicateError', array('domainName' => stripslashes($fd['data']['name']))), '');
					break;
				}

				// Get the domain name from the submitted data and add type
				$domArr = array(
					'name' => $fd['data']['name'],
					'type' => $TUPA_CONF_VARS['DNS']['defaultDomainType'],
				);
				unset($fd['data']['name']);

				// Get the current SOA serial and raise it
				$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'records', 'domain_id='. lib_DB::fullQuoteStr($id) .' AND type=\'SOA\'', '', '', '');
				if (mysql_error()) {
					lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($GLOBALS['TUPA_DB']->sql_num_rows($res) == 1) {
					$srow = $GLOBALS['TUPA_DB']->sql_fetch_assoc($res);
					$soa = explode(' ', $srow['content']);
					$soa_serial = $soa['2'];
					$soa_id = $srow['id'];
					if (substr($soa_serial, 0, 8) == date("Ymd")) {
						$soa_serial++;
					} else {
						$soa_serial = date("Ymd") .'00';
					}
				} else {
					$soa_serial = date("Ymd") .'00';
					$soa_id = '';
				}

				// Set the SOA record in $dataArr
				$soaHostmaster = $TUPA_CONF_VARS['DNS']['defaultSoaHostmaster'];
				$dataArr['0']['name'] = '';
				$dataArr['0']['ttl'] = $fd['data']['soa_ttl'];
				$dataArr['0']['type'] = 'SOA';
				$dataArr['0']['domain_id'] = $id;
				$dataArr['0']['id'] = $soa_id;
				$dataArr['0']['content'] = $fd['data']['soa_primary'] .' '. $soaHostmaster .' '. $soa_serial .' '. $fd['data']['soa_refresh'] .' '. $fd['data']['soa_retry'] .' '. $fd['data']['soa_expire'] .' '. $fd['data']['soa_ttl'];
				// Unset SOA data
				unset($fd['data']['soa_primary'], $fd['data']['soa_refresh'], $fd['data']['soa_retry'], $fd['data']['soa_expire'], $fd['data']['soa_ttl']);

//				$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($fd, true)));
				$dataArr = lib_div::array_merge($dataArr, lib_div::parseRecordsToArray($fd, 'domain_id', $id));

//				$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($dataArr, true)));

				// Check records
				$error = lib_div::checkRecordFields($dataArr);
				if ($error) {
					$TBE_TEMPLATE->addMessage('error', $error);
					break;
				}

				/*if (count($dataArr) > '0') {
					// Check some important fields of records again (already checked on client side)
					$error = lib_div::checkRecordFields($dataArr);
					if ($error) {
						$TBE_TEMPLATE->addMessage('error', $error);
						break;
					}
				}*/

				// Update domain (no records) after everything was checked above
				$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('domains', 'id='. lib_DB::fullQuoteStr($id), $domArr);
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysql' => mysql_error())));
					lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					break;
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editDomainSuccess', array('domainName' => stripslashes($domArr['name']))));
				}

				// Update relation between domain and user if allowed by current user
				if ($USER->hasPerm('domains_edit_group') && $USER->hasPerm('users_show_group')) {
					$domUsrArr = array(
						'usr_id' => $USER->checkOwnerIdOfRecord('domains', $ownerId)
					);
					$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('domain_owners', 'dom_id='. lib_DB::fullQuoteStr($id), $domUsrArr);
					if (mysql_error()) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('editDomainRelationDbError'), 'mysqlError' => mysql_error())));
						lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					}
				}

				// Update (Insert) records if any
				if (count($dataArr) > 0) {
					$existingIds = array();

					while (list(, $dataRow) = each($dataArr)) {
						// Add domainname to field "name"
						$dataRow['name'] .= ($dataRow['name'] != '' ? '.' : '') . $domArr['name'];

						// Add new record if no id is submitted
						if ($dataRow['id'] == '') {
							$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('records', $dataRow);
							$currentId = $GLOBALS['TUPA_DB']->sql_insert_id();
						} else {
							// Else update record
							$currentId = $dataRow['id'];
							unset($dataRow['id']);
							$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('records', 'id='. lib_DB::fullQuoteStr($currentId) .' AND domain_id='. lib_DB::fullQuoteStr($id) , $dataRow);
						}
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('addDomainRecordsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}

						// Add current ID to array of existing records
						$existingIds[] = $currentId;
					}

					// Get list of ID's (records) of current domain, get values which were not inserted/updated and delete them
					$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'records', 'domain_id='. lib_DB::fullQuoteStr($id));
					$dbIds = array();
					while ($row = $GLOBALS['TUPA_DB']->sql_fetch_row($res)) {
						$dbIds[] = $row[0];
					}
					$deleteIds = array_diff($dbIds, $existingIds);

					$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('records', 'id IN ('. lib_DB::fullQuoteStrList(implode(',', $deleteIds)) .')');
					if (mysql_error()) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysql' => mysql_error())));
						lib_logging::addLogMessage('domains', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
						break;
					}

					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editDomainRecordsSuccess', array('domainName' => stripslashes($domArr['name']))));
					lib_logging::addLogMessage('domains', 'edit', 'info', 'logMsgDomainEdit', array('domainName' => $domArr['name']));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editDomainNoRecords'));
					lib_logging::addLogMessage('domains', 'edit', 'info', 'logMsgDomainEdit', array('domainName' => $domArr['name']));
				}
				break;

			case 'move':
				$ids = lib_div::trimExplode(',', $fd['hidden']['id']);
				$idCount = count($ids);
				$domainNames = array();
				$error = false;

				// Clean the data a bit (Remove HTML/PHP tags, add slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				if (lib_div::isNonIntInArray($fd['data'])) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('wrongData'));
					break;
				}

				if (lib_div::isset_value($fd, 'data=>move_domains')) {
					$moveToUser = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($fd['data']['move_domains']);
					$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($fd['data']['move_domains']));
				} else {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveMissingValuesError'));
					break;
				}

				// check permissions for every record
				foreach ($ids as $id) {
					settype($id, 'integer');
					// Check permissions
					if (!($USER->hasPerm('users_show_group') && $conf['csite'] != 'groups' && ($USER->hasPerm('domains_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('domains_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true)))) {
						$TBE_TEMPLATE->noPermissionMessage();
						lib_logging::addLogMessage('domains', 'move', 'permission', 'logMsgNoPermission');
						$error = true;
						break;
					}
					$domainNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetDomainName($id);
				}
				if ($error) break;

				// Change user-ID in domain_owners table
				$updateFields = array('usr_id'=>$fd['data']['move_domains']);
				$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('domain_owners', 'dom_id IN ('. lib_DB::fullQuoteStrList($fd['hidden']['id']) .')', $updateFields);
				if (mysql_error()) {
					if ($idCount <= 1) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveDomainDbError'), 'mysqlError' => mysql_error())));
					} else {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveDomainsDbError'), 'mysqlError' => mysql_error())));
					}
					lib_logging::addLogMessage('domains', 'move', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					break;
				}

				// add Log messages for all domains
				foreach ($domainNames as $domainName) {
					lib_logging::addLogMessage('domains', 'move', 'info', 'logMsgDomainMove', array('domainName' => $domainName, 'userName' => $moveToUser, 'groupName' => $moveToGroup));
				}

				if ($idCount <= 1) {
					$domainName = $domainNames[0];
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveDomainSuccess', array('domainName' => $domainName, 'userName' => $moveToUser, 'groupName' => $moveToGroup)));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveDomainsSuccess', array('count' => $idCount, 'userName' => $moveToUser, 'groupName' => $moveToGroup)));
				}
				$TBE_TEMPLATE->addMessage('', '', 'rmNode("form-table");');
				break;
		}

		// Debugging
//		$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($fd, true)));
//		$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($dataArr, true)));

		return $TBE_TEMPLATE->message;
	}



	/**
	 * Inserts domain data for a single domain.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function insertDomainData($fd, $conf) {
		global $TBE_TEMPLATE, $TUPA_CONF_VARS, $USER, $LANG;

		$dataArr = array();

		// Check permissions
		if (!$USER->hasPerm('domains_add, domains_add_group')) {
			lib_logging::addLogMessage('domains', 'add', 'permission', 'logMsgNoPermission');
			$TBE_TEMPLATE->noPermissionMessage(true);
			return;
		}
		// Check if limit exceeded
		if (lib_div::limitExceeded($conf)) return;

		// Substitute owner ID and unsert from array.
		$ownerId = isset($fd['data']['owner']) ? $fd['data']['owner'] : $_SESSION['uid'];
		unset($fd['data']['owner'], $fd['data']['owner_group']);

		// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
		lib_div::stripTagsOnArray($fd);
		lib_div::addSlashesOnArray($fd);

		// Check if the submitted domain already exists in database
		$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'domains', 'name='. lib_DB::fullQuoteStr($fd['data']['name'])));
		if (mysql_error()) {
			lib_logging::addLogMessage('domains', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
		}
		if ($res) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('domainDuplicateError', array('domainName' => stripslashes($fd['data']['name']))), '');
			return;
		}

		// Get the domain name from the submitted data and add domain to db
		$domArr = array(
			'name' => $fd['data']['name'],
			'type' => $TUPA_CONF_VARS['DNS']['defaultDomainType'],
		);
		unset($fd['data']['name']);

		// Set the SOA record in $dataArr
		$soaHostmaster = $TUPA_CONF_VARS['DNS']['defaultSoaHostmaster'];
		$dataArr['0']['name'] = '';
		$dataArr['0']['ttl'] = $fd['data']['soa_ttl'];
		$dataArr['0']['type'] = 'SOA';
		$dataArr['0']['content'] = $fd['data']['soa_primary'] .' '. $soaHostmaster .' '. $fd['hidden']['soa_serial'] .' '. $fd['data']['soa_refresh'] .' '. $fd['data']['soa_retry'] .' '. $fd['data']['soa_expire'] .' '. $fd['data']['soa_ttl'];
		// Unset SOA data
		unset($fd['data']['soa_primary'], $fd['data']['soa_refresh'], $fd['data']['soa_retry'], $fd['data']['soa_expire'], $fd['data']['soa_ttl']);

		// Check SOA record
		$error = lib_div::checkRecordFields($dataArr);
		if ($error) {
			$TBE_TEMPLATE->addMessage('error', $error);
			return;
		}

		//$TBE_TEMPLATE->addMessage('debug', nl2br(print_r(lib_div::parseRecordsToArray($fd), true)));
		$dataArr = lib_div::array_merge($dataArr, lib_div::parseRecordsToArray($fd));

		if (count($dataArr) > '0') {
			// Check some important fields of records again (already checked on client side)
			$error = lib_div::checkRecordFields($dataArr);
			if ($error) {
				$TBE_TEMPLATE->addMessage('error', $error);
				return;
			}
		}

		// Insert domain (no records) after everything was checked above
		$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('domains', $domArr);
		if (mysql_error()) {
			lib_logging::addLogMessage('domains', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
		}
		$lastInsertId = mysql_insert_id();
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('addDomainDbError'), 'mysqlError' => mysql_error())));
			return;
		} else {
			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addDomainSuccess', array('domainName' => stripslashes($domArr['name']))));
		}

		// Add Database relation between domain and user
		$domUsrArr = array(
			'dom_id' => $lastInsertId,
			'usr_id' => $USER->checkOwnerIdOfRecord('domains', $ownerId)
		);
		$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('domain_owners', $domUsrArr);
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('addDomainRelationDbError'), 'mysqlError' => mysql_error())));
			lib_logging::addLogMessage('domains', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return;
		}

		if (count($dataArr) > '0') {
			while (list(, $dataRow) = each($dataArr)) {
				// Add the lastInsertId
				$dataRow['domain_id'] = $lastInsertId;
				// Replace %DOMAIN% in content field (only used when adding with templates)
				$dataRow['content'] = str_replace('%DOMAIN%', $domArr['name'], $dataRow['content']);

				// Add domainname to field "name"
				$dataRow['name'] .= ($dataRow['name'] != '' ? '.' : '') . $domArr['name'];

				$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('records', $dataRow);
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('addDomainRecordsDbError'), 'mysqlError' => mysql_error())));
					lib_logging::addLogMessage('domains', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					return;
				}
			}
//			$TBE_TEMPLATE->addMessage('debug', print_r($fd['hidden']['cmd'], true));
			if (!mysql_error()) {
				if ($fd['hidden']['cmd'] == 'add') {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addDomainRecordsSuccess', array('domainName' => stripslashes($domArr['name']))), 'clearFieldValues(\'formdata\', \'name\'); removeAllRows(\'records-table\'); addRow(\'records-table\', \'0\', \'1\');');
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addDomainRecordsSuccess', array('domainName' => stripslashes($domArr['name']))), 'clearFieldValues(\'formdata\', \'domain_template,domains\')');
				}
				lib_logging::addLogMessage('domains', 'add', 'info', 'logMsgDomainAdd', array('domainName' => $domArr['name']));
			}
		} else {
			if ($fd['hidden']['cmd'] == 'add') {
				$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addDomainNoRecords'), 'clearFieldValues(\'formdata\', \'name\'); removeAllRows(\'records-table\'); addRow(\'records-table\', \'0\', \'1\');');
			} else {
				$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addDomainRecordsSuccess', array('domainName' => stripslashes($domArr['name']))), 'clearFieldValues(\'formdata\', \'domain_template,domains\')');
			}
			lib_logging::addLogMessage('domains', 'add', 'info', 'logMsgDomainAdd', array('domainName' => $domArr['name']));
		}
	}
}
?>