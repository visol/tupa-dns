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
 * Domain template functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_templates {

	/**
	 * Creates form to add or edit template record.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function addEditTemplate($conf) {
		global $TBE_TEMPLATE, $TUPA_CONF_VARS, $USER, $LANG;

		$content = '';
		$row = '';
		$selectedOwnerId = '';
		$hiddenFields = '';
		$markerArray = lib_div::setDefaultMarkerArray();
		$editFieldList = '';
		$soa = array();

		if (isset($conf['data']['id']) && $conf['data']['id']) {
			$id = $conf['data']['id'];
			settype($id, 'integer');

			// ID set, check edit permissions
			if (!($USER->hasPerm('templates_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('templates_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true))) {
				lib_logging::addLogMessage('templates', 'edit', 'permission', 'logMsgNoPermission');
				return $TBE_TEMPLATE->noPermissionMessage();
			}

			$content .= $TBE_TEMPLATE->header($LANG->getLang('editTemplateTitle'));
			$cmd = 'edit';
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'templates', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
			if (mysql_error()) {
				lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			$row = mysql_fetch_assoc($res);
			lib_div::stripSlashesOnArray($row);
			lib_div::htmlspecialcharOnArray($row);

			// Get owner ID of record
			$selectedOwnerId = $row['usr_id'];

			// Get the SOA record of the template
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'template_records', 'tmpl_id='. lib_DB::fullQuoteStr($row['id']) .' AND type=\'SOA\'', '', '', '');
			if (mysql_error()) {
				lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			if (mysql_num_rows($res) == '1') {
				$srow = mysql_fetch_assoc($res);
				$soa = explode(' ', $srow['content']);
			}

			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'template_records', 'tmpl_id='. lib_DB::fullQuoteStr($row['id']) .' AND type<>\'SOA\'', '', 'tupasorting', '');
			if (mysql_error()) {
				lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			$editFieldArr = array();
			while ($rrow = mysql_fetch_assoc($res)) {
				$keyValuePairs = array();
				foreach ($rrow as $key => $value) {
					if ($key == 'prio' && $value == '0') {
						$value = '';
					}
					$keyValuePairs[] = $key .'='. $value;
				}
				$editFieldArr[] = implode(',', $keyValuePairs);
			}
			$editFieldList = implode('|', $editFieldArr);

			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('editTemplateButtonChange') .'" class="'. STYLE_BUTTON .'" />';
		// else => add record
		} else {
			// Check permissions
			if (!$USER->hasPerm('templates_add, templates_add_group')) {
				lib_logging::addLogMessage('templates', 'add', 'permission', 'logMsgNoPermission');
				return  $TBE_TEMPLATE->noPermissionMessage();
			}
			$content .= $TBE_TEMPLATE->header($LANG->getLang('addTemplateTitle'));
			$cmd = 'add';
			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('addTemplateButtonAdd') .'" class="'. STYLE_BUTTON .'" />';
		}

		// Start table and set records table headers
		$markerArray['template_records_table'] = '
			<table cellpadding="0" cellspacing="0" border="0" id="records-table"><tbody><tr>
				<td>'. $LANG->getLang('labelHostname') .'</td>
				<td>&nbsp;</td>
				<td>'. $LANG->getLang('labelTTL') .'</td>
				<td>&nbsp;</td>
				<td>'. $LANG->getLang('labelType') .'</td>
				<td>'. $LANG->getLang('labelPrio') .'</td>
				<td>'. $LANG->getLang('labelContent') .'</td>
				<td colspan="5">&nbsp;</td>
			</tr></tbody></table>';

		// New template? add first row
		if ($cmd == 'add' OR ($cmd == 'edit' && !$editFieldList)) {
			$TBE_TEMPLATE->addMessage('', '', 'addRow(\'records-table\', \'0\', \'1\')');
		} elseif ($cmd == 'edit') {
			$TBE_TEMPLATE->addMessage('', '', 'addRowsToEdit(\'records-table\', \''. $editFieldList .'\')');
		}

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'templates\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'templates.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###TEMPLATE_ADD_EDIT###');

		// Template name
		$markerArray['label_templatename'] = $LANG->getLang('labelTemplatename');
		$markerArray['input_templatename'] = '<input type="text" name="name" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('templatenameError') .'" '. (lib_div::isset_value($row, 'name') ? 'value="'. $row['name'] .'"' : '') .' />'. $LANG->getHelp('helpTemplatesName');

		// Owner selection
		if ($USER->hasPerm('templates_'. $cmd .'_group') && $USER->hasPerm('users_show_group')) {
			$markerArray['label_template_owner'] = $LANG->getLang('labelTemplateOwner');
			$markerArray['select_template_owner'] = $TBE_TEMPLATE->ownerSelectBoxes('templates', $selectedOwnerId). $LANG->getHelp('helpTemplatesOwner');
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###TEMPLATE_OWNER###', '');
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

		$markerArray['template_records_subtitle'] = $LANG->getLang('templateRecordsSubtitle');

		$hiddenFields .= '<input type="hidden" name="cmd" value="'. $cmd .'" />';
		$cmd == 'edit' ? $hiddenFields .= '<input type="hidden" name="id" value="'. $id .'" />' : '';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInFormTags($subpart, 'processTemplate(document.forms[0]);');

		return $content;
	}


	/**
	 * Creates form to delete template.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function deleteTemplate($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;
		$ids = lib_div::trimExplode(',', $conf['data']['id']);
		$idCount = count($ids);
		$templateNames = array();
		$error = false;

		// check permissions for every record
		foreach ($ids as $id) {
			settype($id, 'integer');
			// Check permissions
			if (!($USER->hasPerm('templates_delete') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('templates_delete_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true))) {
				$TBE_TEMPLATE->noPermissionMessage(true);
				lib_logging::addLogMessage('templates', 'delete', 'permission', 'logMsgNoPermission');
				$error = true;
				break;
			}
			$templateNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetTemplateName($id);
		}
		if ($error) break;

		// Delete templates an records
		// Order to delete: template records => templates
		// delete records:
		$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('template_records', 'tmpl_id IN ('. lib_DB::fullQuoteStrList($conf['data']['id']) .')');
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteTemplateRecordsDbError'), 'mysqlError' => mysql_error())));
			lib_logging::addLogMessage('templates', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return ;
		}
		// delete templates:
		$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('templates', 'id IN ('. lib_DB::fullQuoteStrList($conf['data']['id']) .')');
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteTemplatesDbError'), 'mysqlError' => mysql_error())));
			lib_logging::addLogMessage('templates', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return ;
		}

		// add Log messages for all domains
		foreach ($templateNames as $templateName) {
			lib_logging::addLogMessage('domains', 'delete', 'info', 'logMsgDomainDelete', array('domainName' => $domainName));
		}

		if ($idCount <= 1) {
			$templateName = $templateNames[0];
			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteTemplateSuccess', array('name' => $templateName)));
		} else {
			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteTemplateSuccess', array('count' => $idCount, 'name' => $templateName)));
		}
		$TBE_TEMPLATE->addMessage('', '', 'updateData("templates")');
	}



	/**
	 * Creates form to move template.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function moveTemplate($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;
		$id = $conf['data']['id'];
		$idCount = count(lib_div::trimExplode(',', $id));

		// Check permissions
		//if (!$USER->hasPerm('domains_move')) {
		if (!($USER->hasPerm('users_show_group') && ($USER->hasPerm('templates_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('templateps_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true)))) {
			lib_logging::addLogMessage('templates', 'move', 'permission', 'logMsgNoPermission');
			return $TBE_TEMPLATE->noPermissionMessage(true);
		}

		$content = '';
		$hiddenFields = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		if ($idCount <= 1) {
			$templateName = $GLOBALS['TUPA_DB']->exec_SELECTgetTemplateName($id);
			$content .= $TBE_TEMPLATE->header($LANG->getLang('moveTemplateTitle', array('templateName'=>$templateName)));
		} else {
			$content .= $TBE_TEMPLATE->header($LANG->getLang('moveTemplatesTitle', array('count'=>$idCount)));
		}

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'templates.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###TEMPLATE_MOVE###');

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'templates\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		$markerArray['label_move_templates'] = $LANG->getLang('labelMoveTemplate');

		if ($USER->hasPerm('users_admin')) {
			// Remove unneeded part from template
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###TEMPLATE_MOVE_USER###', '');

			// Get list of groups
			$groupSelectOptions = $TBE_TEMPLATE->groupSelectOptions();

			// Get list of users
			$groupUserOptions = $TBE_TEMPLATE->userSelectOptions();

			$markerArray['select_move_templates_grp'] = '<select name="move_templates_grp" class="'. STYLE_FIELD .'" onchange="getUsersOfGroup(this.form.name, \'move_templates\', this.value, true)" >' . $groupSelectOptions .'</select>';
			$markerArray['select_move_templates'] = '<select name="move_templates" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
		} else {
			// Remove unneeded part from template
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###TEMPLATE_MOVE_ADMIN###', '');

			// Get list of users
			$groupUserOptions = $TBE_TEMPLATE->userSelectOptions($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser());

			$markerArray['select_move_templates'] = '<select name="move_templates" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
		}

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('moveTemplateButtonMove') .'" class="'. STYLE_BUTTON .'" />';

		$hiddenFields .= '<input type="hidden" name="cmd" value="move" />';
		$hiddenFields .= '<input type="hidden" name="id" value="'. $id .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInDiv('move-form', $TBE_TEMPLATE->wrapInFormTags($subpart, 'processTemplate(document.forms[0]);'));

		return $content;
	}



	/**
	 * Process the submitted template data.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function processTemplate($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		$fd = $conf['formdata'];
		$dataArr = array();
		$error = '';
		$soa_serial = '0';

		switch ($fd['hidden']['cmd']) {
			case 'add':
				// Check permissions
				if (!$USER->hasPerm('templates_add, templates_add_group')) {
					$TBE_TEMPLATE->noPermissionMessage();
					lib_logging::addLogMessage('templates', 'add', 'permission', 'logMsgNoPermission');
					break;
				}
				// Check if limit exceeded
				if (lib_div::limitExceeded($conf)) break;

				// Substitute owner ID and unsert from array.
				$ownerId = isset($fd['data']['owner']) ? $fd['data']['owner'] : $_SESSION['uid'];
				unset($fd['data']['owner'], $fd['data']['owner_group']);

				// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				// Check if there is already an identical templatename in users group
				$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('templates.*, users.grp_id', 'templates, users', 'templates.usr_id=users.id AND users.grp_id='. lib_DB::fullQuoteStr($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid'])) .' AND templates.name='. lib_DB::fullQuoteStr($fd['data']['name'])));
				if (mysql_error()) {
					lib_logging::addLogMessage('templates', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($res) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('templatenameDuplicateError', array('templateName' => stripslashes($fd['data']['name']))), '');
					break;
				}

				// Get the template name from the submitted data and add template
				$tmplArr = array(
					'name' => $fd['data']['name'],
					'usr_id' => $USER->checkOwnerIdOfRecord('templates', $ownerId)
				);
				// Unset name
				unset($fd['data']['name']);

				// Set the SOA record in $dataArr
				$soaHostmaster = $TUPA_CONF_VARS['DNS']['defaultSoaHostmaster'];
				$dataArr['0']['ttl'] = $fd['data']['soa_ttl'];
				$dataArr['0']['type'] = 'SOA';
				$dataArr['0']['content'] = $fd['data']['soa_primary'] .' '. $soaHostmaster .' '. $soa_serial .' '. $fd['data']['soa_refresh'] .' '. $fd['data']['soa_retry'] .' '. $fd['data']['soa_expire'] .' '. $fd['data']['soa_ttl'];
				// Unset SOA data
				unset($fd['data']['soa_primary'], $fd['data']['soa_refresh'], $fd['data']['soa_retry'], $fd['data']['soa_expire'], $fd['data']['soa_ttl']);

				// Check SOA record
				$error = lib_div::checkRecordFields($dataArr);
				if ($error) {
					$TBE_TEMPLATE->addMessage('error', $error);
					break;
				}

				$dataArr = lib_div::array_merge($dataArr, lib_div::parseRecordsToArray($fd));

				if (count($dataArr) > '0') {
					// Check some important fields of records again (already checked on client side)
					$error = lib_div::checkRecordFields($dataArr);
					if ($error) {
						$TBE_TEMPLATE->addMessage('error', $error);
						break;
					}
				}

				// Insert template (no records) after everything was checked above
				$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('templates', $tmplArr);
				if (mysql_error()) {
					lib_logging::addLogMessage('templates', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				$lastInsertId = mysql_insert_id();
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('addTemplateDbError'), 'mysqlError' => mysql_error())));
					break;
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addTemplateSuccess', array('templateName' => stripslashes($tmplArr['name']))));
				}

				// Insert records if any
				if (count($dataArr) > '0') {
					while (list(, $dataRow) = each($dataArr)) {
						// Add the lastInsertId
						$dataRow['tmpl_id'] = $lastInsertId;
						$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('template_records', $dataRow);
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('addTemplateRecordsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('templates', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
					}
					if (!mysql_error()) {
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addTemplateRecordsSuccess', array('templateName' => stripslashes($tmplArr['name']))), 'clearFieldValues(\'formdata\', \'name\'); removeAllRows(\'records-table\'); addRow(\'records-table\', \'0\', \'1\');');
						lib_logging::addLogMessage('templates', 'add', 'info', 'logMsgTemplateAdd', array('templateName' => $tmplArr['name']));
					}
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addTemplateNoRecords'), 'clearFieldValues(\'formdata\', \'name\'); removeAllRows(\'records-table\'); addRow(\'records-table\', \'0\', \'1\');');
					lib_logging::addLogMessage('templates', 'add', 'info', 'logMsgTemplateAdd', array('templateName' => $tmplArr['name']));
				}

				break;

			case 'edit':
				$id = $fd['hidden']['id'];
				settype($id, 'integer');

				// Check permissions
				if (!($USER->hasPerm('templates_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('templates_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true))) {
					$TBE_TEMPLATE->noPermissionMessage();
					lib_logging::addLogMessage('templates', 'edit', 'permission', 'logMsgNoPermission');
					break;
				}

				// Substitute owner ID and unsert from array.
				$ownerId = isset($fd['data']['owner']) ? $fd['data']['owner'] : $_SESSION['uid'];
				unset($fd['data']['owner'], $fd['data']['owner_group']);

				// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				// Check if there is already an identical templatename in users group
				$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('templates.*, users.grp_id', 'templates, users', 'templates.usr_id=users.id AND templates.id<>'. lib_DB::fullQuoteStr($id) .' AND users.grp_id='. lib_DB::fullQuoteStr($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid'])) .' AND templates.name='. lib_DB::fullQuoteStr($fd['data']['name'])));
				if (mysql_error()) {
					lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($res) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('templatenameDuplicateError', array('templateName' => stripslashes($fd['data']['name']))), '');
					break;
				}

				// Get the template name from the submitted data and add template
				$tmplArr = array(
					'name' => $fd['data']['name'],
					'usr_id' => $USER->checkOwnerIdOfRecord('templates', $ownerId)
				);
				unset($fd['data']['name']);
				if ($USER->hasPerm('templates_edit_group') && $USER->hasPerm('users_show_group')) {
					$tmplArr['usr_id'] = $USER->checkOwnerIdOfRecord('templates', $ownerId);
				}

				// Get the current SOA
				$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'template_records', 'tmpl_id='. lib_DB::fullQuoteStr($id) .' AND type=\'SOA\'', '', '', '');
				if (mysql_error()) {
					lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($GLOBALS['TUPA_DB']->sql_num_rows($res) == 1) {
					$srow = $GLOBALS['TUPA_DB']->sql_fetch_assoc($res);
					$soa_id = $srow['id'];
				} else {
					$soa_id = '';
				}

				// Set the SOA record in $dataArr
				$soaHostmaster = $TUPA_CONF_VARS['DNS']['defaultSoaHostmaster'];
				$dataArr['0']['ttl'] = $fd['data']['soa_ttl'];
				$dataArr['0']['type'] = 'SOA';
				$dataArr['0']['tmpl_id'] = $id;
				$dataArr['0']['id'] = $soa_id;
				$dataArr['0']['content'] = $fd['data']['soa_primary'] .' '. $soaHostmaster .' '. $soa_serial .' '. $fd['data']['soa_refresh'] .' '. $fd['data']['soa_retry'] .' '. $fd['data']['soa_expire'] .' '. $fd['data']['soa_ttl'];
				// Unset SOA data
				unset($fd['data']['soa_primary'], $fd['data']['soa_refresh'], $fd['data']['soa_retry'], $fd['data']['soa_expire'], $fd['data']['soa_ttl']);

				// Check SOA record
				$error = lib_div::checkRecordFields($dataArr);
				if ($error) {
					$TBE_TEMPLATE->addMessage('error', $error);
					break;
				}

				$dataArr = lib_div::array_merge($dataArr, lib_div::parseRecordsToArray($fd, 'tmpl_id', $id));

				if (count($dataArr) > 0) {
					// Check some important fields of records again (already checked on client side)
					$error = lib_div::checkRecordFields($dataArr);
					if ($error) {
						$TBE_TEMPLATE->addMessage('error', $error);
						break;
					}
				}

				// Update template (no records) after everything was checked above
				$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('templates', 'id='. lib_DB::fullQuoteStr($id), $tmplArr);
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysql' => mysql_error())));
					lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					break;
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editTemplateSuccess', array('templateName' => stripslashes($tmplArr['name']))));
				}

				// Update (Insert) records if any
				if (count($dataArr) > 0) {
					$existingIds = array();

					while (list(, $dataRow) = each($dataArr)) {
						// Add new record if no id is submitted
						if ($dataRow['id'] == '') {
							$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('template_records', $dataRow);
							$currentId = $GLOBALS['TUPA_DB']->sql_insert_id();
						} else {
							// Else update record
							$currentId = $dataRow['id'];
							unset($dataRow['id']);
							$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('template_records', 'id='. lib_DB::fullQuoteStr($currentId) .' AND tmpl_id='. lib_DB::fullQuoteStr($id) , $dataRow);
						}
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('addTemplateRecordsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}

						// Add current ID to array of existing records
						$existingIds[] = $currentId;
					}

					// Get list of ID's (records) of current domain, get values which were not inserted/updated and delete them
					$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'template_records', 'tmpl_id='. lib_DB::fullQuoteStr($id));
					$dbIds = array();
					while ($row = $GLOBALS['TUPA_DB']->sql_fetch_row($res)) {
						$dbIds[] = $row[0];
					}
					$deleteIds = array_diff($dbIds, $existingIds);

					$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('template_records', 'id IN ('. lib_DB::fullQuoteStrList(implode(',', $deleteIds)) .')');
					if (mysql_error()) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysql' => mysql_error())));
						lib_logging::addLogMessage('templates', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
						break;
					}

					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editTemplateRecordsSuccess', array('templateName' => stripslashes($tmplArr['name']))));
					lib_logging::addLogMessage('templates', 'edit', 'info', 'logMsgTemplateEdit', array('templateName' => $tmplArr['name']));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editTemplateNoRecords'));
					lib_logging::addLogMessage('templates', 'edit', 'info', 'logMsgTemplateEdit', array('templateName' => $tmplArr['name']));
				}
				break;

			case 'move':
				$ids = lib_div::trimExplode(',', $fd['hidden']['id']);
				$idCount = count($ids);
				$templateNames = array();
				$error = false;

				// Clean the data a bit (Remove HTML/PHP tags, add slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				if (lib_div::isNonIntInArray($fd['data'])) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('wrongData'));
					break;
				}

				if (lib_div::isset_value($fd, 'data=>move_templates')) {
					$moveToUser = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($fd['data']['move_templates']);
					$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($fd['data']['move_templates']));
				} else {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveMissingValuesError'));
					break;
				}

				// Check permissions
				foreach ($ids as $id) {
					settype($id, 'integer');
					if (!($USER->hasPerm('users_show_group') && $conf['csite'] != 'groups' && ($USER->hasPerm('templates_edit') && $USER->isOwnerOfRecord($id, $conf['csite']) || $USER->hasPerm('templates_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true)))) {
						$TBE_TEMPLATE->noPermissionMessage();
						lib_logging::addLogMessage('templates', 'move', 'permission', 'logMsgNoPermission');
						$error = true;
						break;
					}
					$templateNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetTemplateName($id);
				}
				if ($error) break;

				$updateFields = array('usr_id'=>$fd['data']['move_templates']);
				$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('templates', 'id IN ('. lib_DB::fullQuoteStrList($fd['hidden']['id']) .')', $updateFields);
				if (mysql_error()) {
					if ($idCount <= 1) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveTemplateDbError'), 'mysqlError' => mysql_error())));
					} else {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveTemplatesDbError'), 'mysqlError' => mysql_error())));
					}
					lib_logging::addLogMessage('domains', 'move', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					break;
				}

				// add Log messages for all templates
				foreach ($templateNames as $templateName) {
					lib_logging::addLogMessage('domains', 'move', 'info', 'logMsgTemplateMove', array('templateName' => $templateName, 'userName' => $moveToUser, 'groupName' => $moveToGroup));
				}

				if ($idCount <= 1) {
					$templateName = $templateNames[0];
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveTemplateSuccess', array('templateName' => $templateName, 'userName' => $moveToUser, 'groupName' => $moveToGroup)));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveTemplatesSuccess', array('count' => $idCount, 'userName' => $moveToUser, 'groupName' => $moveToGroup)));
				}
				$TBE_TEMPLATE->addMessage('', '', 'rmNode("form-table");');
				break;
		}

		// Debugging
//		$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($fd, true)));
//		$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($dataArr, true)));

		return $TBE_TEMPLATE->message;
	}
}
?>