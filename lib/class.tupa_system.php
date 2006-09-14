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
 * System functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_system {

	/**
	 * Shows system configuration tools
	 *
	 * @return	void
	 */
	function showSystemConfig($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;

		// Check permissions
		if (!$USER->hasPerm('tools_show')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage('tools', 'show', 'permission', 'logMsgNoPermission');
			return;
		}

		$markerArray = lib_div::setDefaultMarkerArray();

		$content = $TBE_TEMPLATE->header($LANG->getLang('toolsTitle'));

		$content .= $TBE_TEMPLATE->genMessageField();

//	$content .= tupa_system::genSystemDefConfigForm();

		// Get the template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'system.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###SYSTEM###');

		$markerArray['system_config_subtitle'] = $LANG->getLang('systemConfigSubtitle');

		$markerArray['tools_lang_installed_subtitle'] = $LANG->getLang('systemLangInstalledSubtitle');
		$markerArray['tools_lang_available_subtitle'] = $LANG->getLang('systemLangAvailableSubtitle');
		$markerArray['button_get_langs'] = '<input type="button" name="getLang" onclick="getLangMgrUpdate(1);" value="'. $LANG->getLang('systemLangButtonGet') .'" class="'. STYLE_BUTTON .'" />';
		$markerArray['tools_skin_installed_subtitle'] = $LANG->getLang('systemSkinInstalledSubtitle');
		$markerArray['tools_skin_available_subtitle'] = $LANG->getLang('systemSkinAvailableSubtitle');
		$markerArray['button_get_skins'] = '<input type="button" name="getSkins" onclick="getSkinMgrUpdate(1);" value="'. $LANG->getLang('systemSkinButtonGet') .'" class="'. STYLE_BUTTON .'" />';

		// Substitute markers
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');

		// Get additonal subpart (System configuration)
		$subpartTmp = $TBE_TEMPLATE->getSubpart($subpart, '###SYSTEM_CONFIG###');
		$subpartContent = $TBE_TEMPLATE->wrapInFormTags(tupa_system::showSystemDefConfigForm($conf, $subpartTmp), 'processSystemConfig(document.forms[0]);');
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###SYSTEM_CONFIG###', $subpartContent);

		// Get additonal subpart (Languages)
		$subpartTmp = $TBE_TEMPLATE->getSubpart($subpart, '###SYSTEM_LANG_MANAGER###');
		$subpartContent = tupa_system::getLangMgrUpdate($conf, $subpartTmp);
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###SYSTEM_LANG_MANAGER###', $subpartContent);

		// Get additonal subpart (Skins)
		$subpartTmp = $TBE_TEMPLATE->getSubpart($subpart, '###SYSTEM_SKIN_MANAGER###');
		$subpartContent = tupa_system::getSkinMgrUpdate($conf, $subpartTmp);
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###SYSTEM_SKIN_MANAGER###', $subpartContent);

		$content .= $subpart;

		return $content;
	}



	/**
	 * Generates the system configuration page
	 *
	 * @return	void
	 */
	function showSystemDefConfigForm($conf, $subpart) {
		global $TBE_TEMPLATE, $LANG;

		$defConfgContent = lib_div::getFileContent(PATH_lib .'config_default.php');
		$commentArr = tupa_system::getDefaultConfigArrayComments($defConfgContent);
		$hiddenFields = '';
		$partCount = 0;
		$partSelectOptions = '';

		// Get subpart for single groups/items
		$subpartHeader = $TBE_TEMPLATE->getSubpart($subpart, '###HEADER###');
		$subpartSingle = $TBE_TEMPLATE->getSubpart($subpart, '###CONTENT###');

		//debug ($commentArr);

		reset($GLOBALS['TUPA_CONF_VARS']);

		$subpartContent = '';
		while(list($k,$va) = each($GLOBALS['TUPA_CONF_VARS'])) {
			if (!array_key_exists($k, $commentArr[0])) continue;

			$ext = '['. $k .']';

			// Starting DIV tag
			$subpartContent .= '<div id="systemConfigPart_'. $partCount .'" style="display: none;">';

			// Add header
			$markerArray = array();
			$markerArray['part_header'] = '$TUPA_CONF_VARS[\''. $k. '\']';
			$markerArray['part_content'] = $commentArr[0][$k] .'<br /><br />';

			$subpartContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartHeader, $markerArray, '###|###', 1);

			while(list($vk, $value) = each($va)) {
				if (!is_array($value) && !lib_div::hasLinebreak($value)) {
					if (!array_key_exists($vk, $commentArr[1][$k])) continue;
					$k2 = '['. $vk .']';

					// Split the validation part from the comment
					$cSplit = lib_div::trimExplode('|*|', $commentArr[1][$k][$vk]);

//					$msg = $cSplit[2] .'<br /><br />'. $ext . $k2 .' = '. htmlspecialchars($value) .'<br />';
					$msg = $cSplit[2] .'<br /><br />';

					// Get the fValidate check (if has one)
					$fValidate = '';
					if ($cSplit[1]) {
						$fV = lib_div::trimExplode('||', $cSplit[1]);
						$fValidate = 'alt="'. $fV[0] .'"';

						if (array_key_exists(1, $fV)) {
							$fValidate .= ' pattern="'. $fV[1] .'"';
						}
					}

					switch ($cSplit[0]) {
						case 'BOOLEAN':	// Checkbox
							$msg .= '<input type="checkbox" name="'. $k .'_'. $vk .'"'. ($value ? ' checked' : '') .' value="1" class="field">';
							break;
						case 'NUMBER':	// Text field validated to be a number
						case 'STRING':		// Or just text
							$msg .= '<input type="text" size="30" name="'. $k .'_'. $vk .'" value="'. htmlspecialchars($value) .'" '. $fValidate .' class="field">';
							break;
						case 'SELECTOR':	// Select box
							$selectOptions = '';
							$kvk = $k .'=>'. $vk;
							switch ($kvk) {
								case 'SYS=>langDefault':
									$selectOptions = $TBE_TEMPLATE->languageSelectOptions($value); break;
								case 'SKINS=>skinDefault':
									$selectOptions = '<option value="">&nbsp;</option>'. $TBE_TEMPLATE->skinSelectOptions($value); break;
								case 'PREFS=>defDisplayHelp':
									$selectOptions = '<option value="">&nbsp;</option>'. $TBE_TEMPLATE->displayHelpSelectOptions($value); break;
								case 'LOGGING=>defItemAmount':
									$selectOptions = '<option value="">&nbsp;</option>'. $TBE_TEMPLATE->logMaxSelectOptions($value); break;
							}

							$msg .= '<select name="'. $k .'_'. $vk .'" '. $fValidate .' class="'. STYLE_FIELD .'">'. $selectOptions .'</select>';
							break;
						default:	// If noone matches skip it
							continue;
							break;
					}

					$markerArray = array();
					$markerArray['part_header'] = $k2;
					$markerArray['part_content'] = $msg;

					$subpartContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartSingle, $markerArray, '###|###', 1);
				}
			}

			// Ending DIV tag
			$subpartContent .= '</div>';
			$partSelectOptions .= '<option value="'. $partCount .'">'. $k .'</option>';
			$partCount++;
		}

		$markerArray = array();
		$markerArray['part_selector'] = '<select name="part_selector" onchange="showLayer(\'systemConfigPart_\', this.selectedIndex, '. $partCount .');" class="'. STYLE_FIELD .'">' . $partSelectOptions .'</select><br /><br />';
		$markerArray['save_config'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('systemConfigButtonSave') .'" class="'. STYLE_BUTTON .'" /><br /><br />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Check if config file is writable
		$configFile = PATH_config .'config_site.inc.php';
		if (!@is_writable($configFile)) {
			$markerArray['save_config'] = '<br /><br />';
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('configFileNotWritable', array('file' => $configFile)));
		}

		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', 1);

		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###SINGLE###', $subpartContent);

		// Show 1st layer
		$TBE_TEMPLATE->addMessage('', '', 'showLayer(\'systemConfigPart_\', 0, '. $partCount .');');

		return $subpart;
	}



	/**
	 * Gets comments and additional options of config file
	 *
	 * @param	string		config file content
	 * @param	[type]		$mainArray: ...
	 * @param	[type]		$commentArray: ...
	 * @return	[type]		...
	 */
	function getDefaultConfigArrayComments($string, $mainArray=array(), $commentArray=array()) {
		$lines = explode(chr(10), $string);
		$in = 0;
		$mainKey = '';

		$parseParts = array('SYS', 'DNS', 'SKINS', 'PREFS', 'DOMAINS', 'LOGGING', 'SYSINFO', 'CRON');

		while(list(,$lc) = each($lines)) {
			$lc = trim($lc);
			if ($in) {
				if (!strcmp($lc,');')) {
					$in = 0;
				} else {
					if (eregi('["\']([[:alnum:]_-]*)["\'][[:space:]]*=>(.*)', $lc, $reg)) {
						// skip if no comment on this line
						if (!strpos($reg[2], '//')) continue;
						list(,$theComment) = explode('//', $reg[2], 2);
						if (substr(strtolower(trim($reg[2])), 0, 5) == 'array' && !strcmp($reg[1], strtoupper($reg[1]))) {
							$mainKey = trim($reg[1]);
							// Only add key if it is in the $parseParts array
							if (in_array($mainKey, $parseParts)) {
								$mainArray[$mainKey] = trim($theComment);
							}
						} elseif ($mainKey && in_array($mainKey, $parseParts)) {
							// Only add key/value if it has multiple parts splitted by |*|
							if (strpos($theComment, '|*|')) {
								$commentArray[$mainKey][$reg[1]] = trim($theComment);
							}
						}
					} else {
						//debug($lc,1);
					}
				}
			}
			if (!strcmp($lc, '$TUPA_CONF_VARS = array(')) {
				$in=1;
			}
		}
		return array($mainArray, $commentArray);
	}




	/**
	 * Saves the system configuration settings
	 *
	 * @param	array		config file content
	 * @return	void
	 */
	function processSystemConfig($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		// Check permissions
		if (!$USER->hasPerm('systemConfig_show')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage('systemConfig', 'execute', 'store', 'logMsgNoPermission');
			return;
		}

		// Check if config file is writable
		$configFile = PATH_config .'config_site.inc.php';
		if (!@is_writable($configFile)) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('configFileNotWritable', array('file' => $configFile)));
			return;
		}

		$fd = $conf['formdata']['data'];

