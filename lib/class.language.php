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
 * Holds all language specific functions. Load languages, get translations of key, ...
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */
class lib_lang {
	var $LANG = array();			// Array which holds all configured languages
	var $HELP = array();			// Array which holds all help messages
	var $langArr = array();		// Holds all configured language codes


	/**
	 * Loads all configured languages and the default (en) if not included
	 * in list.
	 *
	 */
	function lib_lang() {
		global $TUPA_CONF_VARS;
		// Include all needed (configured) language files
		$this->langArr = explode('|', $TUPA_CONF_VARS['SYS']['languages']);

		// Include users language
		$lang = $TUPA_CONF_VARS['SYS']['language'];
		if (@is_file(PATH_lang . $lang .'/'. $lang .'.inc.php')) {
			include(PATH_lang . $lang .'/'. $lang .'.inc.php');
			$this->includeHelp($lang);
		}

		// Include fallback language file if not already loaded
		$lang = $TUPA_CONF_VARS['SYS']['langFallback'];
		if (!isset($this->LANG[$lang]) && @is_file(PATH_lang . $lang .'/'. $lang .'.inc.php')) {
			include(PATH_lang . $lang .'/'. $lang .'.inc.php');
			$this->includeHelp($lang);
		}
	}


	function includeHelp($lang) {
		global $TUPA_CONF_VARS;
		$displayHelp = $TUPA_CONF_VARS['PREFS']['displayHelp'];

		if ($displayHelp && @is_file(PATH_lang . $lang .'/help.inc.php')) {
			include(PATH_lang . $lang .'/help.inc.php');
			// Merge them into one array
			$this->LANG = array_merge_recursive($this->LANG, $this->HELP);
			unset($this->HELP);
		}
	}


	/**
	 * Gets translation in users language
	 *
	 * @param	string		Key to get
	 * @param	array		Additional replacements in translation: array('%whatever%' => 'replace with this')
	 * @return	string		translation
	 */
	function getLang($key, $varArr='') {
		global $TUPA_CONF_VARS;
		$lang = $TUPA_CONF_VARS['SYS']['language'];
		$langFallback = $TUPA_CONF_VARS['SYS']['langFallback'];

		// First try to get with users language config
		if (isset($this->LANG[$lang][$key])) {
			$content = $this->LANG[$lang][$key];
		// Not set, try the fallback language
		} elseif (isset($this->LANG[$langFallback][$key])) {
			$content = $this->LANG[$langFallback][$key];
		// Still no success, return an error placeholder
		} else {
			$content = 'No usable translation found!';
		}

		// Replace placeholders (like %whatever%)
		if (is_array($varArr)) {
			foreach ($varArr as $key => $var) {
				$content = str_replace('%'. $key .'%', $var, $content);
			}
		}

		if ($this->is_utf8($content)) {
			return $content;
		} else {
			return utf8_encode($content);
		}
	}


	/**
	 * Detects the browser languages and gives back the code if also exists in TUPA.
	 *
	 * @return	string		Code of language
	 */
	function detectBrowserLanguage() {
		$allLanguages = array();

		if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
			//explode languages into array
			$languages = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
			$languages = explode(',', $languages);

			foreach ($languages as $languageList) {
				// pull out the language, place languages into array of full and primary
				// string structure:
				$temp_array = array();
				// slice out the part before ; on first step, the part before - on second, place into array
				$temp_array[0] = substr( $languageList, 0, strcspn( $languageList, ';' ) );	//full language
				$temp_array[1] = substr( $languageList, 0, 2 );	// cut out primary language
				//place this array into main $user_languages language array
				$allLanguages[] = $temp_array;
			}

			//start going through each one
			for ( $i = 0; $i < count( $allLanguages ); $i++ ) {
				// Check full language first
				if (isset($this->LANG[$allLanguages[$i][0]])) {
					return $allLanguages[$i][0];
				}

				// Check primary language
				if (isset($this->LANG[$allLanguages[$i][1]])) {
					return $allLanguages[$i][1];
				}
			}
			return false;
		} else {
			return false;
		}
	}


	/**
	 * Checks if the submitted string is in utf-8 format
	 *
	 * @param	string		String to check
	 * @return	boolean		is or is not
	 */
	function is_utf8($string) {
		return (preg_match('/^([\x00-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xec][\x80-\xbf]{2}|\xed[\x80-\x9f][\x80-\xbf]|[\xee-\xef][\x80-\xbf]{2}|f0[\x90-\xbf][\x80-\xbf]{2}|[\xf1-\xf3][\x80-\xbf]{3}|\xf4[\x80-\x8f][\x80-\xbf]{2})*$/', $string) === 1);
	}


	/**
	 * Gets translated help message and generates the help icon
	 *
	 * @param	string		Key to get
	 * @param	array		Additional replacements in translation (it's just forwarded to the getLang()-Function)
	 * @return	string		img-tag of help image
	 */
	function getHelp($key, $varArr='') {
		global $TUPA_CONF_VARS;
		$displayHelp = $TUPA_CONF_VARS['PREFS']['displayHelp'];

		if ($displayHelp) {
			$message = $this->getLang($key, $varArr);

			$helpIcon = lib_div::getImageInfo('icons/help.png');
			if ($displayHelp == 1) {
				$params = 'onmouseover="showHelpLayer(this, \''. lib_div::convertMsgForJS($message) .'\');" onmouseout="hideHelpLayer();"';
			} elseif ($displayHelp == 2) {
				$params = 'onclick="showHelpPopup(\''. lib_div::convertMsgForJS($message) .'\');"';
				//$params = 'onclick="showHelpLayer(this, \''. lib_div::convertMsgForJS($message) .'\');" onmouseout="hideHelpLayer();"';
			}

			return ' <a href="javascript:void(0);"><img src="'. $helpIcon['path'] .'" '. $helpIcon['size'][3] .' align="middle" border="0" '. $params .' /></a>';
		}
	}


	/**
	 * Gets language informations from XML file
	 *
	 * @param	string		Language key to get infos from
	 * @return	array		array of language infos
	 */
	function getLanguageInfo($lang) {
		// Get language info file
		$xmlInfoPath = PATH_lang . $lang .'/info.xml';
		if (file_exists($xmlInfoPath)) {
			$xmlData = file_get_contents($xmlInfoPath);

			if ($xmlData) {
				// Get the values
				$lLangInfo = XMLParser::GetXMLTree($xmlInfoPath);
			}
		}
		return $lLangInfo;
	}
}

?>