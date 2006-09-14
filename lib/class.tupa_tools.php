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
 * Tools functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_tools {

	/**
	 * Generates form to edit user permissions.
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function showTools($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;

		// Check permissions
		if (!$USER->hasPerm('tools_show')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage('tools', 'show', 'permission', 'logMsgNoPermission');
			return;
		}

		$markerArray = lib_div::setDefaultMarkerArray();

		$hiddenFields = '';

		$content = $TBE_TEMPLATE->header($LANG->getLang('toolsTitle'));

		$content .= $TBE_TEMPLATE->genMessageField();

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'tools.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###TOOLS###');

		$markerArray['tools_ipchange_subtitle'] = $LANG->getLang('toolsIpChangeSubtitle');
		$markerArray['tools_lang_installed_subtitle'] = $LANG->getLang('toolsLangInstalledSubtitle');
		$markerArray['tools_lang_available_subtitle'] = $LANG->getLang('toolsLangAvailableSubtitle');
		$markerArray['button_get_langs'] = '<input type="button" name="getLang" onclick="getLangMgrUpdate(1);" value="'. $LANG->getLang('toolsLangButtonGet') .'" class="'. STYLE_BUTTON .'" />';
		$markerArray['tools_skin_installed_subtitle'] = $LANG->getLang('toolsSkinInstalledSubtitle');
		$markerArray['tools_skin_available_subtitle'] = $LANG->getLang('toolsSkinAvailableSubtitle');
		$markerArray['button_get_skins'] = '<input type="button" name="getSkins" onclick="getSkinMgrUpdate(1);" value="'. $LANG->getLang('toolsSkinButtonGet') .'" class="'. STYLE_BUTTON .'" />';

		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');

		// Get additonal subpart (IP change)
		$subpartTmp = $TBE_TEMPLATE->getSubpart($subpart, '###TOOLS_IPCHANGE###');
		$subpartContent = tupa_tools::showIpChange($conf, $subpartTmp);
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###TOOLS_IPCHANGE###', $subpartContent);

		// Get additonal subpart (Languages)
//		$subpartTmp = $TBE_TEMPLATE->getSubpart($subpart, '###TOOLS_LANG_MANAGER###');
//		$subpartContent = tupa_tools::getLangMgrUpdate($conf, $subpartTmp);
//		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###TOOLS_LANG_MANAGER###', $subpartContent);

		// Get additonal subpart (Skins)
//		$subpartTmp = $TBE_TEMPLATE->getSubpart($subpart, '###TOOLS_SKIN_MANAGER###');
//		$subpartContent = tupa_tools::getSkinMgrUpdate($conf, $subpartTmp);
//		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###TOOLS_SKIN_MANAGER###', $subpartContent);

		$content .= $subpart;

		return $content;
	}



	/**
	 * Gets actual languages from server and creates list of installed an available languages
	 *
	 * @param	array		Configuration array
	 * @return	string		messages
	 */
	function showIpChange($conf, $subpart='') {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		// Check permissions
		if (!$USER->hasPerm('ipchange_show')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage('ipchange', 'show', 'permission', 'logMsgNoPermission');
			return;
		}

		$ipSelectOptions = $TBE_TEMPLATE->ipSelectOptions();
		$ipTypeSelectOptions = $TBE_TEMPLATE->ipTypeSelectOptions();
		$groupSelectOptions = '<option value="" selected>'. $LANG->getLang('selectNoGroups') .'</option>'. $TBE_TEMPLATE->groupSelectOptions('', '', 'selectAllGroups');
		$userSelectOptions = '<option value="" selected>'. $LANG->getLang('selectNoUsers') .'</option>'. $TBE_TEMPLATE->userSelectOptions('', '', '', 'selectAllUsers');

		$markerArray['label_change_ip_type'] = $LANG->getLang('labelChangeIpType');
		$markerArray['label_change_src_ip'] = $LANG->getLang('labelChangeSrcIp');
		$markerArray['label_change_dst_ip'] = $LANG->getLang('labelChangeDstIp');
		$markerArray['label_only_from_grp_usr'] = $LANG->getLang('labelChangeOnlyFromGrpUsr');

		$markerArray['select_change_ip_type'] = '<select name="ip_type" onchange="getIpsOfType(\'formdata\', \'src_ip\', this.value); eval(\'d.formdata.dst_ip\').setAttribute(\'pattern\', eval(\'regex\' + this.value));" class="'. STYLE_FIELD .'">'. $ipTypeSelectOptions .'</select>';
		$markerArray['select_change_src_ip'] = '<select name="src_ip" size="8" alt="selectm|1|*" emsg="'. $LANG->getLang('selectMin1IpError') .'" class="'. STYLE_FIELD .'" multiple="multiple">'. $ipSelectOptions .'</select>'. $LANG->getHelp('helpToolsIpChangeSrcIp');
		$markerArray['input_change_dst_ip'] = '<input type="text" name="dst_ip" class="'. STYLE_FIELD .'" size="30" alt="custom" pattern="'. $TUPA_CONF_VARS['REGEX']['IPv4'] .'" emsg="'. $LANG->getLang('ipError') .'" value="" />'. $LANG->getHelp('helpToolsIpChangeDstIp');
		$markerArray['select_only_from_grp_usr'] = '<select name="grp" size="8" alt="selectm|1|*" emsg="'. htmlspecialchars($LANG->getLang('selectMin1AllGroupError')) .'" class="'. STYLE_FIELD .'" multiple="multiple">'. $groupSelectOptions .'</select>&nbsp;<select name="usr" size="8" alt="selectm|1|*" emsg="'. htmlspecialchars($LANG->getLang('selectMin1AllUserError')) .'" class="'. STYLE_FIELD .'" multiple="multiple">'. $userSelectOptions .'</select>'. $LANG->getHelp('helpToolsIpChangeGrpUsr');

		$markerArray['exec_ipchange'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('toolsIpChangeButtonExec') .'" class="'. STYLE_BUTTON .'" />';

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');

		$subpart = $TBE_TEMPLATE->wrapInFormTags($subpart, 'processIpChange(document.forms[0]);');

		return $subpart;
	}



	/**
	 * Changes the IP
	 *
	 * @param	array		config file content
	 * @return	void
	 */
	function processIpChange($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		// Check permissions
		if (!$USER->hasPerm('ipchange_show')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage('ipchange', 'execute', 'permission', 'logMsgNoPermission');
			return;
		}

		// debug($conf);

		$fd = $conf['formdata']['data'];
		$userIds = array();

		switch ($fd['ip_type']) {
			case 'IPv4':
				$recordType = 'A'; break;
			case 'IPv6':
				$recordType = 'AAAA'; break;
			default:
				$TBE_TEMPLATE->addMessage('error', $LANG->getLang('selectIpTypeError'));
				return;
		}

		// Check some submitted values
		if ($fd['dst_ip'] != '' && !preg_match('/'. $TUPA_CONF_VARS['REGEX'][$fd['ip_type']] .'/', $fd['dst_ip'])) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('ipError'));
			return;
		}



		// If no or all groups is selected, only one item can be selected
		if (count($fd['grp']) > 1 && (in_array('', $fd['grp']) || in_array(0, $fd['grp']))) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('selectNoAllGrpError'));
			return;
		}

		// Same for users
		if (count($fd['usr']) > 1 && (in_array('', $fd['usr']) || in_array(0, $fd['usr']))) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('selectNoAllUsrError'));
			return;
		}

		// Message if no group and no user was selected
		if ($fd['grp'][0] == '' && $fd['usr'][0] == '') {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('selectNoGrpUsrError'));
			return;
		}


		// Get all affected user id's from selected groups
		if (in_array(0, $fd['grp']) && (in_array('', $fd['usr']) || in_array(0, $fd['usr'])) || in_array('', $fd['grp']) && in_array(0, $fd['usr'])) {
			// Get all groups and add the users to list
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'groups', '');
			while ($row = $GLOBALS['TUPA_DB']->sql_fetch_row($res)) {
				$userIds[] = $GLOBALS['TUPA_DB']->exec_SELECTgetUserIdsOfGroup($row[0]);
			}
		} else {
			if (max($fd['grp']) > 0 && (in_array('', $fd['usr']) || in_array(0, $fd['usr'])) || max($fd['grp']) > 0 && max($fd['usr']) > 0) {
				// Get all users of selected groups
				foreach ($fd['grp'] as $grpId) {
					$userIds[] = $GLOBALS['TUPA_DB']->exec_SELECTgetUserIdsOfGroup($grpId);
				}
			}
			if (max($fd['usr']) > 0 && (in_array('', $fd['grp']) || in_array(0, $fd['grp'])) || max($fd['grp']) > 0 && max($fd['usr']) > 0) {
				// Get all selected users
				foreach ($fd['usr'] as $usrId) {
					$userIds[] = $usrId;
				}
			}
		}

		// Now we have all user id to change the IP's - Merge them to one long unique list for SQL query
		$userIds = $GLOBALS['TUPA_DB']->fullQuoteStrList(lib_div::uniqueList(implode(',', $userIds)));

		// To support MySQL 3.23 we have to get all domain id's to update (3.23 does not support update on multiple tables)
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery(
			'dom_id',
			'domain_owners',
			'usr_id IN ('. $userIds .')'
		);
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
			lib_logging::addLogMessage('ipchange', 'execute', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return;
		}
		$domainIds = array();
		while ($row = $GLOBALS['TUPA_DB']->sql_fetch_row($res)) {
			$domainIds[] = $row[0];
		}
		$domainIds = $GLOBALS['TUPA_DB']->fullQuoteStrList(implode(',', $domainIds));


		// Execute SQL query (affects only A or AAAA records of domains)
		$updateFields = array(
			'content' => $fd['dst_ip']
		);
		$srcList = $GLOBALS['TUPA_DB']->fullQuoteStrList(implode(',', $fd['src_ip']));

		$sqlWhere = 'content IN ('. $srcList .')
			AND type=\''. $recordType .'\'
			AND domain_id IN ('. $domainIds .')';

//		debug ($sqlWhere);

		$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('records', $sqlWhere, $updateFields);
		if (mysql_error()) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
			lib_logging::addLogMessage('ipchange', 'execute', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			return;
		}

		$affected = $GLOBALS['TUPA_DB']->sql_Affected_rows();

		if ($affected) {
			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('toolsIpChangeSuccess', array('recordsCount' => $affected)), 'getIpsOfType(\'formdata\', \'src_ip\', d.formdata.ip_type.value);');
			lib_logging::addLogMessage('ipchange', 'execute', 'info', 'logMsgToolsIpChangeSuccess', array('recordsCount' => $affected, 'srcIPs' => $srcList, 'dstIP' => $fd['dst_ip']));
		} else {
			$TBE_TEMPLATE->addMessage('success', $LANG->getLang('toolsIpChangeNothingChanged'));
		}
	}

}
?>