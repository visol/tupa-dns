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
 * User preferences functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_preferences {

	/**
	 * Generates form to edit user permissions.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function editUserPrefs($conf) {
		global $TBE_TEMPLATE, $TUPA_CONF_VARS, $USER, $LANG, $MENU;
		$markerArray = lib_div::setDefaultMarkerArray();

		$hiddenFields = '';

		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'users', 'id='. lib_DB::fullQuoteStr($_SESSION['uid']), '', '', '1');
		if (mysql_error()) {
			lib_logging::addLogMessage('prefs', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return;
		}
		$row = mysql_fetch_assoc($res);
		lib_div::stripSlashesOnArray($row);
		lib_div::htmlspecialcharOnArray($row);

		$content = $TBE_TEMPLATE->header($LANG->getLang('prefsTitle', array('userName' => $row['username'], 'firstName' => $row['firstname'], 'name' => $row['name'])));

		$content .= $TBE_TEMPLATE->genMessageField();

		$langSelectOptions = $TBE_TEMPLATE->languageSelectOptions($TUPA_CONF_VARS['SYS']['language']);
		$skinSelectOptions = $TBE_TEMPLATE->skinSelectOptions($TUPA_CONF_VARS['SKINS']['skin']);
		$startPageSelectOptions = $TBE_TEMPLATE->startPageSelectOptions($MENU->startPage);
		$displayHelpOptions = $TBE_TEMPLATE->displayHelpSelectOptions($TUPA_CONF_VARS['PREFS']['displayHelp']);

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'preferences.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###PREFS_EDIT###');

		$markerArray['prefs_general_subtitle'] = $LANG->getLang('prefsGeneralSubtitle');
		$markerArray['label_language'] = $LANG->getLang('labelLanguage');
		$markerArray['select_language'] = '<select name="pref_SYS_language" class="'. STYLE_FIELD .'" alt="select" emsg="'. $LANG->getLang('languageError') .'">' . $langSelectOptions .'</select>';
		$markerArray['label_start_page'] = $LANG->getLang('labelStartPage');
		$markerArray['select_start_page'] = '<select name="pref_PREFS_startPage" class="'. STYLE_FIELD .'">' . $startPageSelectOptions .'</select>'. $LANG->getHelp('helpPrefsStartPage');
		$markerArray['label_help_display'] = $LANG->getLang('labelHelpDisplay');
		$markerArray['select_help_display'] = '<select name="pref_PREFS_displayHelp" class="'. STYLE_FIELD .'">'. $displayHelpOptions .'</select>'. $LANG->getHelp('helpPrefsDisplayHelp');
		$markerArray['label_skin'] = $LANG->getLang('labelSkin');
		$markerArray['select_skin'] = '<select name="pref_SKINS_skin" class="'. STYLE_FIELD .'">' . $skinSelectOptions .'</select>'. $LANG->getHelp('helpPrefsSkin');
//		$markerArray['label_disable_tabs'] = $LANG->getLang('labelDisableTabs');
//		$markerArray['input_disable_tabs'] = '<input type="checkbox" name="pref_PREFS_disableTabs" value="1" '. ($TUPA_CONF_VARS['PREFS']['disableTabs'] ? 'checked' : '') .' />'. $LANG->getHelp('helpPrefsDisableTabs');
		$markerArray['label_rows_per_site'] = $LANG->getLang('labelRowsPerSite');
		$markerArray['input_rows_per_site'] = '<input type="text" name="pref_PREFS_linesPerSite" class="'. STYLE_FIELD .'" size="5" alt="number|0|'. $TUPA_CONF_VARS['PREFS']['minLinesPerSite'] .'|'. $TUPA_CONF_VARS['PREFS']['maxLinesPerSite'] .'" emsg="'. $LANG->getLang('rowsPerSiteError', array('min'=>$TUPA_CONF_VARS['PREFS']['minLinesPerSite'], 'max'=>$TUPA_CONF_VARS['PREFS']['maxLinesPerSite'])) .'" value="'. $TUPA_CONF_VARS['PREFS']['linesPerSite'] .'" />'. $LANG->getHelp('helpPrefsLinesPerSite');
		$markerArray['label_navi_show_pages'] = $LANG->getLang('labelNaviShowPages');
		$markerArray['input_navi_show_pages'] = '<input type="text" name="pref_PREFS_naviShowPages" class="'. STYLE_FIELD .'" size="5" alt="number|0|'. $TUPA_CONF_VARS['PREFS']['minNaviShowPages'] .'|'. $TUPA_CONF_VARS['PREFS']['maxNaviShowPages'] .'" emsg="'. $LANG->getLang('naviShowPagesError', array('min'=>$TUPA_CONF_VARS['PREFS']['minNaviShowPages'], 'max'=>$TUPA_CONF_VARS['PREFS']['maxNaviShowPages'])) .'" value="'. $TUPA_CONF_VARS['PREFS']['naviShowPages'] .'" />'. $LANG->getHelp('helpPrefsNaviShowPages');
		$markerArray['label_soa_primary'] = $LANG->getLang('labelSoaPrimary');
		$markerArray['input_soa_primary'] = '<input type="text" name="pref_DNS_defaultSoaPrimary" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('soaPrimaryError') .'" value="'. $TUPA_CONF_VARS['DNS']['defaultSoaPrimary'] .'" />'. $LANG->getHelp('helpPrefsDefaultSoaPimary');

		if ($USER->hasPerm('logging_show')) {
			$maxSelectOptions = $TBE_TEMPLATE->logMaxSelectOptions($TUPA_CONF_VARS['LOGGING']['itemAmount']);
			$markerArray['prefs_logging_subtitle'] = $LANG->getLang('prefsLoggingSubtitle');
			$markerArray['label_logging_max'] = $LANG->getLang('labelLoggingMax');
			$markerArray['select_logging_max'] = '<select name="pref_LOGGING_itemAmount" class="'. STYLE_FIELD .'">' . $maxSelectOptions .'</select>'. $LANG->getHelp('helpPrefsItemAmount');
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###PREFS_LOGGING###', '');
		}

		$markerArray['prefs_personal_subtitle'] = $LANG->getLang('prefsPersonalSubtitle');
		$markerArray['label_name'] = $LANG->getLang('labelName');
		$markerArray['input_name'] = '<input type="text" name="name" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('nameError') .'" value="'. $row['name'] .'" />';
		$markerArray['label_firstname'] = $LANG->getLang('labelFirstname');
		$markerArray['input_firstname'] = '<input type="text" name="firstname" class="'. STYLE_FIELD .'" size="30" alt="blank" emsg="'. $LANG->getLang('firstnameError') .'" value="'. $row['firstname'] .'" />';
		$markerArray['label_email'] = $LANG->getLang('labelEmail');
		$markerArray['input_email'] = '<input type="text" name="email" class="'. STYLE_FIELD .'" size="30" alt="email|1" emsg="'. $LANG->getLang('emailError') .'" value="'. $row['email'] .'" />';

		$markerArray['prefs_password_subtitle'] = $LANG->getLang('prefsPasswordSubtitle');
		$markerArray['label_old_password'] = $LANG->getLang('labelOldPassword');
		$markerArray['input_old_password'] = '<input type="password" name="oldpassword" class="'. STYLE_FIELD .'" size="20" alt="blank|bok" emsg="'. $LANG->getLang('oldPasswordError') .'" />';
		$markerArray['label_new_password'] = $LANG->getLang('labelNewPassword');
		$markerArray['input_new_password'] = '<input type="password" name="password" class="'. STYLE_FIELD .'" size="20" alt="length|7|bok" emsg="'. $LANG->getLang('passwordError') .'" />';
		$markerArray['label_confirm_password'] = $LANG->getLang('labelConfirmPassword');
		$markerArray['input_confirm_password'] = '<input type="password" name="cpassword" class="'. STYLE_FIELD .'" size="20" alt="equalto|password|bok" emsg="'. $LANG->getLang('confirmPasswordError') .'" />';

		$hiddenFields .= '<input type="hidden" name="valpassfield" alt="allornone|,|oldpassword,password,cpassword" emsg="'. $LANG->getLang('passwordFieldsError') .'" />';

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('prefsButtonSave') .'" class="'. STYLE_BUTTON .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInFormTags($subpart, 'cryptPassFields(\'oldpassword,password,cpassword\'); processUserPref(document.forms[0]);');

		return $content;
	}




	/**
	 * Process the submitted preferences data.
	 *
	 * @param	array		Configuration array
	 * @return	string		messages
	 */
	function processUserPref($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		$fd = $conf['formdata'];
		$reloadPage = false;

		// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
		lib_div::stripTagsOnArray($fd);
		lib_div::addSlashesOnArray($fd);

		$dataArr = lib_div::splitSpecialPartsFromFormdata($fd['data'], 'users', $_SESSION['uid']);
		if (!$dataArr) return;

		// password fields submitted? check them
		if (strlen($dataArr['oldpassword']) == '32' && strlen($dataArr['password']) == '32' && strlen($dataArr['cpassword']) == '32') {
			// compare the new password with the confirmation
			if ($dataArr['password'] != $dataArr['cpassword']) {
				$TBE_TEMPLATE->addMessage('error', $LANG->getLang('confirmPasswordError'), 'clearFieldValues(\'formdata\', \'oldpassword,password,cpassword\'');
				return;
			}
			// check the old password with db
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('password', 'users', 'id='. lib_DB::fullQuoteStr($_SESSION['uid']), '', '', '1');
			if (mysql_error()) {
				lib_logging::addLogMessage('prefs', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			}
			$row = mysql_fetch_assoc($res);
			if ($dataArr['oldpassword'] != $row['password']) {
				$TBE_TEMPLATE->addMessage('error', $LANG->getLang('passwordCurrentError'), 'clearFieldValues(\'formdata\', \'oldpassword,password,cpassword\')');
				return;
			}
			// seems to be correct, unset some unneeded vars
			unset($dataArr['oldpassword'], $dataArr['cpassword']);
		} else {
			// unset password fields
			unset($dataArr['oldpassword'], $dataArr['password'], $dataArr['cpassword']);
		}

		// Update db
		$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('users', 'id='. lib_DB::fullQuoteStr($_SESSION['uid']), $dataArr);
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysql' => mysql_error())));
			lib_logging::addLogMessage('prefs', 'edit', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return;
		} else {
			// Get some current (soon old) values to check for changes because a reload is needed
			$oldLanguage = $TUPA_CONF_VARS['SYS']['language'];
			$oldSkin = $TUPA_CONF_VARS['SKINS']['skin'];

			// now activating the changes made
			$USER->loadUserPreferences($_SESSION['uid']);

			// Check if something changed
			$oldLanguage != $TUPA_CONF_VARS['SYS']['language'] ? $reloadPage = true : '';
			$oldSkin != $TUPA_CONF_VARS['SKINS']['skin'] ? $reloadPage = true : '';

			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('prefsUpdateSuccess'), 'clearFieldValues(\'formdata\', \'oldpassword,password,cpassword\');');

			// Add reload message if needed
			if ($reloadPage) $TBE_TEMPLATE->addMessage('success', $LANG->getLang('prefsUpdateReload'));
		}

		// Debugging
//		$TBE_TEMPLATE->addMessage('success', print_r($prefArr, true));
//		$TBE_TEMPLATE->addMessage('success', print_r($dataArr, true));
	}




}
?>