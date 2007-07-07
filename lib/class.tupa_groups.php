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
 * Everithing needed for create, update, delete groups
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_groups {

	/**
	 * Creates form to add or edit group record.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function addEditGroup($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		$content = '';
		$row = '';
		$prefArr = '';
		$hiddenFields = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		// Check permissions
		// When an id is submitted => edit record
		if (isset($conf['data']['id']) && $conf['data']['id']) {
			// ID set, check edit permissions
			if (!$USER->hasPerm('groups_edit')) {
				lib_logging::addLogMessage('groups', 'edit', 'permission', 'logMsgNoPermission');
				return  $TBE_TEMPLATE->noPermissionMessage();
			}
			$content .= $TBE_TEMPLATE->header($LANG->getLang('editGroupTitle'));
			$cmd = 'update';
			$id = $conf['data']['id'];
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'groups', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
			if (mysql_error()) {
				lib_logging::addLogMessage('groups', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			$row = mysql_fetch_assoc($res);
			$prefArr = unserialize($row['preferences']);
			lib_div::stripSlashesOnArray($row);
			lib_div::htmlspecialcharOnArray($row);

			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('editGroupButtonChange') .'" class="'. STYLE_BUTTON .'" />';
		// else => add record
		} else {
			// Check permissions
			if (!$USER->hasPerm('groups_add')) {
				lib_logging::addLogMessage('groups', 'edit', 'permission', 'logMsgNoPermission');
				return  $TBE_TEMPLATE->noPermissionMessage();
			}
			$content .= $TBE_TEMPLATE->header($LANG->getLang('addGroupTitle'));
			$cmd = 'insert';
			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('addGroupButtonAdd') .'" class="'. STYLE_BUTTON .'" />';
		}

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'groups\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'groups.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###GROUP_ADD_EDIT###');

		$markerArray['label_groupname'] = $LANG->getLang('labelGroupname');
		$markerArray['input_groupname'] = '<input type="text" name="name" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('groupnameError') .'" '. (lib_div::isset_value($row, 'name') ? 'value="'. $row['name'] .'"' : '') .' />'. $LANG->getHelp('helpGroupsName');
		if ($TUPA_CONF_VARS['DNS']['allowSoaPrimaryChange'] == true) {
			$markerArray['label_soa_primary'] = $LANG->getLang('labelSoaPrimary');
			$markerArray['input_soa_primary'] = '<input type="checkbox" name="temp_soa_primary_chk" value="1" onchange="toggleFields(this, \'pref_DNS_soaPrimary|1|\', \'pref_DNS_soaPrimary|0|\', \'group_config\');" '. (lib_div::isset_value($prefArr, 'DNS=>soaPrimary') ? 'checked' : '') .' />&nbsp;<input type="text" name="pref_DNS_soaPrimary" class="'. STYLE_FIELD .'" size="30" alt="custom|bok" pattern="'. $TUPA_CONF_VARS['REGEX']['domain'] .'" emsg="'. $LANG->getLang('soaPrimaryError') .'" '. (lib_div::isset_value($prefArr, 'DNS=>soaPrimary') ? 'value="'. $prefArr['DNS']['soaPrimary'] .'"' : 'disabled="disabled"') .'" />'. $LANG->getHelp('helpSoaPrimaryG');
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###PREFS_SOA_PRIMARY###', '');
		}
		if ($TUPA_CONF_VARS['DNS']['allowSoaHostmasterChange'] == true) {
			$markerArray['label_soa_hostmaster'] = $LANG->getLang('labelSoaHostmaster');
			$markerArray['input_soa_hostmaster'] = '<input type="checkbox" name="temp_soa_hostmaster_chk" value="1" onchange="toggleFields(this, \'pref_DNS_soaHostmaster|1|\', \'pref_DNS_soaHostmaster|0|\', \'group_config\');" '. (lib_div::isset_value($prefArr, 'DNS=>soaHostmaster') ? 'checked' : '') .' />&nbsp;<input type="text" name="pref_DNS_soaHostmaster" class="'. STYLE_FIELD .'" size="30" alt="email|bok" emsg="'. $LANG->getLang('soaHostmasterError') .'" '. (lib_div::isset_value($prefArr, 'DNS=>soaHostmaster') ? 'value="'. $prefArr['DNS']['soaHostmaster'] .'"' : 'disabled="disabled"') .'" />'. $LANG->getHelp('helpPrefsDefaultSoaHostmaster');
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###PREFS_SOA_HOSTMASTER###', '');
		}
		$markerArray['label_notice'] = $LANG->getLang('labelNotice');
		$markerArray['textarea_notice'] = '<textarea name="notice" class="'. STYLE_FIELD .'" rows="6" cols="40" wrap="on">'. (lib_div::isset_value($row, 'notice') ? $row['notice'] : '') .'</textarea>';

		$markerArray['limits_subtitle'] = $LANG->getLang('groupLimitsSubtitle');
		$markerArray['label_max_users'] = $LANG->getLang('labelMaxUsers');
		$markerArray['input_max_users'] = '<input type="text" name="max_users" class="'. STYLE_FIELD .'" size="5" alt="number|0|0|" emsg="'. $LANG->getLang('maxGroupUsersError') .'" value="'. (lib_div::isset_value($row, 'max_users') ? $row['max_users'] : '0') .'" />'. $LANG->getHelp('helpGroupsMaxUsers');
		$markerArray['label_max_domains'] = $LANG->getLang('labelMaxDomains');
		$markerArray['input_max_domains'] = '<input type="text" name="max_domains" class="'. STYLE_FIELD .'" size="5" alt="number|0|0|" emsg="'. $LANG->getLang('maxGroupDomainsError') .'" value="'. (lib_div::isset_value($row, 'max_domains') ? $row['max_domains'] : '0') .'" />'. $LANG->getHelp('helpGroupsMaxDomains');
		$markerArray['label_max_templates'] = $LANG->getLang('labelMaxTemplates');
		$markerArray['input_max_templates'] = '<input type="text" name="max_templates" class="'. STYLE_FIELD .'" size="5" alt="number|0|0|" emsg="'. $LANG->getLang('maxGroupTemplatesError') .'" value="'. (lib_div::isset_value($row, 'max_templates') ? $row['max_templates'] : '0') .'" />'. $LANG->getHelp('helpGroupsMaxTemplates');

		$hiddenFields .= '<input type="hidden" name="cmd" value="'. $cmd .'" />';
		$cmd == 'update' ? $hiddenFields .= '<input type="hidden" name="id" value="'. $id .'" />' : '';
		$markerArray['hidden_fields'] = $hiddenFields;

				// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInFormTags($subpart, 'processGroup(document.forms[0]);', 'group_config');

		return $content;
	}



	/**
	 * Generates form to delete group.
	 * Checks if users, templates or domains still exists and gives options to delete them or move them to an other group/user.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function deleteGroup($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;
		$ids = lib_div::trimExplode(',', $conf['data']['id']);
		$idCount = count($ids);
		$groupNames = array();
		$error = false;

		foreach ($ids as $id) {
			settype($id, 'integer');
			// Check permissions
			if (!$USER->hasPerm('groups_delete') || $id == $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']) || $id == '1') {
				lib_logging::addLogMessage('groups', 'delete', 'permission', 'logMsgNoPermission');
				return $TBE_TEMPLATE->noPermissionMessage(true);
			}
			$groupNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($id);
		}
		if ($error) return;

		$content = '';
		$hiddenFields = '';
		$delAllFieldsArr = array();
		$markerArray = lib_div::setDefaultMarkerArray();

		if ($idCount <= 1) {
			$groupName = $groupNames[0];
			$content .= $TBE_TEMPLATE->header($LANG->getLang('deleteGroupTitle', array('groupName' => $groupName)));
		} else {
			$content .= $TBE_TEMPLATE->header($LANG->getLang('deleteGroupsTitle', array('count' => $idCount)));
		}

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'groups.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###GROUP_DELETE###');

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'groups\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get list of users in group
		$userIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'users', 'grp_id', $conf['data']['id']);

		// No users => no templates/domains
		if ($userIdList['count'] > '0') {

			// Get list of templates
			$templateIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'templates', 'usr_id', $userIdList['list']);

			// Get list of domains
			$domainIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('dom_id', 'domain_owners', 'usr_id', $userIdList['list']);

			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###GROUP_DELETE_DIRECT###', '');
		} else {
			$templateIdList['count'] = '';
			$domainIdList['count'] = '';
			$markerArray['no_utd'] = $LANG->getLang('deleteNoUTD');
		}

		// Get list of groups
		$groupSelectOptions = $TBE_TEMPLATE->groupSelectOptions('', $conf['data']['id']);

		// Get list of users
		$groupUserOptions = '<option value="0">'. $LANG->getLang('selectGroupFirst') .'</option>';

		// Users in group?
		if ($userIdList['count'] > '0') {
			$delAllFieldsArr[] = 'delete_users';
			$markerArray['users_subtitle'] = $LANG->getLang('deleteUsersSubtitle') .' ('. $userIdList['count'] .'):';
			$markerArray['label_delete_users'] = $LANG->getLang('labelDeleteUsers');
			$markerArray['input_delete_users'] = '<input type="checkbox" name="delete_users" value="1" onchange="toggleFields(this, \'move_users_grp|0|0\', \'move_users_grp|1|0\');" />';
			$markerArray['label_move_users'] = $LANG->getLang('labelMoveUsers');
			$markerArray['select_move_users_grp'] = '<select name="move_users_grp" class="'. STYLE_FIELD .'">' . $groupSelectOptions .'</select>';
			$hiddenFields .= '<input type="hidden" name="chkusr" alt="checkbselect|,|delete_users|move_users_grp" emsg="'. $LANG->getLang('moveDeleteUsersError') .'" />';
		} else {
			// remove the part from the content
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###GROUP_DELETE_USERS###', '');
		}

		// Any user templates?
		if ($templateIdList['count'] > '0') {
			$delAllFieldsArr[] = 'delete_templates';
			$markerArray['templates_subtitle'] = $LANG->getLang('deleteTemplatesSubtitle') .' ('. $templateIdList['count'] .'):';
			$markerArray['label_delete_templates'] = $LANG->getLang('labelDeleteTemplates');
			$markerArray['input_delete_templates'] = '<input type="checkbox" name="delete_templates" value="1" onchange="toggleFields(this, \'move_templates_grp|0|0,move_templates|0|0\', \'move_templates_grp|1|0,move_templates|1|0\');" />';
			$markerArray['label_move_templates'] = $LANG->getLang('labelMoveTemplates');
			$markerArray['select_move_templates_grp'] = '<select name="move_templates_grp" class="'. STYLE_FIELD .'" onchange="getUsersOfGroup(this.form.name, \'move_templates\', this.value)" >' . $groupSelectOptions .'</select>';
			$markerArray['select_move_templates'] = '<select name="move_templates" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
			$hiddenFields .= '<input type="hidden" name="chktmpl" alt="checkbselect|,|delete_templates|move_templates_grp,move_templates" emsg="'. $LANG->getLang('moveDeleteTemplatesError') .'" />';
		} else {
			// remove the part from the content
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###GROUP_DELETE_TEMPLATES###', '');
		}

		// Any domains?
		if ($domainIdList['count'] > '0') {
			$delAllFieldsArr[] = 'delete_domains';
			$markerArray['domains_subtitle'] = $LANG->getLang('deleteDomainsSubtitle') .' ('. $domainIdList['count'] .'):';
			$markerArray['label_delete_domains'] = $LANG->getLang('labelDeleteDomains');
			$markerArray['input_delete_domains'] = '<input type="checkbox" name="delete_domains" value="1" onchange="toggleFields(this, \'move_domains_grp|0|0,move_domains|0|0\', \'move_domains_grp|1|0,move_domains|1|0\');" />';
			$markerArray['label_move_domains'] = $LANG->getLang('labelMoveDomains');
			$markerArray['select_move_domains_grp'] = '<select name="move_domains_grp" class="'. STYLE_FIELD .'" onchange="getUsersOfGroup(this.form.name, \'move_domains\', this.value)" >' . $groupSelectOptions .'</select>';
			$markerArray['select_move_domains'] = '<select name="move_domains" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
			$hiddenFields .= '<input type="hidden" name="chkdom" alt="checkbselect|,|delete_domains|move_domains_grp,move_domains" emsg="'. $LANG->getLang('moveDeleteDomainsError') .'" />';
		} else {
			// remove the part from the content
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###GROUP_DELETE_DOMAINS###', '');
		}

		// Add "Mark all to delete link"
		if (count($delAllFieldsArr) > '1') {
			$check = '';
			$uncheck = '';
			foreach ($delAllFieldsArr as $value) {
				$check ? $check .= ',' : '';
				$check .= $value .'|1|1';
				$uncheck ? $uncheck .= ',' : '';
				$uncheck .= $value .'|1|0';
			}
			$markerArray['delete_all_links'] = '<a href="javascript:void(0);" onclick="toggleFields(this, \''. $check .'\');">'. $LANG->getLang('markAllToDelete') .'</a>&nbsp;|&nbsp;<a href="javascript:void(0);" onclick="toggleFields(this, \''. $uncheck .'\');">'. $LANG->getLang('unMarkAllFromDelete') .'</a>';
		} else {
			// remove the part from the content
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###GROUP_DELETE_ALL###', '');
		}

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('deleteGroupButtonDelete') .'" class="'. STYLE_BUTTON .'" />';

		$hiddenFields .= '<input type="hidden" name="cmd" value="delete" />';
		$hiddenFields .= '<input type="hidden" name="id" value="'. $conf['data']['id'] .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInDiv('delete-form', $TBE_TEMPLATE->wrapInFormTags($subpart, 'processGroup(document.forms[0]);'));

		return $content;
	}



	/**
	 * Processes the submitted data from the forms.
	 *
	 * @param	array		Configuration array
	 * @return	string		messages
	 */
	function processGroup($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;
		$fd = $conf['formdata'];
		$dataArr = array();

		switch ($fd['hidden']['cmd']) {
			case 'insert':
				// Check permissions
				if (!$USER->hasPerm('groups_add')) {
					$TBE_TEMPLATE->noPermissionMessage();
					lib_logging::addLogMessage('groups', 'add', 'permission', 'logMsgNoPermission');
					break;
				}

				// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				// Check if there is already an identical group
				$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'groups', 'name='. lib_DB::fullQuoteStr($fd['data']['name'])));
				if (mysql_error()) {
					lib_logging::addLogMessage('groups', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}

				if ($res) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('groupnameDuplicateError', array('groupName' => stripslashes($fd['data']['name']))));
					break;
				}

				$dataArr = lib_div::splitSpecialPartsFromFormdata($fd['data'], 'groups');
				if (!$dataArr) break;

				$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('groups', $dataArr);

				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
					lib_logging::addLogMessage('groups', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addGroupSuccess', array('groupName' => stripslashes($fd['data']['name']))), 'clearFieldValues(\'formdata\', \'name,notice\'); clearFieldValues(\'formdata\', \'max_users,max_domains,max_templates\', true)');
					lib_logging::addLogMessage('groups', 'add', 'info', 'logMsgGroupAdd', array('groupName' => $fd['data']['name']));
				}
				break;

			case 'update':
				$id = $fd['hidden']['id'];
				settype($id, 'integer');

				// Check permissions
				if (!$USER->hasPerm('groups_edit')) {
					$TBE_TEMPLATE->noPermissionMessage();
					lib_logging::addLogMessage('groups', 'edit', 'permission', 'logMsgNoPermission');
					break;
				}

				// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				// Check if there is already an identical group
				$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'groups', 'name='. lib_DB::fullQuoteStr($fd['data']['name']) .' AND id<>'. lib_DB::fullQuoteStr($id)));
				if (mysql_error()) {
					lib_logging::addLogMessage('groups', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($res) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('groupnameDuplicateError', array('groupName' => stripslashes($fd['data']['name']))));
					break;
				}

				$dataArr = lib_div::splitSpecialPartsFromFormdata($fd['data'], 'groups', $id);
				if (!$dataArr) break;

				$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('groups', 'id='. lib_DB::fullQuoteStr($id), $dataArr);
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
					lib_logging::addLogMessage('groups', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				} else {
					// now activating the changes made if the current logged in user is in updated group
					if ($id == $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid'])) {
						$USER->loadGroupPreferences($id);
					}

					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editGroupSuccess', array('groupName' => stripslashes($fd['data']['name']))));
					lib_logging::addLogMessage('groups', 'edit', 'info', 'logMsgGroupEdit', array('groupName' => $fd['data']['name']));
				}
				break;

			case 'delete':
				$ids = lib_div::trimExplode(',', $fd['hidden']['id']);
				$idCount = count($ids);
				$groupNames = array();
				$error = false;

				// Clean the data a bit (Remove HTML/PHP tags, add slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				if (lib_div::isNonIntInArray($fd['data'])) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('wrongData'));
					break;
				}

				// Check permissions
				foreach ($ids as $id) {
					settype($id, 'integer');
					if (!$USER->hasPerm('groups_delete') || $id == $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']) || $id == '1') {
						$TBE_TEMPLATE->noPermissionMessage();
						lib_logging::addLogMessage('groups', 'delete', 'permission', 'logMsgNoPermission');
						break;
					}
					$groupNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($id);
				}
				if ($error) break;

				// Get list of users in group(s)
				$userIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'users', 'grp_id', lib_DB::fullQuoteStrList($fd['hidden']['id']));

				// 1st delete or move domains
				// Only valid if all "domain" fields ar submitted
				if (lib_div::isset_value($fd, 'data=>delete_domains', false) && lib_div::isset_value($fd, 'data=>move_domains_grp', false) && lib_div::isset_value($fd, 'data=>move_domains', false)) {
					// Make sure not both was submitted (move and delete) - Can't be possible if JS works fine...
					if ($fd['data']['delete_domains'] && ($fd['data']['move_domains_grp'] || $fd['data']['move_domains'])) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveAndDeleteDomainsError'));
						break;
					}
					// Delete domains an records
					// Get list of domain id's
					$domainIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('dom_id', 'domain_owners', 'usr_id', $userIdList['list']);
					if ($fd['data']['delete_domains']) {
						// Order to delete: domain records => domains => domain/owner relation
						// delete records:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('records', 'domain_id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteDomainRecordsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						// delete domains:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('domains', 'id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteDomainsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						// delete relations
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('domain_owners', 'dom_id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('message' => $LANG->getLang('deleteDomainRelationsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteDomainsSuccess', array('count' => $domainIdList['count'])), 'rmNode("form-domains")');
					}
					if ($fd['data']['move_domains_grp'] && $fd['data']['move_domains']) {
						// Change user-ID in domain_owners table
						$updateFields = array('usr_id'=>$fd['data']['move_domains']);
						$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('domain_owners', 'dom_id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')', $updateFields);
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveDomainsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$moveToUser = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($fd['data']['move_domains']);
						$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($fd['data']['move_domains_grp']);
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveDomainsSuccess', array('count' => $domainIdList['count'], 'userName' => $moveToUser, 'groupName' => $moveToGroup)), 'rmNode("form-domains")');
					}
				} elseif (lib_div::isset_value($fd, 'data=>delete_templates', false) || lib_div::isset_value($fd, 'data=>move_templates_grp', false) || lib_div::isset_value($fd, 'data=>move_templates', false)) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('deleteMissingValuesError'));
					break;
				}

				// 2nd delete or move templates
				// Only valid if all "template" fields ar submitted
				if (lib_div::isset_value($fd, 'data=>delete_templates', false) && lib_div::isset_value($fd, 'data=>move_templates_grp', false) && lib_div::isset_value($fd, 'data=>move_templates', false)) {
					// Make sure not both was submitted (move and delete) - Can't be possible if JS works fine...
					if ($fd['data']['delete_templates'] && ($fd['data']['move_templates_grp'] || $fd['data']['move_templates'])) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveAndDeleteTemplatesError'));
						break;
					}
					// Delete templates an records
					// Get list of template id's
					$templateIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'templates', 'usr_id', $userIdList['list']);
					if ($fd['data']['delete_templates']) {
						// Order to delete: template records => templates
						// delete records:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('template_records', 'tmpl_id IN ('. lib_DB::fullQuoteStrList($templateIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteTemplateRecordsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						// delete templates:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('templates', 'id IN ('. lib_DB::fullQuoteStrList($templateIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteTemplatesDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteTemplatesSuccess', array('count' => $templateIdList['count'])), 'rmNode("form-templates")');
					}
					if ($fd['data']['move_templates_grp'] && $fd['data']['move_templates']) {
						// Change user-ID in templates table
						$updateFields = array('usr_id'=>$fd['data']['move_templates']);
						$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('templates', 'id IN ('. lib_DB::fullQuoteStrList($templateIdList['list']) .')', $updateFields);
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveTemplatesDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$moveToUser = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($fd['data']['move_templates']);
						$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($fd['data']['move_templates_grp']);
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveTemplatesSuccess', array('count' => $templateIdList['count'], 'userName' => $moveToUser, 'groupName' => $moveToGroup)), 'rmNode("form-templates")');
					}
				} elseif (lib_div::isset_value($fd, 'data=>delete_templates', false) || lib_div::isset_value($fd, 'data=>move_templates_grp', false) || lib_div::isset_value($fd, 'data=>move_templates', false)) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('deleteMissingValuesError'));
					break;
				}

				// 3rd delete or move users
				// Only valid if all "user" fields ar submitted
				if (lib_div::isset_value($fd, 'data=>delete_users', false) && lib_div::isset_value($fd, 'data=>move_users_grp', false)) {
					// Make sure not both was submitted (move and delete) - Can't be possible if JS works fine...
					if ($fd['data']['delete_users'] && $fd['data']['move_users_grp']) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveAndDeleteUsersError'));
						break;
					}
					// Delete users
					if ($fd['data']['delete_users']) {
						// delete users:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('users', 'id IN ('. lib_DB::fullQuoteStrList($userIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteUsersDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteUsersSuccess', array('count' => $userIdList['count'])), 'rmNode("form-users")');
					}
					if ($fd['data']['move_users_grp']) {
						// Change group-ID in users table
						$updateFields = array('grp_id'=>$fd['data']['move_users_grp']);
						$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('users', 'id IN ('. lib_DB::fullQuoteStrList($userIdList['list']) .')', $updateFields);
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveUsersDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($fd['data']['move_users_grp']);
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveUsersSuccess', array('count' => $userIdList['count'], 'groupName' => $moveToGroup)), 'rmNode("form-users")');
					}
				} elseif (lib_div::isset_value($fd, 'data=>delete_users', false) || lib_div::isset_value($fd, 'data=>move_users_grp', false)) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('deleteMissingValuesError'));
					break;
				}

				// 4th delete group
				$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('groups', 'id IN ('. lib_DB::fullQuoteStrList($fd['hidden']['id']) .')');
				if (mysql_error()) {
					if ($idCount <= 1) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteGroupDbError'), 'mysqlError' => mysql_error())));
					} else {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteGroupsDbError'), 'mysqlError' => mysql_error())));
					}
					lib_logging::addLogMessage('groups', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					break;
				}

				// add Log messages for all users
				foreach ($groupNames as $groupName) {
					lib_logging::addLogMessage('groups', 'delete', 'info', 'logMsgGroupDelete', array('groupName' => $groupName));
				}

				if ($idCount <= 1) {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteGroupSuccess', array('groupName' => $groupName)));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteGroupsSuccess', array('count' => $idCount)));
				}
				$TBE_TEMPLATE->addMessage('', '', 'rmNode("delete-form");');
				break;
		}

		return  $TBE_TEMPLATE->message;
	}



}

?>