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
 * General functions used on many places.
 * - Requesthandler which handles the requests from JS
 * - Generating "show" table
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_general {

	/**
	 * Handles the requests submitted by JavaScript and calls the php function.
	 *
	 * @param	array		Configuration array
	 * @return	array		Content and messages
	 */
	function defaultRequestHandler($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $startMT, $debugMode;

		$output = array();

		switch($conf['part']) {
		// General
			case 'showData':
				$output['content'] = $this->showData($conf);
				break;
			case 'updateData':
				$output['content'] = $this->updateData($conf);
				break;
		// Groups
			case 'addEditGroup':
				$output['content'] = tupa_groups::addEditGroup($conf);
				break;
			case 'deleteGroup':
				$output['content'] = tupa_groups::deleteGroup($conf);
				break;
			case 'processGroup':
				tupa_groups::processGroup($conf);
				break;
		// Users
			case 'addEditUser':
				$output['content'] = tupa_users::addEditUser($conf);
				break;
			case 'moveUser':
				$output['content'] = tupa_users::moveUser($conf);
				break;
			case 'deleteUser':
				$output['content'] = tupa_users::deleteUser($conf);
				break;
			case 'processUser':
				tupa_users::processUser($conf);
				break;
		// Templates
			case 'addEditTemplate':
				$output['content'] = tupa_templates::addEditTemplate($conf);
				break;
			case 'moveTemplate':
				$output['content'] = tupa_templates::moveTemplate($conf);
				break;
			case 'deleteTemplate':
				tupa_templates::deleteTemplate($conf);
				break;
			case 'processTemplate':
				tupa_templates::processTemplate($conf);
				break;
		// Domains
			case 'addEditDomain':
				$output['content'] = tupa_domains::addEditDomain($conf);
				break;
			case 'addDomainWithTemplate':
				$output['content'] = tupa_domains::addDomainWithTemplate($conf);
				break;
			case 'moveDomain':
				$output['content'] = tupa_domains::moveDomain($conf);
				break;
			case 'deleteDomain':
				tupa_domains::deleteDomain($conf);
				break;
			case 'processDomain':
				tupa_domains::processDomain($conf);
				break;
		// Logging
			case 'showLogMessages':
				$output['content'] = tupa_logging::showLogMessages($conf);
				break;
			case 'updateLogMessages':
				$output['content'] = tupa_logging::updateLogMessages($conf);
				break;
		// Preferences
			case 'editUserPrefs':
				$output['content'] = tupa_preferences::editUserPrefs($conf);
				break;
			case 'processUserPref':
				tupa_preferences::processUserPref($conf);
				break;
		// Tools
			case 'showTools':
				$output['content'] = tupa_tools::showTools($conf);
				break;
			case 'processIpChange':
				tupa_tools::processIpChange($conf);
				break;
		// Sysinfo
			case 'updateShortSysinfo':
				$output['content'] = tupa_sysinfo::updateShortSysinfo();
				$dontParseTabs = true;
				break;
			case 'showSysinfo':
				$output['content'] = tupa_sysinfo::showSysinfo();
				break;
		// Backup
			case 'showBackup':
				$output['content'] = tupa_backup::showBackup();
				break;
			case 'processBackup':
				tupa_backup::processBackup($conf);
				break;
		// System
			case 'showSystemConfig':
				$output['content'] = tupa_system::showSystemConfig($conf);
				break;
			case 'processSystemConfig':
				tupa_system::processSystemConfig($conf);
				break;
			case 'getLangMgrUpdate':
				$output['content'] = tupa_system::getLangMgrUpdate($conf);
				break;
			case 'deleteSysLang':
			case 'deleteSysSkin':
				tupa_system::deleteToolsItem($conf);
				break;
			case 'updateSysLang':
			case 'updateSysSkin':
				tupa_system::updateToolsItem($conf);
				break;
			case 'installSysLang':
			case 'installSysSkin':
				tupa_system::installToolsItem($conf);
				break;
			case 'getSkinMgrUpdate':
				$output['content'] = tupa_system::getSkinMgrUpdate($conf);
				break;
		// Others
			case 'getusersofgroup':
				$output['content'] = $this->getUsersOfGroup($conf);
				$dontParseTabs = true;
				break;
			case 'getdomainexample':
				$output['content'] = tupa_domains::getDomainExample($conf);
				$dontParseTabs = true;
				break;
			case 'getipsoftype':
				$output['content'] = $this->getIpsOfType($conf);
				$dontParseTabs = true;
				break;
		}

		// Parse Tabs
		if (isset($output['content']) && !isset($dontParseTabs)) {  // OBSOLETE:  && !$TUPA_CONF_VARS['PREFS']['disableTabs']
			$output['content'] = $TBE_TEMPLATE->parseTabs($output['content']);

			// Parse whole content to IE PNG transparency fix function
			$output['content'] = $TBE_TEMPLATE->replacePngTags($output['content'], 'images/');
		}
		/*elseif(isset($output['content']) && !isset($dontParseTabs)) {
			// Remove Tabs navigation
			$output['content'] = $TBE_TEMPLATE->substituteMarker($output['content'], '###REGISTER_NAVIGATION###', '');
		}*/

		if ($debugMode) {
			$TBE_TEMPLATE->addMessage('debug', 'Parse time: '. round((lib_div::microtime_float() - $startMT)*1000, 1) .' ms');
		}

		$output['messages'] = $TBE_TEMPLATE->message;

		return $output;
	}



	/**
	 * Generates the "show" table the first time with header char list.
	 *
	 * @param	array		Configuration array
	 * @return	string		"show" table content
	 */
	function showData($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;

		if (!$USER->hasPerm($conf['csite'] .'_show, '. $conf['csite'] .'_show_group')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$content = '';
		$otherButtons = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		// generate header
		$content .= $TBE_TEMPLATE->header($LANG->getLang('show'. lib_div::firstUpper($conf['csite']) .'Title'));
		$content .= $TBE_TEMPLATE->genMessageField();

		// Check if a limit is exceeded
		$limitExceeded = lib_div::limitExceeded($conf);

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'general.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###GENERAL_SHOW_SEARCH###');

		// Some additional button(s)
		if (!$limitExceeded && $USER->hasPerm($conf['csite'] .'_add,'. $conf['csite'] .'_add_group')) {
			if ($conf['csite'] == 'domains') {
				$otherButtons = $TBE_TEMPLATE->genSingleButton($LANG->getLang('show'. lib_div::firstUpper($conf['csite']) .'ButtonAdd'), 'addEdit'. lib_div::firstUpper($conf['csite'], true) .'()');
				if ($USER->hasPerm($conf['csite'] .'_add_with_template')) {
					$otherButtons .= $TBE_TEMPLATE->genSingleButton($LANG->getLang('show'. lib_div::firstUpper($conf['csite']) .'ButtonAddWithTemplate'), 'add'. lib_div::firstUpper($conf['csite'], true) .'WithTemplate()');
				}
			} else {
				$otherButtons = $TBE_TEMPLATE->genSingleButton($LANG->getLang('show'. lib_div::firstUpper($conf['csite']) .'ButtonAdd'), 'addEdit'. lib_div::firstUpper($conf['csite'], true) .'()');
			}
		}

		// Add table with search-box and other buttons
		$markerArray['search_form'] = $TBE_TEMPLATE->genSearchForm($conf);
		$markerArray['other_buttons'] = $otherButtons;

		// Substitute search form and add to content
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$content .= $TBE_TEMPLATE->wrapInDiv('search-form', $subpart);

		// add the character links
		$conf['csite'] == 'domains' ? $reverse = true : $reverse = false;
		$content .= $TBE_TEMPLATE->genCharLinks($conf, $reverse);

		// and finaly add the page navigation and the table
		$content .= $TBE_TEMPLATE->wrapInDiv('div-show', $this->updateData($conf));

		return $content;
	}


	/**
	 * Generates the table itself without title or char links for page update.
	 *
	 * @param	array		Configuration array
	 * @return	string		table content
	 */
	function updateData($conf) {
		global $TBE_TEMPLATE, $TUPA_CONF_VARS, $USER, $LANG;

		if (!$USER->hasPerm($conf['csite'] .'_show, '. $conf['csite'] .'_show_group')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$content = '';
		$multiChkBox = false;
		$rowColor = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		// Get the path and sizes of the used icons
		$icons = array();
		$icons['edit'] = lib_div::getImageInfo('icons/edit.png');
		$icons['move'] = lib_div::getImageInfo('icons/move.png');
		$icons['del'] = lib_div::getImageInfo('icons/garbage.png');
		$icons['move_m'] = lib_div::getImageInfo('icons/move_m.png');
		$icons['del_m'] = lib_div::getImageInfo('icons/garbage_m.png');
		$icons['mark_all'] = lib_div::getImageInfo('icons/mark_all.png');

		// Set default values if nothing submitted
		if (!isset($conf['show']))  $conf['show'] = array();
		if (!isset($conf['show']['char']))  $conf['show']['char'] = 'ALL';
		if (!isset($conf['show']['page']))  $conf['show']['page'] = '1';
		if (!isset($conf['show']['search']))  $conf['show']['search'] = '';

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'general.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###GENERAL_SHOW###');
		// Substitute global markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$markerArray = array();

		// Get some subparts
		$subpartColHeader = $TBE_TEMPLATE->getSubpart($subpart, '###GENERAL_COL_HEADER###');
		$subpartSingleTemp = $TBE_TEMPLATE->getSubpart($subpart, '###GENERAL_SHOW_SINGLE###');

		// Set the configured table headers
		$showFieldsArr = lib_div::trimExplode(',', $TUPA_CONF_VARS[strtoupper($conf['csite'])]['showFields']);
		$headerSubpartContent = '';
		while (list(,$field) = each($showFieldsArr)) {
			$markerArray['header_class'] = $conf['csite'] .'-cell-'. $field;
			$markerArray['header'] = $LANG->getLang('show'. lib_div::firstUpper($conf['csite']) .'Col'. lib_div::firstUpper($field));
			$headerSubpartContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartColHeader, $markerArray, '###|###', '1');
		}

		// Set additional cols if allowed to edit/move/delete (just to have the correct amount of columns at the end)
		if ($USER->hasPerm($conf['csite'] .'_admin, '. $conf['csite'] .'_edit, '. $conf['csite'] .'_edit_group')) {
			$markerArray['header_class'] = $conf['csite'] .'-cell-edit';
			$markerArray['header'] = '&nbsp;';
			$headerSubpartContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartColHeader, $markerArray, '###|###', '1');
		}
		//if ($USER->hasPerm('users_show_group') && $conf['csite'] != 'groups' && $USER->hasPerm($conf['csite'] .'_admin, '. $conf['csite'] .'_edit, '. $conf['csite'] .'_edit_group')) {
		if ($USER->hasPerm('users_show_group') && $conf['csite'] != 'groups' && ($USER->hasPerm($conf['csite'] .'_admin') || $USER->hasPerm($conf['csite'] .'_edit, '. $conf['csite'] .'_edit_group') && $conf['csite'] != 'users')) {
			$multiChkBox = true;
			$markerArray['header_class'] = $conf['csite'] .'-cell-move';
			//$markerArray['header'] = '&nbsp;';
			$markerArray['header'] = '<a href="javascript:void(0);" onclick="move'. lib_div::firstUpper($conf['csite'], true) .'();" style="display: none;" id="mmove"><img src="'. $icons['move_m']['path'] .'" '. $icons['move_m']['size'][3] .' border="0" /></a>';
			$headerSubpartContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartColHeader, $markerArray, '###|###', '1');
		}

		/// The headers are substituted after the single elementr were parsed because we need some values from it for mark all/none field

		// parse search into an array
		$searchFields = lib_div::doubleTrimExplode(',', '=', $conf['show']['search']);

		// Generate the search query
		$sqlArr = $GLOBALS['TUPA_DB']->genShowSearchQuery($conf, $searchFields);

		// Calculate start position
		$linesPerSite = $TUPA_CONF_VARS['PREFS']['linesPerSite'];
		$mysqlStart =  $linesPerSite * $conf['show']['page'] - $linesPerSite;

		// Set the rest of the SQL query
		$sqlArr['GROUPBY'] = '';
		$sqlArr['ORDERBY'] = 'name';
		$sqlArr['LIMIT'] = '';
		//$sqlArr['LIMIT'] = $mysqlStart .','. $linesPerSite;

		// Get data
		$res = $GLOBALS['TUPA_DB']->exec_SELECT_queryArray($sqlArr);
		if (mysql_error()) {
			lib_logging::addLogMessage('general', 'show', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
		}

		$totalLines = mysql_num_rows($res);

		// Debug
		//$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($sqlArr, true)));

		// Add navigation
		$markerArray['navigation'] = $TBE_TEMPLATE->genNavigation($conf, $totalLines, $linesPerSite, $TUPA_CONF_VARS['PREFS']['naviShowPages']);
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$markerArray = array();

		// Get single cell subpart
		$subpartSingleCell = $TBE_TEMPLATE->getSubpart($subpartSingleTemp, '###GENERAL_SHOW_SINGLE_CELL###');

		$subpartContent = '';
		$moveCount = 0;
		$delCount = 0;
		$lineCount = 0;
		$markAllArr = array();

		while ($row = mysql_fetch_assoc($res)) {

			// Don't allow an user to edit/delete himself or delete the group he is member of. Same for Admin (id=1)
			switch ($conf['csite']) {
				case 'users':
					$allowEdit2 = $row['id'] == $_SESSION['uid'] || $row['id'] == '1' ? false : true;
					$allowDelete2 = $allowEdit2;
					break;
				case 'groups':
					$allowEdit2 = true;
					$allowDelete2 = $row['id'] == $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']) || $row['id'] == '1' ? false : true;
					break;
				default:
					$allowEdit2 = true;
					$allowDelete2 = true;
					break;
			}

			if ($allowEdit2 && $allowDelete2) $markAllArr[] = $row['id'];

			if ($allowEdit2) $moveCount++;
			if ($allowDelete2) $delCount++;

			if ($lineCount >= $mysqlStart && $lineCount < $mysqlStart + $linesPerSite) {
				$multiChkBox = 0;
				$multiMove = 0;
				$multiDel = 0;
				$subpartSubContent = '';
				reset($showFieldsArr);

				// Strip slashes
				lib_div::stripSlashesOnArray($row);

				// Check first allowed part
				$allowEdit1 = (($USER->hasPerm($conf['csite'] .'_admin, '. $conf['csite'] .'_edit_group')) OR ($USER->hasPerm($conf['csite'] .'_edit') && $row[($conf['csite'] == 'users' || $conf['csite'] == 'groups' ? 'id' : 'usr_id')] == $_SESSION['uid'])) ? true : false;
				//$allowMove1 = $conf['csite'] != 'groups' && $USER->hasPerm('users_show_group') && (($USER->hasPerm($conf['csite'] .'_admin, '. $conf['csite'] .'_edit_group')) OR ($USER->hasPerm($conf['csite'] .'_edit') && $row[($conf['csite'] == 'users' || $conf['csite'] == 'groups' ? 'id' : 'usr_id')] == $_SESSION['uid'])) ? true : false;
				$allowMove1 = $conf['csite'] != 'groups' && $USER->hasPerm('users_show_group') && (($USER->hasPerm($conf['csite'] .'_admin') || $USER->hasPerm($conf['csite'] .'_edit_group') && $conf['csite'] != 'users') OR ($USER->hasPerm($conf['csite'] .'_edit') && $row[($conf['csite'] == 'users' || $conf['csite'] == 'groups' ? 'id' : 'usr_id')] == $_SESSION['uid'])) ? true : false;
				$allowDelete1 = (($USER->hasPerm($conf['csite'] .'_admin, '. $conf['csite'] .'_delete_group')) OR ($USER->hasPerm($conf['csite'] .'_delete') && $row[($conf['csite'] == 'users' || $conf['csite'] == 'groups' ? 'id' : 'usr_id')] == $_SESSION['uid'])) ? true : false;

				while (list(,$field) = each($showFieldsArr)) {
					$markerArray['cell_class'] = $conf['csite'] .'-cell-'. $field;
					$markerArray['cell'] = htmlspecialchars($row[$field]);
					$subpartSubContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartSingleCell, $markerArray, '###|###', '1');
				}

				// When allowed to edit
				$editOnClick = false;
				if ($allowEdit1 && $allowEdit2) {
					$editOnClick = 'addEdit'. lib_div::firstUpper($conf['csite'], true) .'(\''. $row['id'].' \')';
					$editContent = '<a href="javascript:void(0);" onclick="'. $editOnClick .'"><img src="'. $icons['edit']['path'] .'" '. $icons['edit']['size'][3] .' border="0" /></a>';
				} elseif ($USER->hasPerm($conf['csite'] .'_edit')) {
					$editContent = '&nbsp;';
				} else {
					$editContent = false;
				}
				if ($editContent) {
					$markerArray['cell_class'] = $conf['csite'] .'-cell-edit';
					$markerArray['cell'] = $editContent;
					$subpartSubContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartSingleCell, $markerArray, '###|###', '1');
				}
				// Add doubleclick to edit
				$markerArray['ondblclick'] = $editOnClick ? 'ondblclick="'. $editOnClick .'"' : '';

				// When allowed to move
				if ($allowMove1 && $allowEdit2) {
					$moveContent = '<a href="javascript:void(0);" onclick="move'. lib_div::firstUpper($conf['csite'], true) .'(\''. $row['id'].' \');"><img src="'. $icons['move']['path'] .'" '. $icons['move']['size'][3] .' border="0" /></a>';
					$multiChkBox = 2;
					$multiMove = 1;
				//} elseif ($conf['csite'] != 'groups' && ($USER->hasPerm('users_show_group') && $USER->hasPerm($conf['csite'] .'_edit'))) {
				} elseif ($conf['csite'] != 'groups' && $USER->hasPerm('users_show_group') && $USER->hasPerm($conf['csite'] .'_edit') && ($USER->hasPerm($conf['csite'] .'_edit_group, '. $conf['csite'] .'_edit') && $conf['csite'] != 'users' || $USER->hasPerm($conf['csite'] .'_admin'))) {
					$moveContent = '&nbsp;';
					$multiChkBox = 1;
				} else {
					$moveContent = false;
				}
				if ($moveContent) {
					$markerArray['cell_class'] = $conf['csite'] .'-cell-move';
					$markerArray['cell'] = $moveContent;
					$subpartSubContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartSingleCell, $markerArray, '###|###', '1');
				}

				// When allowed to delete
				if ($allowDelete1 && $allowDelete2) {
					//$preDeleteJS = '';
					$preDelete = 'delete'. lib_div::firstUpper($conf['csite'], true) .'(\''. $row['id'].'\');';
					if ($conf['csite'] == 'templates' OR $conf['csite'] == 'domains') {
						$msgTrans = $LANG->getLang('delete'. lib_div::firstUpper($conf['csite'], true) .'JsConfirm', array('name' => $row['name']));
						$preDelete = "openConfirm('". lib_div::convertMsgForJS($msgTrans) ."', '". lib_div::slashJS($preDelete) ."', 'void(0);')";
					}
					$deleteContent = '<a href="javascript:void(0);" onclick="'. $preDelete .'"><img src="'. $icons['del']['path'] .'" '. $icons['edit']['size'][3] .' border="0" /></a>';
					$multiChkBox = 2;
					$multiDel = 1;
				} elseif ($USER->hasPerm($conf['csite'] .'_delete')) {
					$deleteContent = '&nbsp;';
					if ($multiChkBox < 1) $multiChkBox = 1;
				} else {
					$deleteContent = false;
				}
				if ($deleteContent) {
					$markerArray['cell_class'] = $conf['csite'] .'-cell-delete';
					$markerArray['cell'] = $deleteContent;
					$subpartSubContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartSingleCell, $markerArray, '###|###', '1');
				}