//		debug($fd);

		// Unset part selector
		unset($fd['part_selector']);

		// Load config
		$CONFIG = lib_div::makeInstance('lib_config');
		$siteConfig = $CONFIG->writeToConfig_control();

		foreach ($fd as $key => $value) {
			$key = lib_div::trimExplode('_', $key);

			if ($TUPA_CONF_VARS[$key[0]][$key[1]] != $value) {
				//debug($key[1] .'='. $value .' ('. $TUPA_CONF_VARS[$key[0]][$key[1]].')');
				// Set config value
				$CONFIG->setValueInConfigFile($siteConfig, '$TUPA_CONF_VARS[\''. $key[0] .'\'][\''. $key[1] .'\']', $value);
			}
		}

		// Write config back to file
		if ($CONFIG->writeToConfig_control($siteConfig) == 'nochange') {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('configFileNotChangedSimple'));
			return;
		}

		$TBE_TEMPLATE->addMessage('success', $LANG->getLang('configFileSaveSuccess'));
		lib_logging::addLogMessage('systemConfig', 'store', 'info', 'logMsgConfigFileSaveSuccess');
	}




	/**
	 * Gets actual languages from server and creates list of installed an available languages
	 *
	 * @param	array		Configuration array
	 * @return	string		messages
	 */
	function getLangMgrUpdate($conf, $subpart='') {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		// Check permissions
		if (!$USER->hasPerm('languages_show')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage('languages', 'show', 'permission', 'logMsgNoPermission');
			return;
		}

		$removeComments = false;
		$rLangInfo = array();
		$getRemoteData = lib_div::isset_value($conf, 'data=>rInfo');

		// Get subpart from template if it was not submitted
		if (!$subpart) {
			$markerArray = lib_div::setDefaultMarkerArray();

			// Get the template
			$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'system.html'));
			// Get template subpart
			$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###SYSTEM_LANG_MANAGER###');

			$markerArray['tools_lang_installed_subtitle'] = $LANG->getLang('systemLangInstalledSubtitle');
			$markerArray['tools_lang_available_subtitle'] = $LANG->getLang('systemLangAvailableSubtitle');
			$markerArray['button_get_langs'] = '<input type="button" name="getLang" onclick="getLangMgrUpdate(1);" value="'. $LANG->getLang('systemLangButtonGet') .'" class="'. STYLE_BUTTON .'" />';

			// Substitute markers
			$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
			$removeComments = true;
		}

		// Get current language informations from server if button was clicked
		if ($getRemoteData && $conf['part'] == 'getLangMgrUpdate') {
			$rLangInfo = XMLParser::GetXMLTree($TUPA_CONF_VARS['SYS']['langMgrServerUrl']);
		}

		// Get the path and sizes of the used icons
		$icons = array();
		$icons['inst'] = lib_div::getImageInfo('icons/install.png');
		$icons['upd'] = lib_div::getImageInfo('icons/update.png');
		$icons['del'] = lib_div::getImageInfo('icons/garbage.png');

		// Get additonal subparts
		$subpartInstalled = $TBE_TEMPLATE->getSubpart($subpart, '###INSTALLED_LANG###');
		$subpartAvailable = $TBE_TEMPLATE->getSubpart($subpart, '###AVAILABLE_LANG###');

		// Get installed languages
		$subpartInstalledContent = '';
		$subpartAvailableContent = '';
		$rowColor = 1;

		foreach ($LANG->langArr as $lang) {
			$markerArray = array();
			$needUpdate = false;
			$rLangInfoTmp = array();

			// Get language info
			$lLangInfo = $LANG->getLanguageInfo($lang);

			if (lib_div::isset_value($rLangInfo, 'TTOOL_LANGUAGES=>0=>'. strtoupper($lang) .'=>0')) {
				$rLangInfoTmp = $rLangInfo['TTOOL_LANGUAGES'][0][strtoupper($lang)][0];
				if ($rLangInfoTmp['VERSION_TSTAMP'][0]['VALUE'] > $lLangInfo['LANGUAGE'][0]['VERSION_TSTAMP'][0]['VALUE']) $needUpdate = true;

				// Unset from remote array
				unset ($rLangInfo['TTOOL_LANGUAGES'][0][strtoupper($lang)]);
			}

			$langName = $lLangInfo['LANGUAGE'][0]['NAME_EN'][0]['VALUE'];
			$lLangInfoTmp = $lLangInfo['LANGUAGE'][0];

			// Prepare delete command / image
			$preDelete = 'deleteSysLang(\''. $lang.'\');';
			$msgTrans = $LANG->getLang('systemDeleteSysLangJsConfirm', array('name' => $langName));
			$preDelete = "openConfirm('". lib_div::convertMsgForJS($msgTrans) ."', '". lib_div::slashJS($preDelete) ."')";

			$markerArray['lang_name'] = $langName;
			$markerArray['lang_last_change'] = $lLangInfoTmp['VERSION_TSTAMP'][0]['VALUE'] ? strftime(DATE_FORMAT, $lLangInfoTmp['VERSION_TSTAMP'][0]['VALUE']) .' - '. strftime(TIME_FORMAT, $lLangInfoTmp['VERSION_TSTAMP'][0]['VALUE']) : $LANG->getLang('systemLangUpdatedNever');
			$markerArray['lang_last_remote_change'] = count($rLangInfoTmp) ? $rLangInfoTmp['VERSION_TSTAMP'][0]['VALUE'] ? strftime(DATE_FORMAT, $rLangInfoTmp['VERSION_TSTAMP'][0]['VALUE']) .' - '. strftime(TIME_FORMAT, $rLangInfoTmp['VERSION_TSTAMP'][0]['VALUE']) : $LANG->getLang('systemLangUpdatedNever') : '';
			$markerArray['lang_comp_version'] = $lLangInfoTmp['VERSION_CHECK'][0]['VALUE'];
			$markerArray['lang_update'] = $needUpdate ? '<a href="javascript:void(0);" onclick="updateSysLang(\''. $lang.'\');"><img src="'. $icons['upd']['path'] .'" '. $icons['upd']['size'][3] .' border="0" alt="'. $LANG->getLang('altUpdate') .'" /></a>' : '';
			$markerArray['lang_remove'] = $lang != $TUPA_CONF_VARS['SYS']['langFallback'] && $lang != $TUPA_CONF_VARS['SYS']['langDefault'] ? '<a href="javascript:void(0);" onclick="'. $preDelete .'"><img src="'. $icons['del']['path'] .'" '. $icons['del']['size'][3] .' border="0" alt="'. $LANG->getLang('altRemove') .'" /></a>' : '';
			$markerArray['row_class'] = 'table-row'. $rowColor;

			$subpartInstalledContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartInstalled, $markerArray, '###|###', '1');

			// Toggle row color
			$rowColor == 1 ? $rowColor = 2 : $rowColor = 1;
		}

		$markerArray = array();
		$markerArray['lang_name_title'] = $LANG->getLang('systemLangLanguageTitle');
		$markerArray['lang_last_change_title'] = $LANG->getLang('systemLangLastChangeTitle');
		$markerArray['lang_last_remote_change_title'] = $LANG->getLang('systemLangLastRemoteChangeTitle');
		$markerArray['lang_comp_version_title'] = $LANG->getLang('systemLangCompVersionTitle') . $LANG->getHelp('helpSystemLangUntilVersion');
		$markerArray['lang_comp_version_install_title'] = $LANG->getLang('systemLangCompVersionTitle') . $LANG->getHelp('helpSystemLangUntilVersionInstall');
		$markerArray['lang_install_title'] = $LANG->getLang('systemLangInstallTitle');
		$markerArray['lang_update_title'] = $LANG->getLang('systemLangUpdateTitle');
		$markerArray['lang_remove_title'] = $LANG->getLang('systemLangRemoveTitle');
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');

		if ($getRemoteData && $conf['part'] == 'getLangMgrUpdate') {
			$rowColor = 1;
			foreach ($rLangInfo['TTOOL_LANGUAGES'][0] as $key => $value) {
				$markerArray = array();
				$rLangInfoTmp = $rLangInfo['TTOOL_LANGUAGES'][0][$key][0];

				$markerArray['lang_name'] = $rLangInfoTmp['NAME_EN'][0]['VALUE'];
				$markerArray['lang_last_change'] = $rLangInfoTmp['VERSION_TSTAMP'][0]['VALUE'] ? strftime(DATE_FORMAT, $rLangInfoTmp['VERSION_TSTAMP'][0]['VALUE']) .' - '. strftime(TIME_FORMAT, $rLangInfoTmp['VERSION_TSTAMP'][0]['VALUE']) : $LANG->getLang('systemLangUpdatedNever');
				$markerArray['lang_comp_version'] = $rLangInfoTmp['VERSION_CHECK'][0]['VALUE'];
				$markerArray['lang_install'] = '<a href="javascript:void(0);" onclick="installSysLang(\''. strtolower($key).'\');"><img src="'. $icons['inst']['path'] .'" '. $icons['inst']['size'][3] .' border="0" alt="'. $LANG->getLang('altInstall') .'" /></a>';
				$markerArray['row_class'] = 'table-row'. $rowColor;
				$subpartAvailableContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartAvailable, $markerArray, '###|###', '1');

				// Toggle row color
				$rowColor == 1 ? $rowColor = 2 : $rowColor = 1;
			}
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###AVAILABLE_HEADERS###', '');
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###AVAILABLE_LANG###', '<tr><td>'. $LANG->getLang('systemLangAvailableGetMsg', array('buttonName'=>$LANG->getLang('systemLangButtonGet'))) .'</td></tr>');
		}

		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###INSTALLED_LANG###', $subpartInstalledContent);
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###AVAILABLE_LANG###', $subpartAvailableContent);

		// Remove any copmments and return
		return $removeComments ? lib_div::removeHtmlComments($subpart) : $subpart;
	}






	/**
	 * Delete lang/skin from server
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function deleteToolsItem($conf, $skipSuccessMsg=false, $cPart='') {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		$part = '';
		if(!$cPart) $cPart = $conf['part'];
		$id = $conf['data']['id'];

		switch ($cPart) {
			case 'deleteSysLang':
				$part = 'lang';
				$fullItemPath = PATH_lang . $id .'/';
				break;
			case 'deleteSysSkin':
				$part = 'skin';
				$fullItemPath = PATH_site . 'skins/'. $id .'/';
				break;
		}

		if (!$part) return;

		// Check permissions
		if (!$USER->hasPerm($part .'s_delete')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage($part .'s', 'delete', 'permission', 'logMsgNoPermission');
			return;
		}

		// Check if path to lang/skin really exists
		if (!@file_exists($fullItemPath)) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('system'. lib_div::firstUpper($part) .'DirNotExistsError', array('dir' => $fullItemPath)));
			lib_logging::addLogMessage($part .'s', 'delete', 'error', 'logMsgSystem'. lib_div::firstUpper($part) .'DirNotExistsError', array('dir' => $fullItemPath));
			return;
		}

		// Check if the directory and all its content is witeable (or deleteable in this case)
		if (!lib_div::checkDirContentWritable($fullItemPath)) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('system'. lib_div::firstUpper($part) .'DirNotRemovableError', array('dir' => $fullItemPath)));
			lib_logging::addLogMessage($part .'s', 'delete', 'error', 'logMsgSystem'. lib_div::firstUpper($part) .'DirNotRemovableError', array('dir' => $fullItemPath));
			return;
		}

		// Remove lang/skin from $TUPA_CONF_VARS array
		// Check if config file is writable
		$configFile = PATH_config .'config_site.inc.php';
		if (!@is_writable($configFile)) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('configFileNotWritable', array('file' => $configFile)));
			lib_logging::addLogMessage($part .'s', 'delete', 'error', 'logMsgConfigFileNotWritable', array('file' => $configFile));
			return;
		}

		if (!$skipSuccessMsg) {
			// Load config
			$CONFIG = lib_div::makeInstance('lib_config');
			$siteConfig = $CONFIG->writeToConfig_control();
			if ($part == 'lang') {
				$newItems = lib_div::rmFromList($id, $TUPA_CONF_VARS['SYS']['languages'], '|');
				$CONFIG->setValueInConfigFile($siteConfig, '$TUPA_CONF_VARS[\'SYS\'][\'languages\']', $newItems);
			} elseif ($part == 'skin') {
				$newItems = lib_div::rmFromList($id, $TUPA_CONF_VARS['SKINS']['skins'], '|');
				$CONFIG->setValueInConfigFile($siteConfig, '$TUPA_CONF_VARS[\'SKINS\'][\'skins\']', $newItems);
			}

			// Write config back to file
			if ($CONFIG->writeToConfig_control($siteConfig) == 'nochange') {
				$TBE_TEMPLATE->addMessage('error', $LANG->getLang('configFileNotChanged', array('file' => $configFile)));
				lib_logging::addLogMessage($part .'s', 'delete', 'error', 'logMsgConfigFileNotChanged', array('file' => $configFile));
				return;
			}
		}

		// Delete directory
		if (!lib_div::rmdirRecursive($fullItemPath)) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('system'. lib_div::firstUpper($part) .'DeleteFailed', array('item' => $id)));
			lib_logging::addLogMessage($part .'s', 'delete', 'error', 'logMsgSystem'. lib_div::firstUpper($part) .'DeleteFailed', array('item' => $id));
			return;
		}

		// Update view
		if (!$skipSuccessMsg) $TBE_TEMPLATE->addMessage('', '', 'get'. lib_div::firstUpper($part) .'MgrUpdate('. $conf['data']['rInfo'] .');');

		// Language was successfully deleted
		if (!$skipSuccessMsg) $TBE_TEMPLATE->addMessage('success', $LANG->getLang('system'. lib_div::firstUpper($part) .'DeleteSuccess', array('item' => $id)));
		lib_logging::addLogMessage($part .'s', 'delete', 'info', 'logMsgSystem'. lib_div::firstUpper($part) .'DeleteSuccess', array('item' => $id));

		return true;
	}



	/**
	 * Update lang/skin from server
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function updateToolsItem($conf) {
		global $TBE_TEMPLATE, $USER, $LANG;

		$part = '';
		$id = strtolower($conf['data']['id']);

		switch ($conf['part']) {
			case 'updateSysLang':
				$part = 'lang';
				$fullItemPath = PATH_lang . $id .'/';
				break;
			case 'updateSysSkin':
				$part = 'skin';
				$fullItemPath = PATH_site . 'skins/'. $id .'/';
				break;
		}

		if (!$part) return;

		// Check permissions
		if (!$USER->hasPerm($part .'s_update')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage($part .'s', 'update', 'permission', 'logMsgNoPermission');
			return;
		}

		// Execute install script but skip final message
		if (!tupa_system::installToolsItem($conf, true, 'installSys'. lib_div::firstUpper($part))) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('system'. lib_div::firstUpper($part) .'UpdateFailedPrevious', array('item' => $id)));
			lib_logging::addLogMessage($part .'s', 'update', 'error', 'logMsgSystem'. lib_div::firstUpper($part) .'UpdateFailedPrevious', array('item' => $id));
			return;
		}

		// Lang/Skin was successfully updated
		$TBE_TEMPLATE->addMessage('success', $LANG->getLang('system'. lib_div::firstUpper($part) .'UpdateSuccess', array('item' => $id)));
		lib_logging::addLogMessage($part .'s', 'update', 'info', 'logMsgSystem'. lib_div::firstUpper($part) .'UpdateSuccess', array('item' => $id));
	}



	/**
	 * Install lang/skin from server
	 *
	 * @param	array		Configuration array
	 * @return	string		content
	 */
	function installToolsItem($conf, $update=false, $cPart='') {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		$part = '';
		if(!$cPart) $cPart = $conf['part'];
		$id = strtolower($conf['data']['id']);

		switch ($cPart) {
			case 'installSysLang':
				$part = 'lang';
				$fullItemPath = PATH_lang . $id .'/';
				break;
			case 'installSysSkin':
				$part = 'skin';
				$fullItemPath = PATH_site . 'skins/'. $id .'/';
				break;
		}

		if (!$part) return;

		// Check permissions
		if (!$USER->hasPerm($part .'s_install')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage($part .'s', 'install', 'permission', 'logMsgNoPermission');
			return;
		}

		// Check if directory exists (if so, try to delete language first)
		if (@file_exists($fullItemPath)) {
			if (!$update) $TBE_TEMPLATE->addMessage('error', $LANG->getLang('tools'. lib_div::firstUpper($part) .'DirAlreadyExistsError', array('dir' => $fullItemPath)));

			if (!tupa_system::deleteToolsItem($conf, true, 'deleteSys'. lib_div::firstUpper($part))) {
				if (!$update) $TBE_TEMPLATE->addMessage('error', $LANG->getLang('tools'. lib_div::firstUpper($part) .'InstallFailedPrevious', array('item' => $id)));
				if (!$update) lib_logging::addLogMessage($part .'s', 'install', 'error', 'logMsgSystem'. lib_div::firstUpper($part) .'InstallFailedPrevious', array('item' => $id));
				return;
			}
		}

		// Check if directory can be created
		if (!@mkdir($fullItemPath, 0755)) {
			$TBE_TEMPLATE->addMessage('error', $LANG->getLang('system'. lib_div::firstUpper($part) .'InstallCreateDirFailed', array('dir' => $fullItemPath)));
			lib_logging::addLogMessage($part .'s', 'install', 'error', 'logMsgSystem'. lib_div::firstUpper($part) .'InstallCreateDirFailed', array('dir' => $fullItemPath));
			return;
		}

		if ($part == 'lang') {
			// Get language data from server
			$getUrl = $TUPA_CONF_VARS['SYS']['langMgrServerUrl'] .'?no_cache=1&lang_code='. $id;
			$langFilesData = XMLParser::GetXMLTree($getUrl);

			// Add header data
			$phpDocLangHeader = $TUPA_CONF_VARS['MISC']['phpDocLangHeader'];
			$phpDocLangHeader = str_replace('###LANGUAGE###', $id, $phpDocLangHeader);
			$subpartAuthor = $TBE_TEMPLATE->getSubpart($phpDocLangHeader, '###AUTHOR_P###');

			// Write files to directory
			foreach ($langFilesData['TUPA_LANGUAGEPACK'][0]['FILE'] as $file) {

				$fileName = $file['FILENAME'][0]['VALUE'];
				$fileContent = base64_decode($file['CONTENT'][0]['VALUE']);
				$authorsContent = '';

				// Get infos for header
				if ($fileName == 'info.xml') {
					$infoData = XMLParser::GetXMLTree($fileContent);
					$authors = $infoData['LANGUAGE'][0]['AUTHOR'];
					unset($infoData);

					foreach ($authors as $author) {
						$markerArray = array();
						$markerArray['author'] = $author['NAME'][0]['VALUE'];
						$markerArray['email'] = $author['EMAIL'][0]['VALUE'];
						$authorsContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartAuthor, $markerArray, '###|###', '1') ."\n";
					}

					$phpDocLangHeader = $TBE_TEMPLATE->substituteSubpart($phpDocLangHeader, '###AUTHOR_P###', $authorsContent);
				}

				if (substr($fileName, strlen($fileName)-3) == 'xml') {
					$fileContent = $fileContent;
				} else {
					$fileContent = "<?\n". $phpDocLangHeader ."\n\n". $TUPA_CONF_VARS['MISC']['copyrightNotice'] ."\n\n". $fileContent ."\n?>";
				}


	//			$TBE_TEMPLATE->addMessage('debug', htmlentities($fileName));
	//			$TBE_TEMPLATE->addMessage('debug', htmlentities($fileContent));

				$fh = fopen($fullItemPath . $fileName, 'w');
				fwrite($fh, $fileContent);
				fclose($fh);
			}
			// Write default index file
			$fh = fopen($fullItemPath .'index.html', 'w');
			fwrite($fh, $TUPA_CONF_VARS['MISC']['defaultIndexLevel2']);
			fclose($fh);
		} elseif ($part == 'skin') {
			// Create remote URL
			$getUrl = $TUPA_CONF_VARS['SKINS']['skinMgrServerUrl'] .'?no_cache=1&skin_id='. $id;

			// Get info.xml content
			$infoData = file($getUrl);
			// Must have more than one line
			if (count($infoData) <= 1) {

				return;
			}
			$infoData = implode('', $infoData);


			// Get skin file package from server
			$tmpSkinPath = $fullItemPath .'tmpskin.tgz';
			$getSkinReturn = lib_div::readRemoteWriteLocal($getUrl .'&cmd=get_skin', $tmpSkinPath);
			if ($getSkinReturn === false) {
				// Error while fetching or write

				return;
			} elseif ($getSkinReturn == 0) {
				// Fetched but empty...

				return;
			}

			$archiveType = lib_div::getArchiveType($tmpSkinPath);

			// Decompress file
			if ($archiveType == 'gzip') {
				if (!lib_div::gunzip($tmpSkinPath) ) {
					// Error on decopress

					return;
				}
			} else {
				// No known file extension

				return;
			}

			$tmpSkinPath = lib_div::getUncompressedFilename($tmpSkinPath);

			// Untar file
			if (!lib_div::untar($tmpSkinPath)) {
				// Error on untar

				return;
			}

			// Remove tmp file
			unlink($tmpSkinPath);

			// (Over)write info.xml file
			$fh = fopen($fullItemPath .'info.xml', 'w');
			if ($fh === false) {
				// Error writing info.xml

				return;
			}
			fwrite($fh, $infoData);
			fclose($fh);


			// Write default index file
			$fh = fopen($fullItemPath .'index.html', 'w');
			fwrite($fh, $TUPA_CONF_VARS['MISC']['defaultIndexLevel2']);
			fclose($fh);
		}

		if (!$update) {
			// Add language to $TUPA_CONF_VARS array
			// Check if config file is writable
			$configFile = PATH_config .'config_site.inc.php';
			if (!@is_writable($configFile)) {
				$TBE_TEMPLATE->addMessage('error', $LANG->getLang('configFileNotWritable', array('file' => $configFile)));
				lib_logging::addLogMessage($part .'s', 'delete', 'error', 'logMsgConfigFileNotWritable', array('file' => $configFile));
				return;
			}
			// Load config
			$CONFIG = lib_div::makeInstance('lib_config');
			$siteConfig = $CONFIG->writeToConfig_control();
			if ($part == 'lang') {
				$newItems = lib_div::uniqueList($TUPA_CONF_VARS['SYS']['languages'] .'|'. $id, '|');
				$CONFIG->setValueInConfigFile($siteConfig, '$TUPA_CONF_VARS[\'SYS\'][\'languages\']', $newItems);
			} elseif ($part == 'skin') {
				$newItems = lib_div::uniqueList($TUPA_CONF_VARS['SKINS']['skins'] .'|'. $id, '|');
				$CONFIG->setValueInConfigFile($siteConfig, '$TUPA_CONF_VARS[\'SKINS\'][\'skins\']', $newItems);
			}
			// Write config back to file
			if ($CONFIG->writeToConfig_control($siteConfig) == 'nochange') {
				$TBE_TEMPLATE->addMessage('error', $LANG->getLang('configFileNotChanged', array('file' => $configFile)));
				lib_logging::addLogMessage($part .'s', 'delete', 'error', 'logMsgConfigFileNotChanged', array('file' => $configFile));
				return;
			}
		}

		// Update view
		$TBE_TEMPLATE->addMessage('', '', 'get'. lib_div::firstUpper($part) .'MgrUpdate('. $conf['data']['rInfo'] .');');

		// Lang/Skin was successfully installed
		if (!$update) $TBE_TEMPLATE->addMessage('success', $LANG->getLang('system'. lib_div::firstUpper($part) .'InstallSuccess', array('item' => $id)));
		if (!$update) lib_logging::addLogMessage($part .'s', 'install', 'info', 'logMsgSystem'. lib_div::firstUpper($part) .'InstallSuccess', array('item' => $id));

		return true;
	}






	/**
	 * Gets actual skins from server and creates list of installed an available skins
	 *
	 * @param	array		Configuration array
	 * @return	string		messages
	 */
	function getSkinMgrUpdate($conf, $subpart='') {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		// Check permissions
		if (!$USER->hasPerm('skins_show')) {
			$TBE_TEMPLATE->noPermissionMessage(true);
			lib_logging::addLogMessage('skins', 'show', 'permission', 'logMsgNoPermission');
			return;
		}

		$removeComments = false;
		$rSkinInfo = array();
		$getRemoteData = lib_div::isset_value($conf, 'data=>rInfo');

		// Get subpart from template if it was not submitted
		if (!$subpart) {
			$markerArray = lib_div::setDefaultMarkerArray();

			// Get the template
			$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'system.html'));
			// Get template subpart
			$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###SYSTEM_SKIN_MANAGER###');

			$markerArray['tools_skin_installed_subtitle'] = $LANG->getLang('systemSkinInstalledSubtitle');
			$markerArray['tools_skin_available_subtitle'] = $LANG->getLang('systemSkinAvailableSubtitle');
			$markerArray['button_get_skins'] = '<input type="button" name="getSkins" onclick="getSkinMgrUpdate(1);" value="'. $LANG->getLang('systemSkinButtonGet') .'" class="'. STYLE_BUTTON .'" />';

			// Substitute markers
			$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
			$removeComments = true;
		}

		// Get current language informations from server if button was clicked
		if ($getRemoteData && $conf['part'] == 'getSkinMgrUpdate') {
			$rSkinInfo = XMLParser::GetXMLTree($TUPA_CONF_VARS['SKINS']['skinMgrServerUrl']);
			// debug($rSkinInfo);
		}

		// Get the path and sizes of the used icons
		$icons = array();
		$icons['inst'] = lib_div::getImageInfo('icons/install.png');
		$icons['upd'] = lib_div::getImageInfo('icons/update.png');
		$icons['del'] = lib_div::getImageInfo('icons/garbage.png');

		// Get additonal subparts
		$subpartInstalled = $TBE_TEMPLATE->getSubpart($subpart, '###INSTALLED_SKIN###');
		$subpartAvailable = $TBE_TEMPLATE->getSubpart($subpart, '###AVAILABLE_SKIN###');

		// Get installed languages
		$subpartInstalledContent = '';
		$subpartAvailableContent = '';
		$rowColor = 1;

		$skinArr = lib_div::trimExplode('|', $TUPA_CONF_VARS['SKINS']['skins']);

		foreach ($skinArr as $skin) {
			$markerArray = array();
			$needUpdate = false;
			$rSkinInfoTmp = array();

			// Get language info
			$lSkinInfo = tupa_system::getSkinInfo($skin);

			if (lib_div::isset_value($rSkinInfo, 'SKINMGR_SKINS=>0=>'. strtoupper($skin) .'=>0')) {
				$rSkinInfoTmp = $rSkinInfo['SKINMGR_SKINS'][0][strtoupper($skin)][0];
				if ($rSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE'] > $lSkinInfo['SKIN'][0]['VERSION_TSTAMP'][0]['VALUE']) $needUpdate = true;

				// Unset from remote array
				unset ($rSkinInfo['SKINMGR_SKINS'][0][strtoupper($skin)]);
			}

			$skinName = $lSkinInfo['SKIN'][0]['NAME'][0]['VALUE'];
			$lSkinInfoTmp = $lSkinInfo['SKIN'][0];

			// Prepare delete command / image
			$preDelete = 'deleteSysSkin(\''. $skin.'\');';
			$msgTrans = $LANG->getLang('systemDeleteSysSkinJsConfirm', array('name' => $skinName));
			$preDelete = "openConfirm('". lib_div::convertMsgForJS($msgTrans) ."', '". lib_div::slashJS($preDelete) ."')";

			$markerArray['skin_name'] = $skinName;
			$markerArray['skin_id'] = $skin;
			$markerArray['skin_last_change'] = $lSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE'] ? strftime(DATE_FORMAT, $lSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE']) .' - '. strftime(TIME_FORMAT, $lSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE']) : $LANG->getLang('systemSkinUpdatedNever');
			$markerArray['skin_last_remote_change'] = count($rSkinInfoTmp) ? $rSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE'] ? strftime(DATE_FORMAT, $rSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE']) .' - '. strftime(TIME_FORMAT, $rSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE']) : $LANG->getLang('systemSkinUpdatedNever') : '';
			$markerArray['skin_update'] = $needUpdate ? '<a href="javascript:void(0);" onclick="updateSysSkin(\''. $skin .'\');"><img src="'. $icons['upd']['path'] .'" '. $icons['upd']['size'][3] .' border="0" alt="'. $LANG->getLang('altUpdate') .'" /></a>' : '';
			$markerArray['skin_remove'] = $skin != $TUPA_CONF_VARS['SKINS']['skinFallback'] && $skin != $TUPA_CONF_VARS['SKINS']['skinDefault'] ? '<a href="javascript:void(0);" onclick="'. $preDelete .'"><img src="'. $icons['del']['path'] .'" '. $icons['del']['size'][3] .' border="0" alt="'. $LANG->getLang('altRemove') .'" /></a>' : '';
			$markerArray['row_class'] = 'table-row'. $rowColor;

			$subpartInstalledContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartInstalled, $markerArray, '###|###', '1');

			// Toggle row color
			$rowColor == 1 ? $rowColor = 2 : $rowColor = 1;
		}

		$markerArray = array();
		$markerArray['skin_name_title'] = $LANG->getLang('systemSkinSkinTitle');
		$markerArray['skin_id_title'] = $LANG->getLang('systemSkinIdTitle');
		$markerArray['skin_last_change_title'] = $LANG->getLang('systemSkinLastChangeTitle');
		$markerArray['skin_last_remote_change_title'] = $LANG->getLang('systemSkinLastRemoteChangeTitle');
		$markerArray['skin_install_title'] = $LANG->getLang('systemSkinInstallTitle');
		$markerArray['skin_update_title'] = $LANG->getLang('systemSkinUpdateTitle');
		$markerArray['skin_remove_title'] = $LANG->getLang('systemSkinRemoveTitle');
		$subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');

		if ($getRemoteData && $conf['part'] == 'getSkinMgrUpdate') {
			if (lib_div::isset_value($rSkinInfo, 'SKINMGR_SKINS=>0') && is_array($rSkinInfo['SKINMGR_SKINS'][0])) {
				$rowColor = 1;
				foreach ($rSkinInfo['SKINMGR_SKINS'][0] as $key => $value) {
					$markerArray = array();
					$rSkinInfoTmp = $rSkinInfo['SKINMGR_SKINS'][0][$key][0];

					$markerArray['skin_name'] = $rSkinInfoTmp['NAME'][0]['VALUE'];
					$markerArray['skin_id'] = strtolower($key);
					$markerArray['skin_last_change'] = $rSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE'] ? strftime(DATE_FORMAT, $rSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE']) .' - '. strftime(TIME_FORMAT, $rSkinInfoTmp['VERSION_TSTAMP'][0]['VALUE']) : $LANG->getLang('systemSkinUpdatedNever');
					$markerArray['skin_install'] = '<a href="javascript:void(0);" onclick="installSysSkin(\''. strtolower($key).'\');"><img src="'. $icons['inst']['path'] .'" '. $icons['inst']['size'][3] .' border="0" alt="'. $LANG->getLang('altInstall') .'" /></a>';
					$markerArray['row_class'] = 'table-row'. $rowColor;
					$subpartAvailableContent .= $TBE_TEMPLATE->substituteMarkerArray($subpartAvailable, $markerArray, '###|###', '1');

					// Toggle row color
					$rowColor == 1 ? $rowColor = 2 : $rowColor = 1;
				}
			}
		} else {
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###AVAILABLE_HEADERS###', '');
			$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###AVAILABLE_SKIN###', '<tr><td>'. $LANG->getLang('systemSkinAvailableGetMsg', array('buttonName'=>$LANG->getLang('systemSkinButtonGet'))) .'</td></tr>');
		}

		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###INSTALLED_SKIN###', $subpartInstalledContent);
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###AVAILABLE_SKIN###', $subpartAvailableContent);

		// Remove any copmments and return
		return $removeComments ? lib_div::removeHtmlComments($subpart) : $subpart;
	}



	/**
	 * Gets skin informations from XML file
	 *
	 * @param	string		Skin key to get infos from
	 * @return	array		array of skin infos
	 */
	function getSkinInfo($skin) {
		// Get language info file
		$xmlInfoPath = PATH_site .'skins/'. $skin .'/info.xml';
		if (file_exists($xmlInfoPath)) {
			$xmlData = file_get_contents($xmlInfoPath);

			if ($xmlData) {
				// Get the values
				$lSkinInfo = XMLParser::GetXMLTree($xmlInfoPath);
			}
		}
		return $lSkinInfo;
	}
}
?>