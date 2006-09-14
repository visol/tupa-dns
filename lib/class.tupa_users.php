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
 * User functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_users {

	/**
	 * Creates form to add or edit user record.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function addEditUser($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		$content = '';
		$row = '';
		$userPerms = '';
		$userPrefs = '';
		$hiddenFields = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		// Check permissions
		// When an id is submitted => edit record
		if (isset($conf['data']['id']) && $conf['data']['id']) {
			$id = $conf['data']['id'];
			settype($id, 'integer');

			// ID set, check edit permissions
			if (!($USER->hasPerm('users_edit_group') && $USER->isOwnerOfRecord($id, 'users', true)) || $id == $_SESSION['uid']) {
				lib_logging::addLogMessage('users', 'edit', 'permission', 'logMsgNoPermission');
				return $TBE_TEMPLATE->noPermissionMessage();
			}
			$cmd = 'update';
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'users', 'id='. lib_DB::fullQuoteStr($id), '', '', '1');
			if (mysql_error()) {
				lib_logging::addLogMessage('users', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			$row = mysql_fetch_assoc($res);
			lib_div::stripSlashesOnArray($row);

			$content .= $TBE_TEMPLATE->header($LANG->getLang('editUserTitle', array('userName' => $row['username'])));

			$userPerms = unserialize($row['permissions']);
			$userPrefs = unserialize($row['preferences']);
			//$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($userPerms, true)));

			// Get the group limits of edited user
			$groupId = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($id);
			$domainGroupLimit = $GLOBALS['TUPA_DB']->exec_SELECTgetLimitOfGroup($groupId, 'max_domains');
			$templateGroupLimit = $GLOBALS['TUPA_DB']->exec_SELECTgetLimitOfGroup($groupId, 'max_templates');

			lib_div::htmlspecialcharOnArray($row);

			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('editUserButtonChange') .'" class="'. STYLE_BUTTON .'" />';
		// else => add record
		} else {
			// Check permissions
			if (!$USER->hasPerm('users_add')) {
				lib_logging::addLogMessage('users', 'add', 'permission', 'logMsgNoPermission');
				return  $TBE_TEMPLATE->noPermissionMessage();
			}
			$content .= $TBE_TEMPLATE->header($LANG->getLang('addUserTitle'));
			$cmd = 'insert';

			// Setting the limits to 0 (unlimited) because we dont know in wich group te user will be.
			$domainGroupLimit = 0;
			$templateGroupLimit = 0;

			$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('addUserButtonAdd') .'" class="'. STYLE_BUTTON .'" />';
		}

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'users\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get list of groups
		$groupSelectOptionsSelected = lib_div::isset_value($row, 'grp_id') ? $row['grp_id']  : '';
		$groupSelectOptions = $TBE_TEMPLATE->groupSelectOptions($groupSelectOptionsSelected);

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'users.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###USER_ADD_EDIT###');

		// Personal data
		$markerArray['personal_subtitle'] = $LANG->getLang('userPersonalSubtitle');
		$markerArray['label_username'] = $LANG->getLang('labelUsername');
		$markerArray['input_username'] = '<input type="text" name="username" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('usernameError') .'" '. (lib_div::isset_value($row, 'username') ? 'value="'. $row['username'] .'"' : '') .' />';
		$markerArray['label_password'] = $LANG->getLang('labelPassword');
		$markerArray['input_password'] = '<input type="password" name="password" class="'. STYLE_FIELD .'" size="20" alt="length|7'. ($cmd == 'update' ? '|bok' : '') .'" emsg="'. $LANG->getLang('passwordError') .'" />'. $LANG->getHelp('helpUsersPassword');
		$markerArray['label_cpassword'] = $LANG->getLang('labelConfirmPassword');
		$markerArray['input_cpassword'] = '<input type="password" name="cpassword" class="'. STYLE_FIELD .'" size="20" alt="equalto|password'. ($cmd == 'update' ? '|bok' : '') .'" emsg="'. $LANG->getLang('confirmPasswordError') .'" />';
		if ($USER->hasPerm('users_admin')) {
			$markerArray['label_group'] = $LANG->getLang('labelGroup');
			$markerArray['select_group'] = '<select name="grp_id" class="'. STYLE_FIELD .'" alt="select" emsg="'. $LANG->getLang('groupError') .'">' . $groupSelectOptions .'</select>';
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###USER_ADD_EDIT_GROUP###', '');
			$hiddenFields .= '<input type="hidden" name="grp_id" value="'. $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']) .'" />';
		}
		$markerArray['label_name'] = $LANG->getLang('labelName');
		$markerArray['input_name'] = '<input type="text" name="name" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('nameError') .'" '. (lib_div::isset_value($row, 'name') ? 'value="'. $row['name'] .'"' : '') .' />';
		$markerArray['label_firstname'] = $LANG->getLang('labelFirstname');
		$markerArray['input_firstname'] = '<input type="text" name="firstname" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('firstnameError') .'" '. (lib_div::isset_value($row, 'firstname') ? 'value="'. $row['firstname'] .'"' : '') .' />';
		$markerArray['label_email'] = $LANG->getLang('labelEmail');
		$markerArray['input_email'] = '<input type="text" name="email" class="'. STYLE_FIELD .'" size="30" alt="email|1" emsg="'. $LANG->getLang('emailError') .'" '. (lib_div::isset_value($row, 'email') ? 'value="'. $row['email'] .'"' : '') .' />';
		$markerArray['label_notice'] = $LANG->getLang('labelNotice');
		$markerArray['textarea_notice'] = '<textarea name="notice" class="'. STYLE_FIELD .'" rows="6" cols="40" wrap="on">'. (lib_div::isset_value($row, 'notice') ? $row['notice'] : '') .'</textarea>';

		// Limits
		$markerArray['limits_subtitle'] = $LANG->getLang('userLimitsSubtitle');
		$markerArray['label_max_domains'] = $LANG->getLang('labelMaxDomains');
		$markerArray['input_max_domains'] = '<input type="text" name="max_domains" class="'. STYLE_FIELD .'" size="5" alt="number|0|0|'. ($domainGroupLimit > 0 ? $domainGroupLimit : '') .'" emsg="'. $LANG->getLang('maxDomainsError', array('groupLimit'=>$domainGroupLimit)) .'" value="'. (lib_div::isset_value($row, 'max_domains') ? $row['max_domains'] : '0') .'" />'. $LANG->getHelp('helpUsersMaxDomains');
		$markerArray['label_max_templates'] = $LANG->getLang('labelMaxTemplates');
		$markerArray['input_max_templates'] = '<input type="text" name="max_templates" class="'. STYLE_FIELD .'" size="5" alt="number|0|0|'. ($templateGroupLimit > 0 ? $templateGroupLimit : '') .'" emsg="'. $LANG->getLang('maxTemplatesError', array('groupLimit'=>$templateGroupLimit)) .'" value="'. (lib_div::isset_value($row, 'max_templates') ? $row['max_templates'] : '0') .'" />'. $LANG->getHelp('helpUsersMaxTemplates');

		// Admin permissions
		if ($USER->hasPerm('users_admin')) {
			$markerArray['perm_admin_subtitle'] = $LANG->getLang('userPermissionsAdminSubtitle');
			if ($USER->hasPerm('admin')) {		// Admin only
				$markerArray['label_perm_admin'] = $LANG->getLang('labelPermAdmin');
				$markerArray['input_perm_admin'] = '<input type="checkbox" name="perm_ADM_admin" value="1" '. (lib_div::isset_value($userPerms, 'ADM=>admin') && $userPerms['ADM']['admin'] == '1' ? 'checked' : '') .' onchange="toggleFields(this, \'TAB_prefsLogging_MENU|1,perm_ADM_user|0|0,perm_ADM_domain|0|0,perm_USR_all|0|0,perm_USR_show|0|0,perm_USR_add|0|0,perm_USR_edit|0|0,perm_USR_delete|0|0,perm_DOM_all|0|0,perm_DOM_show|0|0,perm_DOM_add|0|0,perm_DOM_edit|0|0,perm_DOM_delete|0|0,perm_DOMGROUP_all|0|0,perm_DOMGROUP_show|0|0,perm_DOMGROUP_add|0|0,perm_DOMGROUP_edit|0|0,perm_DOMGROUP_delete|0|0,perm_TMPL_all|0|0,perm_TMPL_show|0|0,perm_TMPL_add|0|0,perm_TMPL_edit|0|0,perm_TMPL_delete|0|0,perm_TMPLGROUP_all|0|0,perm_TMPLGROUP_show|0|0,perm_TMPLGROUP_add|0|0,perm_TMPLGROUP_edit|0|0,perm_TMPLGROUP_delete|0|0\', \'TAB_prefsLogging_MENU|0,perm_ADM_user|1|0,perm_ADM_domain|1|0,perm_USR_all|1|0,perm_USR_show|1|0,perm_USR_add|1|0,perm_USR_edit|1|0,perm_USR_delete|1|0,perm_DOM_all|1|0,perm_DOM_show|1|0,perm_DOM_add|1|0,perm_DOM_edit|1|0,perm_DOM_delete|1|0,perm_DOMGROUP_all|1|0,perm_DOMGROUP_show|1|0,perm_DOMGROUP_add|1|0,perm_DOMGROUP_edit|1|0,perm_DOMGROUP_delete|1|0,perm_TMPL_all|1|0,perm_TMPL_show|1|0,perm_TMPL_add|1|0,perm_TMPL_edit|1|0,perm_TMPL_delete|1|0,perm_TMPLGROUP_all|1|0,perm_TMPLGROUP_show|1|0,perm_TMPLGROUP_add|1|0,perm_TMPLGROUP_edit|1|0,perm_TMPLGROUP_delete|1|0\');" />'. $LANG->getHelp('helpUsersPermAdmin');
			} else {
				$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###PERM_ADMIN###', '');
			}
			$markerArray['label_perm_admin_user'] = $LANG->getLang('labelPermAdminUser');
			$markerArray['input_perm_admin_user'] = '<input type="checkbox" name="perm_ADM_user" value="1" '. (lib_div::isset_value($userPerms, 'ADM=>user') && $userPerms['ADM']['user'] == '1' ? 'checked' : '') .' onchange="toggleFields(this, \'perm_USR_all|0|0,perm_USR_show|0|0,perm_USR_add|0|0,perm_USR_edit|0|0,perm_USR_delete|0|0\', \'perm_USR_all|1|0,perm_USR_show|1|0,perm_USR_add|1|0,perm_USR_edit|1|0,perm_USR_delete|1|0\');" />'. $LANG->getHelp('helpUsersPermUserAdmin');
			$markerArray['label_perm_admin_domain'] = $LANG->getLang('labelPermAdminDomain');
			$markerArray['input_perm_admin_domain'] = '<input type="checkbox" name="perm_ADM_domain" value="1" '. (lib_div::isset_value($userPerms, 'ADM=>domain') && $userPerms['ADM']['domain'] == '1' ? 'checked' : '') .' onchange="toggleFields(this, \'perm_DOM_all|0|0,perm_DOM_show|0|0,perm_DOM_add|0|0,perm_DOM_edit|0|0,perm_DOM_delete|0|0,perm_DOMGROUP_all|0|0,perm_DOMGROUP_show|0|0,perm_DOMGROUP_add|0|0,perm_DOMGROUP_edit|0|0,perm_DOMGROUP_delete|0|0,perm_TMPL_all|0|0,perm_TMPL_show|0|0,perm_TMPL_add|0|0,perm_TMPL_edit|0|0,perm_TMPL_delete|0|0,perm_TMPLGROUP_all|0|0,perm_TMPLGROUP_show|0|0,perm_TMPLGROUP_add|0|0,perm_TMPLGROUP_edit|0|0,perm_TMPLGROUP_delete|0|0\', \'perm_DOM_all|1|0,perm_DOM_show|1|0,perm_DOM_add|1|0,perm_DOM_edit|1|0,perm_DOM_delete|1|0,perm_DOMGROUP_all|1|0,perm_DOMGROUP_show|1|0,perm_DOMGROUP_add|1|0,perm_DOMGROUP_edit|1|0,perm_DOMGROUP_delete|1|0,perm_TMPL_all|1|0,perm_TMPL_show|1|0,perm_TMPL_add|1|0,perm_TMPL_edit|1|0,perm_TMPL_delete|1|0,perm_TMPLGROUP_all|1|0,perm_TMPLGROUP_show|1|0,perm_TMPLGROUP_add|1|0,perm_TMPLGROUP_edit|1|0,perm_TMPLGROUP_delete|1|0\');" />'. $LANG->getHelp('helpUsersPermDomainAdmin');
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###PERM_ADMIN_GROUP###', '');
		}

		// Check if logging tab is enabled or disabled
		$TBE_TEMPLATE->addMessage('', '', 'toggleFields(d.formdata.perm_ADM_admin, \'TAB_prefsLogging_MENU|1\', \'TAB_prefsLogging_MENU|0\');');

		// General
		$markerArray['label_perm_all'] = $LANG->getLang('labelPermAll');
		$markerArray['label_perm_show'] = $LANG->getLang('labelPermShow');
		$markerArray['label_perm_add'] = $LANG->getLang('labelPermAdd');
		$markerArray['label_perm_edit'] = $LANG->getLang('labelPermEdit');
		$markerArray['label_perm_delete'] = $LANG->getLang('labelPermDelete');

		// User group permissions
		$markerArray['perm_usr_subtitle'] = $LANG->getLang('userPermissionsUserSubtitle'). $LANG->getHelp('helpUsersPermGroupUsers');
		$markerArray = $TBE_TEMPLATE->genPermissionCheckboxes($markerArray, 'usr', $userPerms);

		// Domain permissions
		$markerArray['perm_dom_subtitle'] = $LANG->getLang('userPermissionsDomainSubtitle'). $LANG->getHelp('helpUsersPermDomains');
		$markerArray['perm_dom_group_subtitle'] = $LANG->getLang('userPermissionsDomainGroupSubtitle'). $LANG->getHelp('helpUsersPermGroupDomains');
		$markerArray = $TBE_TEMPLATE->genPermissionCheckboxes($markerArray, 'dom', $userPerms);
		$markerArray = $TBE_TEMPLATE->genPermissionCheckboxes($markerArray, 'domgroup', $userPerms);

		// Template Permissions
		$markerArray['perm_tmpl_subtitle'] = $LANG->getLang('userPermissionsTemplatesSubtitle'). $LANG->getHelp('helpUsersPermTemplates');
		$markerArray['perm_tmpl_group_subtitle'] = $LANG->getLang('userPermissionsTemplatesGroupSubtitle'). $LANG->getHelp('helpUsersPermGroupTemplates');
		$markerArray = $TBE_TEMPLATE->genPermissionCheckboxes($markerArray, 'tmpl', $userPerms);
		$markerArray = $TBE_TEMPLATE->genPermissionCheckboxes($markerArray, 'tmplgroup', $userPerms);

		$hiddenFields .= '<input type="hidden" name="cmd" value="'. $cmd .'" />';
		$cmd == 'update' ? $hiddenFields .= '<input type="hidden" name="id" value="'. $id .'" />
			<input type="hidden" name="valpassfields" alt="allornone|,|password,cpassword" "emsg="'. $LANG->getLang('passwordFieldsError') .'" />' : '';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');


		// Add user preferences
		// Get the preferences template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'preferences.html'));
		// Get needed template subparts
		$subpartPref = $TBE_TEMPLATE->getSubpart($templateFileContent, '###PREFS_GENERAL###');
		//if (lib_div::isset_value($userPerms, 'ADM=>admin') && $userPerms['ADM']['admin']) {
		if ($USER->hasPerm('admin')) {
			$subpartPref .= $TBE_TEMPLATE->getSubpart($templateFileContent, '###PREFS_LOGGING###');
		}
		$langSelectOptions = $TBE_TEMPLATE->languageSelectOptions(lib_div::isset_value($userPrefs, 'SYS=>language') ? $userPrefs['SYS']['language'] : $TUPA_CONF_VARS['SYS']['langDefault']);
		$skinSelectOptions = $TBE_TEMPLATE->skinSelectOptions(lib_div::isset_value($userPrefs, 'SKINS=>skin') ? $userPrefs['SKINS']['skin'] : $TUPA_CONF_VARS['SKINS']['skinDefault']);
		$startPageSelectOptions = $TBE_TEMPLATE->startPageSelectOptions(lib_div::isset_value($userPrefs, 'PREFS=>startPage') ? $userPrefs['PREFS']['startPage'] : '');
		$displayHelpOptions = $TBE_TEMPLATE->displayHelpSelectOptions(lib_div::isset_value($userPrefs, 'PREFS=>displayHelp') ? $userPrefs['PREFS']['displayHelp'] : $TUPA_CONF_VARS['PREFS']['defDisplayHelp']);

		$markerArray = lib_div::setDefaultMarkerArray();
		$markerArray['prefs_general_subtitle'] = $LANG->getLang('prefsGeneralSubtitle');
		$markerArray['label_language'] = $LANG->getLang('labelLanguage');
		$markerArray['select_language'] = '<select name="pref_SYS_language" class="'. STYLE_FIELD .'" alt="select" emsg="'. $LANG->getLang('languageError') .'">' . $langSelectOptions .'</select>';
		$markerArray['label_start_page'] = $LANG->getLang('labelStartPage');
		$markerArray['select_start_page'] = '<select name="pref_PREFS_startPage" class="'. STYLE_FIELD .'">' . $startPageSelectOptions .'</select>'. $LANG->getHelp('helpPrefsStartPage');
		$markerArray['label_help_display'] = $LANG->getLang('labelHelpDisplay');
		$markerArray['select_help_display'] = '<select name="pref_PREFS_displayHelp" class="'. STYLE_FIELD .'">'. $displayHelpOptions .'</select>'. $LANG->getHelp('helpPrefsDisplayHelp');
		$markerArray['label_skin'] = $LANG->getLang('labelSkin');
		$markerArray['select_skin'] = '<select name="pref_SKINS_skin" class="'. STYLE_FIELD .'">' . $skinSelectOptions .'</select>'. $LANG->getHelp('helpPrefsSkin');
		$markerArray['label_disable_tabs'] = $LANG->getLang('labelDisableTabs');
		$markerArray['input_disable_tabs'] = '<input type="checkbox" name="pref_PREFS_disableTabs" value="1" '. (lib_div::isset_value($userPrefs, 'PREFS=>disableTabs') && $userPrefs['PREFS']['disableTabs'] ? 'checked' : '') .' />'. $LANG->getHelp('helpPrefsDisableTabs');
		$markerArray['label_rows_per_site'] = $LANG->getLang('labelRowsPerSite');
		$markerArray['input_rows_per_site'] = '<input type="text" name="pref_PREFS_linesPerSite" class="'. STYLE_FIELD .'" size="5" alt="number|0|'. $TUPA_CONF_VARS['PREFS']['minLinesPerSite'] .'|'. $TUPA_CONF_VARS['PREFS']['maxLinesPerSite'] .'" emsg="'. $LANG->getLang('rowsPerSiteError', array('min'=>$TUPA_CONF_VARS['PREFS']['minLinesPerSite'], 'max'=>$TUPA_CONF_VARS['PREFS']['maxLinesPerSite'])) .'" value="'. (lib_div::isset_value($userPrefs, 'PREFS=>linesPerSite') ? $userPrefs['PREFS']['linesPerSite'] : $TUPA_CONF_VARS['PREFS']['defLinesPerSite']) .'" />'. $LANG->getHelp('helpPrefsLinesPerSite');
		$markerArray['label_navi_show_pages'] = $LANG->getLang('labelNaviShowPages');
		$markerArray['input_navi_show_pages'] = '<input type="text" name="pref_PREFS_naviShowPages" class="'. STYLE_FIELD .'" size="5" alt="number|0|'. $TUPA_CONF_VARS['PREFS']['minNaviShowPages'] .'|'. $TUPA_CONF_VARS['PREFS']['maxNaviShowPages'] .'" emsg="'. $LANG->getLang('naviShowPagesError', array('min'=>$TUPA_CONF_VARS['PREFS']['minNaviShowPages'], 'max'=>$TUPA_CONF_VARS['PREFS']['maxNaviShowPages'])) .'" value="'. (lib_div::isset_value($userPrefs, 'PREFS=>naviShowPages') ? $userPrefs['PREFS']['naviShowPages'] : $TUPA_CONF_VARS['PREFS']['defNaviShowPages']) .'" />'. $LANG->getHelp('helpPrefsNaviShowPages');
		$markerArray['label_soa_primary'] = $LANG->getLang('labelSoaPrimary');
		$markerArray['input_soa_primary'] = '<input type="text" name="pref_DNS_defaultSoaPrimary" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('soaPrimaryError') .'" value="'. (lib_div::isset_value($userPrefs, 'DNS=>defaultSoaPrimary') ? $userPrefs['DNS']['defaultSoaPrimary'] : $TUPA_CONF_VARS['DNS']['defaultSoaPrimary']) .'" />'. $LANG->getHelp('helpPrefsDefaultSoaPimary');

		$maxSelectOptions = $TBE_TEMPLATE->logMaxSelectOptions(lib_div::isset_value($userPrefs, 'LOGGING=>itemAmount') ? $userPrefs['LOGGING']['itemAmount'] : $TUPA_CONF_VARS['LOGGING']['defItemAmount']);

		$markerArray['prefs_logging_subtitle'] = $LANG->getLang('prefsLoggingSubtitle');
		$markerArray['label_logging_max'] = $LANG->getLang('labelLoggingMax');
		$markerArray['select_logging_max'] = '<select name="pref_LOGGING_itemAmount" class="'. STYLE_FIELD .'">' . $maxSelectOptions .'</select>'. $LANG->getHelp('helpPrefsItemAmount');

		// Substitute markers
		$subpartPref = $TBE_TEMPLATE->substituteMarkerArray($subpartPref, $markerArray, '###|###', '1');

		// put them together
		$subpart = $TBE_TEMPLATE->substituteMarker($subpart, '###USER_PREFERENCES###', $subpartPref);

		$content .= $TBE_TEMPLATE->wrapInFormTags($subpart, 'cryptPassFields(\'password,cpassword\'); processUser(document.forms[0]);');

		return $content;
	}




	/**
	 * Generates form to delete user.
	 * Checks if templates or domains still exists and gives options to delete them or move them to an other user.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function deleteUser($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;
		$ids = lib_div::trimExplode(',', $conf['data']['id']);
		$idCount = count($ids);
		$userNames = array();
		$error = false;

		foreach ($ids as $id) {
			settype($id, 'integer');
			// Check permissions
			if (!($USER->hasPerm('users_delete_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true)) || $id == $_SESSION['uid']  || $id == '1') {
				$TBE_TEMPLATE->noPermissionMessage(true);
				lib_logging::addLogMessage('users', 'delete', 'permission', 'logMsgNoPermission');
				$error = true;
				break;
			}
			$userNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($id);
		}
		if ($error) return;

		$content = '';
		$hiddenFields = '';
		$delAllFieldsArr = array();
		$markerArray = lib_div::setDefaultMarkerArray();

		if ($idCount <= 1) {
			$userName = $userNames[0];
			$content .= $TBE_TEMPLATE->header($LANG->getLang('deleteUserTitle', array('userName' => $userName)));
		} else {
			$content .= $TBE_TEMPLATE->header($LANG->getLang('deleteUsersTitle', array('count' => $idCount)));
		}

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'users.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###USER_DELETE###');

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'users\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get list of templates
		$templateIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'templates', 'usr_id', $conf['data']['id']);

		// Get list of domains
		$domainIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('dom_id', 'domain_owners', 'usr_id', $conf['data']['id']);

		if ($templateIdList['count'] > '0' OR $domainIdList['count'] > '0') {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###USER_DELETE_DIRECT###', '');
		} else {
			$markerArray['no_td'] = $LANG->getLang('deleteNoTD');
		}

		if ($USER->hasPerm('users_admin')) {
			// Get list of users
			$groupUserOptions = '<option value="0">'. $LANG->getLang('selectGroupFirst') .'</option>';
			$groupSelectOptions = $TBE_TEMPLATE->groupSelectOptions('');
		} else {
			$groupUserOptions = $TBE_TEMPLATE->userSelectOptions($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']), '', $conf['data']['id']);
		}

		// Any user templates?
		if ($templateIdList['count'] > '0') {
			$delAllFieldsArr[] = 'delete_templates';
			$markerArray['templates_subtitle'] = $LANG->getLang('deleteTemplatesSubtitle') .' ('. $templateIdList['count'] .'):';
			$markerArray['label_delete_templates'] = $LANG->getLang('labelDeleteTemplate'. ($templateIdList['count'] > '1' ? 's' : ''));
			$markerArray['input_delete_templates'] = '<input type="checkbox" name="delete_templates" value="1" onchange="toggleFields(this, \'move_templates_grp|0|0,move_templates|0|0\', \'move_templates_grp|1|0,move_templates|1|0\');" />';
			if ($USER->hasPerm('users_admin')) {
				$markerArray['label_move_templates'] = $LANG->getLang('labelMoveTemplate'. ($templateIdList['count'] > '1' ? 's' : ''));
				$markerArray['label_move_templates2'] = '&nbsp';
				$markerArray['select_move_templates_grp'] = '<select name="move_templates_grp" class="'. STYLE_FIELD .'" onchange="getUsersOfGroup(this.form.name, \'move_templates\', this.value, \'\', \''. $conf['data']['id'] .'\')" >' . $groupSelectOptions .'</select>';
				$hiddenFields .= '<input type="hidden" name="chktmpl" alt="checkbselect|,|delete_templates|move_templates_grp,move_templates" emsg="'. $LANG->getLang('moveDeleteTemplatesError') .'" />';
			} else {
				$markerArray['label_move_templates2'] = $LANG->getLang('labelMoveTemplate'. ($templateIdList['count'] > '1' ? 's' : ''));
				$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###USER_MOVE_TEMPLATES_GROUP###', '');
				$hiddenFields .= '<input type="hidden" name="chktmpl" alt="checkbselect|,|delete_templates|move_templates" emsg="'. $LANG->getLang('moveDeleteTemplatesError') .'" />';
			}
			$markerArray['select_move_templates'] = '<select name="move_templates" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
		} else {
			// remove the part from the content
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###USER_DELETE_TEMPLATES###', '');
		}

		// Any domains?
		if ($domainIdList['count'] > '0') {
			$delAllFieldsArr[] = 'delete_domains';
			$markerArray['domains_subtitle'] = $LANG->getLang('deleteDomainsSubtitle') .' ('. $domainIdList['count'] .'):';
			$markerArray['label_delete_domains'] = $LANG->getLang('labelDeleteDomain'. ($domainIdList['count'] > '1' ? 's' : ''));
			$markerArray['input_delete_domains'] = '<input type="checkbox" name="delete_domains" value="1" onchange="toggleFields(this, \'move_domains_grp|0|0,move_domains|0|0\', \'move_domains_grp|1|0,move_domains|1|0\');" />';
			if ($USER->hasPerm('users_admin')) {
				$markerArray['label_move_domains'] = $LANG->getLang('labelMoveDomain'. ($domainIdList['count'] > '1' ? 's' : ''));
				$markerArray['label_move_domains2'] = '&nbsp';
				$markerArray['select_move_domains_grp'] = '<select name="move_domains_grp" class="'. STYLE_FIELD .'" onchange="getUsersOfGroup(this.form.name, \'move_domains\', this.value, \'\', \''. $conf['data']['id'] .'\')" >' . $groupSelectOptions .'</select>';
				$hiddenFields .= '<input type="hidden" name="chkdom" alt="checkbselect|,|delete_domains|move_domains_grp,move_domains" emsg="'. $LANG->getLang('moveDeleteDomainsError') .'" />';
			} else {
				$markerArray['label_move_domains2'] = $LANG->getLang('labelMoveDomain'. ($domainIdList['count'] > '1' ? 's' : ''));
				$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###USER_MOVE_DOMAINS_GROUP###', '');
				$hiddenFields .= '<input type="hidden" name="chkdom" alt="checkbselect|,|delete_domains|move_domains" emsg="'. $LANG->getLang('moveDeleteDomainsError') .'" />';
			}
			$markerArray['select_move_domains'] = '<select name="move_domains" class="'. STYLE_FIELD .'">' . $groupUserOptions .'</select>';
		} else {
			// remove the part from the content
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###USER_DELETE_DOMAINS###', '');
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
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###USER_DELETE_ALL###', '');
		}

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('deleteUserButtonDelete') .'" class="'. STYLE_BUTTON .'" />';

		$hiddenFields .= '<input type="hidden" name="cmd" value="delete" />';
		$hiddenFields .= '<input type="hidden" name="id" value="'. $conf['data']['id'] .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInDiv('delete-form', $TBE_TEMPLATE->wrapInFormTags($subpart, 'processUser(document.forms[0]);'));

		return $content;
	}




	/**
	 * Creates form to move domain.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function moveUser($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;
		$id = $conf['data']['id'];
		$idCount = count(lib_div::trimExplode(',', $id));

		// Check permissions
		//if (!$USER->hasPerm('domains_move')) {
		if (!$USER->hasPerm('users_admin')) {
			lib_logging::addLogMessage('users', 'move', 'permission', 'logMsgNoPermission');
			return $TBE_TEMPLATE->noPermissionMessage(true);
		}

		$content = '';
		$hiddenFields = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		if ($idCount <= 1) {
			$userName = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($id);
			$content .= $TBE_TEMPLATE->header($LANG->getLang('moveUserTitle', array('userName'=>$userName)));
		} else {
			$content .= $TBE_TEMPLATE->header($LANG->getLang('moveUsersTitle', array('count'=>$idCount)));
		}

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'users.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###USER_MOVE###');

		// Add table with back-button
		$naviDiv = '<table width="100%" cellspacing="0" cellpadding="0"><tr>';
		$naviDiv .= '<td align="right">'. $TBE_TEMPLATE->genSingleButton($LANG->getLang('buttonBackToList'), 'showData(\'users\')') .'</td>';
		$naviDiv .= '</table>';
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $naviDiv);

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get list of groups
		$groupSelectOptions = $TBE_TEMPLATE->groupSelectOptions();

		$markerArray['label_move_users'] = $LANG->getLang('labelMoveUser'. ($idCount > '1' ? 's' : ''));
		$markerArray['select_move_users_grp'] = '<select name="move_users_grp" class="'. STYLE_FIELD .'">' . $groupSelectOptions .'</select>';
		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('moveUserButtonMove') .'" class="'. STYLE_BUTTON .'" />';

		$hiddenFields .= '<input type="hidden" name="cmd" value="move" />';
		$hiddenFields .= '<input type="hidden" name="id" value="'. $id .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInDiv('move-form', $TBE_TEMPLATE->wrapInFormTags($subpart, 'processUser(document.forms[0]);'));

		return $content;
	}





	/**
	 * Process the submitted user data.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function processUser($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;

		$fd = $conf['formdata'];

		switch ($fd['hidden']['cmd']) {
			case 'insert':
				// Check permissions
				if (!$USER->hasPerm('users_add')) {
					$TBE_TEMPLATE->noPermissionMessage();
					lib_logging::addLogMessage('users', 'add', 'permission', 'logMsgNoPermission');
					break;
				}
				// Check if limit exceeded
				if (lib_div::limitExceeded($conf)) break;

				// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				// Make sure the user did not change the group id somehow if the user is not an user admin
				if (!$USER->hasPerm('user_admin')) {
					$fd['data']['grp_id'] = $fd['hidden']['grp_id'];
					 if ($fd['data']['grp_id'] != $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid'])) {
					 	$TBE_TEMPLATE->noPermissionMessage(true);
					 	lib_logging::addLogMessage('users', 'add', 'permission', 'logMsgNoPermission');
						break;
					 }
				}

				// Check if there is already an identical username
				$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'users', 'username='. lib_DB::fullQuoteStr($fd['data']['username'])));
				if (mysql_error()) {
					lib_logging::addLogMessage('users', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($res) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('usernameDuplicateError', array('userName' => stripslashes($fd['data']['username']))), 'clearFieldValues(\'formdata\', \'password,cpassword\');');
					break;
				}

				$dataArr = lib_div::splitSpecialPartsFromFormdata($fd['data'], 'users');
				if (!$dataArr) break;

				// compare the new password with the confirmation
				if ($dataArr['password'] != $dataArr['cpassword']) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('confirmPasswordError'), 'clearFieldValues(\'formdata\', \'password,cpassword\');');
					return $TBE_TEMPLATE->message;
				}
				// seems to be correct, unset some unneeded vars
				unset($dataArr['cpassword']);

				$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('users', $dataArr);
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
					lib_logging::addLogMessage('users', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				} else {
					// reload user permissions to make dem active
					$USER->perm = $USER->getPermissions();
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('addUserSuccess', array('userName' => stripslashes($fd['data']['username']))), 'clearFieldValues(\'formdata\', \'username,password,cpassword,name,firstname,email,notice\'); clearFieldValues(\'formdata\', \'max_domains,max_templates\', true); toggleFields(\'\', \'perm_ADM_admin|1|0,grp_id|1|0\');');
					lib_logging::addLogMessage('users', 'add', 'info', 'logMsgUserAdd', array('userName' => stripslashes($fd['data']['username'])));
				}
				break;


			case 'update':
				$id = $fd['hidden']['id'];
				settype($id, 'integer');

				// Check permissions
				if (!($USER->hasPerm('users_edit_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true)) || $id == $_SESSION['uid'] || $id == '1') {
					$TBE_TEMPLATE->noPermissionMessage();
					lib_logging::addLogMessage('users', 'edit', 'permission', 'logMsgNoPermission');
					break;
				}

				// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				// Check if there is already an identical username
				$res = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'users', 'username='. lib_DB::fullQuoteStr($fd['data']['username']) .' AND id<>'. lib_DB::fullQuoteStr($id)));
				if (mysql_error()) {
					lib_logging::addLogMessage('users', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				}
				if ($res) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('usernameDuplicateError', array('userName' => stripslashes($fd['data']['username']))));
					break;
				}

				$dataArr = lib_div::splitSpecialPartsFromFormdata($fd['data'], 'users', $id);
				if (!$dataArr) break;

				// password fields submitted? check them
				if (strlen($dataArr['password']) == '32' && strlen($dataArr['cpassword']) == '32') {
					// compare the new password with the confirmation
					if ($dataArr['password'] != $dataArr['cpassword']) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('confirmPasswordError'), 'clearFieldValues(\'formdata\', \'password,cpassword\'');
						return $TBE_TEMPLATE->message;
					}
					unset($dataArr['cpassword']);
				} else {
					// unset password fields
					unset($dataArr['password'], $dataArr['cpassword']);
				}

				// Update db
				$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('users', 'id='. lib_DB::fullQuoteStr($id), $dataArr);
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysql' => mysql_error())));
					lib_logging::addLogMessage('users', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('editUserSuccess', array('userName' => stripslashes($fd['data']['username']))), 'clearFieldValues(\'formdata\', \'password,cpassword\');');
					lib_logging::addLogMessage('users', 'edit', 'info', 'logMsgUserEdit', array('userName' => stripslashes($fd['data']['username'])));
				}

				break;

			case 'delete':
				$ids = lib_div::trimExplode(',', $fd['hidden']['id']);
				$idCount = count($ids);
				$userNames = array();
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
					if (!($USER->hasPerm('users_delete_group') && $USER->isOwnerOfRecord($id, $conf['csite'], true)) || $id == $_SESSION['uid'] || $id == '1') {
						$TBE_TEMPLATE->noPermissionMessage();
						lib_logging::addLogMessage('users', 'delete', 'permission', 'logMsgNoPermission');
						break;
					}
					$userNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($id);
				}
				if ($error) break;

				// 1st delete or move domains
				// Only valid if all "domain" fields ar submitted
				if (lib_div::isset_value($fd, 'data=>delete_domains', false) && lib_div::isset_value($fd, 'data=>move_domains', false)) {
					// Make sure not both was submitted (move and delete) - Can't be possible if JS works fine...
					if ($fd['data']['delete_domains'] && $fd['data']['move_domains']) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveAndDeleteDomainsError'));
						break;
					}
					// Delete domains an records
					// Get list of domain id's
					$domainIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('dom_id', 'domain_owners', 'usr_id', $fd['hidden']['id']);
					if ($fd['data']['delete_domains']) {
						// Order to delete: domain records => domains => domain/owner relation
						// delete records:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('records', 'domain_id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteDomainRecordsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						// delete domains:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('domains', 'id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteDomainsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						// delete relations
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('domain_owners', 'dom_id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('message' => $LANG->getLang('deleteDomainRelationsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteDomainsSuccess', array('count' => $domainIdList['count'])), 'rmNode("form-domains")');
					}
					if ($fd['data']['move_domains']) {
						// Change user-ID in domain_owners table
						$updateFields = array('usr_id'=>$fd['data']['move_domains']);
						$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('domain_owners', 'dom_id IN ('. lib_DB::fullQuoteStrList($domainIdList['list']) .')', $updateFields);
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveDomainsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$moveToUser = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($fd['data']['move_domains']);
						$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($fd['data']['move_domains']));
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveDomainsSuccess', array('count' => $domainIdList['count'], 'userName' => $moveToUser, 'groupName' => $moveToGroup)), 'rmNode("form-domains")');
					}
				} elseif (lib_div::isset_value($fd, 'data=>delete_domains', false) || lib_div::isset_value($fd, 'data=>move_domains', false)) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('deleteMissingValuesError'));
					break;
				}

				// 2nd delete or move templates
				// Only valid if all "template" fields ar submitted
				if (lib_div::isset_value($fd, 'data=>delete_templates', false) && lib_div::isset_value($fd, 'data=>move_templates', false)) {
					// Make sure not both was submitted (move and delete) - Can't be possible if JS works fine...
					if ($fd['data']['delete_templates'] && $fd['data']['move_templates']) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveAndDeleteTemplatesError'));
						break;
					}
					// Delete templates an records
					// Get list of template id's
					$templateIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'templates', 'usr_id', $fd['hidden']['id']);
					if ($fd['data']['delete_templates']) {
						// Order to delete: template records => templates
						// delete records:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('template_records', 'tmpl_id IN ('. lib_DB::fullQuoteStrList($templateIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteTemplateRecordsDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						// delete templates:
						$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('templates', 'id IN ('. lib_DB::fullQuoteStrList($templateIdList['list']) .')');
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteTemplatesDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteTemplatesSuccess', array('count' => $templateIdList['count'])), 'rmNode("form-templates")');
					}
					if ($fd['data']['move_templates']) {
						// Change user-ID in templates table
						$updateFields = array('usr_id'=>$fd['data']['move_templates']);
						$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('templates', 'id IN ('. lib_DB::fullQuoteStrList($templateIdList['list']) .')', $updateFields);
						if (mysql_error()) {
							$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveTemplatesDbError'), 'mysqlError' => mysql_error())));
							lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
							break;
						}
						$moveToUser = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($fd['data']['move_templates']);
						$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($fd['data']['move_templates']));
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveTemplatesSuccess', array('count' => $templateIdList['count'], 'userName' => $moveToUser, 'groupName' => $moveToGroup)), 'rmNode("form-templates")');
					}
				} elseif (lib_div::isset_value($fd, 'data=>delete_templates', false) || lib_div::isset_value($fd, 'data=>move_templates', false)) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('deleteMissingValuesError'));
					break;
				}

				// 3th delete user
				$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('users', 'id IN ('. lib_DB::fullQuoteStrList($fd['hidden']['id']) .')');
				if (mysql_error()) {
					if ($idCount <= 1) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteUserDbError'), 'mysqlError' => mysql_error())));
					} else {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('deleteUsersDbError'), 'mysqlError' => mysql_error())));
					}
					lib_logging::addLogMessage('users', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					break;
				}

				// add Log messages for all users
				foreach ($userNames as $userName) {
					lib_logging::addLogMessage('users', 'delete', 'info', 'logMsgUserDelete', array('userName' => $userName));
				}

				if ($idCount <= 1) {
					$userName = $userNames[0];
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteUserSuccess', array('userName' => $userName)));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('deleteUsersSuccess', array('count' => $idCount)));
				}
				$TBE_TEMPLATE->addMessage('', '', 'rmNode("delete-form");');
				break;

			case 'move':
				$ids = lib_div::trimExplode(',', $fd['hidden']['id']);
				$idCount = count($ids);
//				$domainNames = array();
				$error = false;

				// Clean the data a bit (Remove HTML/PHP tags, add slashes)
				lib_div::stripTagsOnArray($fd);
				lib_div::addSlashesOnArray($fd);

				if (lib_div::isNonIntInArray($fd['data'])) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('wrongData'));
					break;
				}

				if (lib_div::isset_value($fd, 'data=>move_users_grp')) {
					$moveToGroup = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupName($fd['data']['move_users_grp']);
				} else {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('moveMissingValuesError'));
					break;
				}

				// Check permissions
				foreach ($ids as $id) {
					settype($id, 'integer');
					if (!$USER->hasPerm('users_admin')) {
						$TBE_TEMPLATE->noPermissionMessage();
						lib_logging::addLogMessage('users', 'move', 'permission', 'logMsgNoPermission');
						$error = true;
						break;
					}
					$userNames[] = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($id);
				}
				if ($error) break;

				// Change group-ID
				$updateFields = array('grp_id'=>$fd['data']['move_users_grp']);
				$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('users', 'id IN ('. lib_DB::fullQuoteStrList($fd['hidden']['id']) .')', $updateFields);
				if (mysql_error()) {
					if ($idCount <= 1) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveUserDbError'), 'mysqlError' => mysql_error())));
					} else {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbErrorExt', array('message' => $LANG->getLang('moveUsersDbError'), 'mysqlError' => mysql_error())));
					}
					lib_logging::addLogMessage('users', 'move', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					break;
				}

				// add Log messages for all users
				foreach ($userNames as $userName) {
					lib_logging::addLogMessage('users', 'move', 'info', 'logMsgUserMove', array('userName' => $userName, 'groupName' => $moveToGroup));
				}

				if ($idCount <= 1) {
					$userName = $userNames[0];
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveUserSuccess', array('userName' => $userName, 'groupName' => $moveToGroup)));
				} else {
					$TBE_TEMPLATE->addMessage('success', $LANG->getLang('moveUsersSuccess', array('count' => $idCount, 'groupName' => $moveToGroup)));
				}
				$TBE_TEMPLATE->addMessage('', '', 'rmNode("form-table");');
				break;
		}

		// Debugging
//		$TBE_TEMPLATE->addMessage('success', print_r($prefArr, true));
//		$TBE_TEMPLATE->addMessage('success', print_r($dataArr, true));

		return $TBE_TEMPLATE->message;
	}


}

?>