// $TBE_TEMPLATE->addMessage('debug', nl2br(print_r($conf['data'], true)));
				// Add checkbok for multi move / delete
				$multiChkBoxContent = '';
				if ($multiChkBox == 2) {
					$multiChkBoxContent = '<input type="checkbox" onclick="multiMvDelChkbUpdate(this, '. $multiMove .', '. $multiDel .');" name="mmvdel" value="'. $row['id'] .'" '. (lib_div::isset_value($conf, 'data=>id') && lib_div::inList($row['id'], $conf['data']['id']) ? 'checked' : '') .' class="list-chkb" />';
				} elseif($multiChkBox == 1) {
					$multiChkBoxContent = '&nbsp;';
				} else {
					$multiChkBoxContent = false;
				}
				if ($multiChkBoxContent) {
					$markerArray['cell_class'] = $conf['csite'] .'-cell-chkb';
					$markerArray['cell'] = $multiChkBoxContent;
					$subpartSubContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartSingleCell, $markerArray, '###|###', '1');
				}

				// Set row color num and substitute it
				$markerArray['color_num'] = $rowColor == '1' ? $rowColor = '2' : $rowColor = '1';
				$subpartSingle = $TBE_TEMPLATE->substituteMarkerArray($subpartSingleTemp, $markerArray, '###|###', '1');
				$markerArray = array();

				$subpartContent .= $TBE_TEMPLATE->substituteSubpart($subpartSingle, '###GENERAL_SHOW_SINGLE_CELL###', $subpartSubContent);

			}
			$lineCount++;
		}

		$markAllList = implode(',', $markAllArr);

		//$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($conf, true)));

		// Add multi delete image with JS confirmation message
		if ($USER->hasPerm($conf['csite'] .'_admin, '. $conf['csite'] .'_delete, '. $conf['csite'] .'_delete_group')) {
			$multiChkBox = true;
			$preDelete = 'delete'. lib_div::firstUpper($conf['csite'], true) .'();';
			if ($conf['csite'] == 'templates' OR $conf['csite'] == 'domains') {
				$msgTrans = $LANG->getLang('delete'. lib_div::firstUpper($conf['csite']) .'JsConfirm');
				//$preDelete = "openConfirm('". lib_div::convertMsgForJS($msgTrans) ."', '". lib_div::slashJS($preDelete) ."', 'void(0);')";
				$preDelete = "multiMvDelExecDel('". lib_div::convertMsgForJS($msgTrans) ."', '". lib_div::slashJS($preDelete) ."')";
			}
			$markerArray['header_class'] = $conf['csite'] .'-cell-delete';
			$markerArray['header'] = '&nbsp;';
			$markerArray['header'] = '<a href="javascript:void(0);" onclick="'. $preDelete .'" style="display: none;" id="mdel"><img src="'. $icons['del_m']['path'] .'" '. $icons['del_m']['size'][3] .' border="0" /></a>';
			$headerSubpartContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartColHeader, $markerArray, '###|###', '1');
		}
		// Add mark all/none for multi move / delete
		if ($multiChkBox) {
			$markerArray['header_class'] = $conf['csite'] .'-cell-chkb';
			$markerArray['header'] = '<a href="javascript:void(0);" onclick="multiMvDelChkAllNone(\''. $markAllList .'\', '. $moveCount .', '. $delCount .');"><img src="'. $icons['mark_all']['path'] .'" '. $icons['mark_all']['size'][3] .' border="0" /></a>';
			$headerSubpartContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartColHeader, $markerArray, '###|###', '1');
		}

		// Substitute the headers
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###GENERAL_COL_HEADER###', $headerSubpartContent);
		$markerArray = array();

		$content .= $TBE_TEMPLATE->wrapInFormTags($TBE_TEMPLATE->substituteSubpart($subpart, '###GENERAL_SHOW_SINGLE###', $subpartContent), '', 'dummy');

		return $content;
	}



	/**
	 * Genrates user/id pairs for selection box options for delete options.
	 * Also adds message to position "0" of select box.
	 *
	 * @param	array		Configuration array
	 * @return	array		user/id pairs
	 */
	function getUsersOfGroup($conf) {
		global $LANG;
		$groupUsers = array();
		$sqlWhereArr = array();
		$id = $conf['data']['fieldValue'];
		$showAll = $conf['data']['showAll'];
		$excludeIds = $conf['data']['exclude'];

		if ($id == '0' && !$showAll) {
			$groupUsers[0]['value'] = '0';
			$groupUsers[0]['content'] = lib_div::htmlspecialchars_decode($LANG->getLang('selectGroupFirst'));
		} else {
			if (!($showAll && $id=='0')) $sqlWhereArr[] = 'grp_id='. lib_DB::fullQuoteStr($id);
			if ($excludeIds) $sqlWhereArr[] = 'id NOT IN ('. lib_db::fullQuoteStrList($excludeIds) .')';
			$sqlWhere = implode(' AND ', $sqlWhereArr);
			$groupUsers = $GLOBALS['TUPA_DB']->exec_SELECTgetRows('id, username', 'users', $sqlWhere, '', 'username', '');
			lib_div::stripSlashesOnArray($groupUsers);
			// lib_div::htmlspecialcharOnArray($groupUsers);
			if (count($groupUsers) == '0') {
				$groupUsers = array();
				$groupUsers[0]['value'] = '0';
			 	$groupUsers[0]['content'] = lib_div::htmlspecialchars_decode($LANG->getLang('selectNoUsers'));
			} else {
				array_unshift($groupUsers, array('value' => '0', 'content' => lib_div::htmlspecialchars_decode($LANG->getLang('selectUser'))));
			}
		}

		return $groupUsers;
	}




	/**
	 * Genrates IP/id pairs for selection box options.
	 * Also adds message to position "0" of select box.
	 *
	 * @param	array		Configuration array
	 * @return	array		content/id pairs
	 */
	function getIpsOfType($conf) {
		global $LANG;
		$groupUsers = array();
		$values = array();

		switch ($conf['data']['type']) {
			case 'IPv4':
			default:
				$getType = 'A'; break;
			case 'IPv6':
				$getType = 'AAAA'; break;
		}

		$content = $GLOBALS['TUPA_DB']->exec_SELECTgetRows('content', 'records', 'type=\''. $getType .'\'', 'content', '', '');
		lib_div::stripSlashesOnArray($content);

		foreach ($content as $value) {
			$values[] = $value['content'];
		}

		// Sort the values natural
		natsort($values);

		return $values;
	}

}

?>