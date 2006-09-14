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
 * Everithing needed to show logging messages
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_logging {

	/**
	 * Shows logging messages.
	 *
	 * @return 	string
	 */
	function showLogMessages($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG, $jsCalendar;

		if (!$USER->hasPerm('logging_show')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$content = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		// execute log maintenance
		lib_logging::logMaintenance();

		$groupSelectOptions = $TBE_TEMPLATE->groupSelectOptions();
		$userSelectOptions = $TBE_TEMPLATE->userSelectOptions();
		$partSelectOptions = $TBE_TEMPLATE->logPartSelectOptions();
		$maxSelectOptions = $TBE_TEMPLATE->logMaxSelectOptions($TUPA_CONF_VARS['LOGGING']['itemAmount']);
		$typeSelectOptions = $TBE_TEMPLATE->logTypeSelectOptions();
		$refreshSelectOptions = $TBE_TEMPLATE->logRefreshSelectOptions();


		// Get lowest and highest years
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('MIN(tstamp)', 'logging', '', '', '', '');
		$minYear = date("Y", mysql_result($res, 0));
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('MAX(tstamp)', 'logging', '', '', '', '');
		$maxYear = date("Y", mysql_result($res, 0));


		$content .= $TBE_TEMPLATE->header($LANG->getLang('logShowTitle'));

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'logging.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###LOGGING_SHOW_FILTER###');

		$markerArray['log_filter_subtitle'] = $LANG->getLang('logFilterSubtitle');

		$markerArray['label_filter_group'] = $LANG->getLang('labelGroup');
		$markerArray['select_filter_group'] = '<select name="filter_group" class="'. STYLE_FIELD .'" onchange="getUsersOfGroup(this.form.name, \'filter_user\', this.value, true)" >' . $groupSelectOptions .'</select>';
		$markerArray['label_filter_user'] = $LANG->getLang('labelUser');
		$markerArray['select_filter_user'] = '<select name="filter_user" class="'. STYLE_FIELD .'">' . $userSelectOptions .'</select>';
		$markerArray['label_filter_part'] = $LANG->getLang('labelPart');
		$markerArray['select_filter_part'] = '<select name="filter_part" class="'. STYLE_FIELD .'">'. $partSelectOptions .'</select>';
		$markerArray['label_filter_type'] = $LANG->getLang('labelType');
		$markerArray['select_filter_type'] = '<select name="filter_type" class="'. STYLE_FIELD .'">'. $typeSelectOptions .'</select>';
		$markerArray['label_filter_date'] = $LANG->getLang('labelDate');
		$markerArray['input_filter_date'] = $jsCalendar->make_input_field(
			array(	'firstDay'       => 1,
				'step'	=> 1,
				'showOthers'     => true,
				'ifFormat'       => DATE_FORMAT,
				'align'	=> 'br',
				'range'	=> array($minYear,$maxYear),
				'timeFormat'     => '24'
			),
			array(	'style'       => '',
				'size'	   => '15',
				'class'	   => STYLE_FIELD,
				'name'        => 'filter_date',
				'value'       => ''
			)
		);

		$markerArray['label_filter_max'] = $LANG->getLang('labelLoggingMax');
		$markerArray['select_filter_max'] = '<select name="filter_max" class="'. STYLE_FIELD .'">'. $maxSelectOptions .'</select>';
		$markerArray['label_filter_message'] = $LANG->getLang('labelLoggingMessage');
		$markerArray['input_filter_message'] = '<input name="filter_message" class="'. STYLE_FIELD .'" />';
		$markerArray['label_refresh'] = $LANG->getLang('labelLoggingRefresh');
		$markerArray['select_refresh'] = '<select name="refresh" onchange="setLogRefresh(this.value);" class="'. STYLE_FIELD .'">'. $refreshSelectOptions .'</select>';

		$markerArray['filter_submit'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('logFilterSubmit') .'" class="'. STYLE_BUTTON .'" />';

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$subpart = $TBE_TEMPLATE->wrapInFormTags($subpart, 'setConf(\'show=>page\', \'1\', true); updateLogMessages(d.forms[0]);');
		$content .= $TBE_TEMPLATE->wrapInDiv('filter-form', $subpart);

		$content .= $TBE_TEMPLATE->wrapInDiv('div-show', tupa_logging::updateLogMessages($conf));

		return $content;
	}


	/**
	 * Updates logging messages.
	 *
	 * @return 	string
	 */
	function updateLogMessages($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		if (!$USER->hasPerm('logging_show')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$fd = isset($conf['formdata']) ? $conf['formdata'] : array();
		$content = '';
		$logData = '';
		$sqlWhere = '';
		$markerArray = lib_div::setDefaultMarkerArray();

		// Set some default values
		if (!lib_div::isset_value($conf, 'show=>page')) $conf['show']['page'] = '1';

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'logging.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###LOGGING_SHOW###');
		// Substitute global markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$markerArray = array();

		// Get some subparts and remove some of them
		$subpartDate = $TBE_TEMPLATE->getSubpart($subpart, '###LOGGING_SHOW_DATE###');
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###LOGGING_SHOW_DATE###', '');
		$subpartColTitles = $TBE_TEMPLATE->getSubpart($subpart, '###LOGGING_SHOW_COL_TITLES###');
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###LOGGING_SHOW_COL_TITLES###', '');
		$subpartSingle = $TBE_TEMPLATE->getSubpart($subpart, '###LOGGING_SHOW_SINGLE###');

		$markerArray['log_col_time'] = $LANG->getLang('logColTime');
		$markerArray['log_col_user'] = $LANG->getLang('logColUser');
		$markerArray['log_col_part'] = $LANG->getLang('logColPart');
		$markerArray['log_col_action'] = $LANG->getLang('logColAction');
		$markerArray['log_col_type'] = $LANG->getLang('logColType');
		$markerArray['log_col_message'] = $LANG->getLang('logColMessage');
		// Substitute the markes on col-titles subpart
		$subpartColTitles = $TBE_TEMPLATE->substituteMarkerArray($subpartColTitles, $markerArray, '###|###', '1');
		$markerArray = array();

		// Set filter values
		if (isset($fd['data'])) {
//			$TBE_TEMPLATE->addMessage('debug', print_r($fd['data'], true));
			$sqlWhereArr = array();
			$fData = $fd['data'];
			if (!isset($fData['filter_user']) || isset($fData['filter_user']) && !$fData['filter_user'] > '0') {
				if (isset($fData['filter_group']) && $fData['filter_group'] > '0') {
					$userIdList = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'users', 'grp_id', $fData['filter_group']);
					$sqlWhereArr[] = 'usr_id IN ('. lib_DB::fullQuoteStrList($userIdList['list']) .')';
				}
			}
			// set date filter
			if (isset($fData['filter_date']) && $fData['filter_date'] > '') {
				// Split submitted date to make a timestamp
				$splittedDate = explode($LANG->getLang('dateSplitter'), $fData['filter_date']);
				$splittedDateFormat = explode(' ', $LANG->getLang('dateFormat'));
				for ($i='0'; $i<count($splittedDateFormat); $i++) {
					switch ($splittedDateFormat[$i]) {
						case 'Y':
						case 'y':
							$dateYear = $splittedDate[$i];
							break;
						case 'm':
							$dateMonth = $splittedDate[$i];
							break;
						case 'd':
							$dateDay = $splittedDate[$i];
							break;
					}
				}
				$sqlWhereArr[] = 'tstamp>='. lib_DB::fullQuoteStr(mktime(0, 0, 0, $dateMonth, $dateDay, $dateYear));
				$sqlWhereArr[] = 'tstamp<='. lib_DB::fullQuoteStr(mktime(23, 59, 59, $dateMonth, $dateDay, $dateYear));
			}
			isset($fData['filter_user']) && $fData['filter_user'] > '0' ? $sqlWhereArr[] = 'usr_id='. lib_DB::fullQuoteStr($fData['filter_user']) : '';
			isset($fData['filter_part']) && $fData['filter_part'] > '0' ? $sqlWhereArr[] = 'part='. lib_DB::fullQuoteStr($fData['filter_part']) : '';
			isset($fData['filter_type']) && $fData['filter_type'] > '0' ? $sqlWhereArr[] = 'type='. lib_DB::fullQuoteStr($fData['filter_type']) : '';
			isset($fData['filter_message']) && $fData['filter_message'] != '' ? $sqlWhereArr[] = 'message_repl LIKE '. lib_DB::fullQuoteStr('%'. $fData['filter_message'] .'%') : '';

			$sqlWhere = implode(' AND ', $sqlWhereArr);
		}
		//$TBE_TEMPLATE->addMessage('debug', $sqlWhere);
		isset($fData['filter_max']) && $fData['filter_max'] > '0' ? $linesPerSite = $fData['filter_max'] : $linesPerSite = $TUPA_CONF_VARS['LOGGING']['itemAmount'];

		// Calculate start position
		$mysqlStart =  $linesPerSite * $conf['show']['page'] - $linesPerSite;

		// Now get the data
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'logging', $sqlWhere, '', 'tstamp DESC', $mysqlStart .','. $linesPerSite);
		lib_div::stripSlashesOnArray($res);
		lib_div::htmlspecialcharOnArray($res);
		$totalLines = mysql_num_rows($GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'logging', $sqlWhere, '', '', ''));

		// Add navigation
		$markerArray['navigation'] = $TBE_TEMPLATE->genNavigation($conf, $totalLines, $linesPerSite, $TUPA_CONF_VARS['PREFS']['naviShowPages']);
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
		$markerArray = array();

		$lastDate = '';
		while ($row = mysql_fetch_assoc($res)) {
			$currentDate = date("Ymd", $row['tstamp']);
			if ($currentDate != $lastDate) {
				$markerArray['log_date'] = strftime(DATE_FORMAT, $row['tstamp']);
				$logData .= $TBE_TEMPLATE->substituteMarkerArray($subpartDate, $markerArray, '###|###', '1');
				$logData .= $subpartColTitles;
				$lastDate = $currentDate;
			}
			$markerArray = array();
			// Set user id to '' if it is zero in database. zeros are from login page where is no user id yet.
			$user = $row['usr_id'] == '0' ? '' : $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($row['usr_id']);

			// Set type and color
			$type = '<span class="log-'. $row['type'] .'">'. $LANG->getLang('logType'. lib_div::firstUpper($row['type'])) .'</span>';

			$markerArray['log_time'] = strftime(TIME_FORMAT, $row['tstamp']);
			$markerArray['log_user'] = $user;
			$markerArray['log_part'] = $LANG->getLang('logPart'. lib_div::firstUpper($row['part']));
			$markerArray['log_action'] = $LANG->getLang('logAction'. lib_div::firstUpper($row['action']));
			$markerArray['log_type'] = $type;
			$markerArray['log_message'] = $LANG->getLang($row['message'], unserialize($row['message_repl']));

			$logData .= $TBE_TEMPLATE->substituteMarkerArray($subpartSingle, $markerArray, '###|###', '1');
		}

		$content .= $TBE_TEMPLATE->substituteSubpart($subpart, '###LOGGING_SHOW_SINGLE###', $logData);

		return $content;
	}


}
?>