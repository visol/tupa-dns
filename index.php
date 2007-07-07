<?php
/**
 * Loginscreen
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

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


// Include needed files and start session
require('init.php');
// Include browser detection functions
require(PATH_lib .'browser_detection.php');

// Check language cookie and set value in configuration.
// Or try to detect a matching browser language.
if (isset($_COOKIE['lang']) && strlen($_COOKIE['lang']) < '10') {
	$TUPA_CONF_VARS['SYS']['language'] = $_COOKIE['lang'];
} else {
	$detectLang = $LANG->detectBrowserLanguage();
	if ($detectLang) {
		$TUPA_CONF_VARS['SYS']['language'] = $detectLang;
	}
}
$LANG->lib_lang();

// Check if form is submitted and get error message when needed
$loginError = $USER->login();

$hiddenFields = '';
$tmpHash = md5(uniqid(rand()));

$USER->setAuthHash($tmpHash);

// Get the template
$templateFileContent = $TBE_TEMPLATE->fileContent(PATH_site .'login.html');
// Get template subpart
$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###TUPA_LOGIN###');

$markerArray['meta_generator'] = $TBE_TEMPLATE->generator();
$markerArray['page_title'] = 'TUPA: The Ultimate PowerDNS Admin - Login';
$markerArray['style_sheet'] = '<link rel="stylesheet" type="text/css" href="login_styles.css" />';

$markerArray['js_md5'] = '<script type="text/javascript" src="lib/js/md5.js"></script>';
$markerArray['js_crypt_pass'] = $TBE_TEMPLATE->wrapScriptTags('
				function cryptPass() {
					var password = document.loginform.password.value;
					var tmphash = document.loginform.tmphash.value;

					if (password && tmphash) {
						document.loginform.pfield.value = MD5(MD5(password) + tmphash);
						document.loginform.password.value = "";
						return true;
					}
				}
			');
$markerArray['js_field_focus'] = $TBE_TEMPLATE->wrapScriptTags('
		// If for some reason there already is a username in the username for field, move focus to the password field:
		if (document.loginform.username && document.loginform.username.value == "") {
			document.loginform.username.focus();
		} else if (document.loginform.password && document.loginform.password.type!="hidden") {
			document.loginform.password.focus();
		}
	');

$hashTTL = $TUPA_CONF_VARS['SYS']['loginHashTimeout'];
$markerArray['js_hash_timer'] = $TBE_TEMPLATE->wrapScriptTags('
	function hashTimer() {
		this.hashRefreshed = hashTimer_hashRefreshed;
		this.checkHashTimeout = hashTimer_checkHashTimeout;
		this.hashTimerUpdate=2000;
		this.hashTime=0;
		this.timerState=0;
	}
	function hashTimer_hashRefreshed() {
		var date = new Date();
		this.hashTime = Math.floor(date.getTime()/1000);
		this.timerState=0;
		hashTimer.hasgTimerUpdate=2000;
	}
	function hashTimer_checkHashTimeout() {
		var date = new Date();
		var theTime = Math.floor(date.getTime()/1000);
		if (theTime > this.hashTime+'.  $hashTTL .'-10) {
			hashTimer.timerState=1;
			return true;
		}
	}
	function hashTimer_checkHashTimeout_timer() {
		if (hashTimer.checkHashTimeout()) {
			var loginboxElem = document.getElementById("loginbox").lastChild;
			var newRow = document.createElement("TR");
			var newCell = document.createElement("TD");
			newCell.setAttribute("colspan", 2);
			newCell.colSpan = 2;
			var newDiv = document.createElement("DIV");
			newDiv.setAttribute("id", "error");
			var message = document.createTextNode(\''. $LANG->getLang('loginErrorHashTimeout') .'\');
			newDiv.appendChild(message);
			newCell.appendChild(newDiv);
			newRow.appendChild(newCell);
			loginboxElem.insertBefore(newRow, loginboxElem.firstChild);
			document.loginform.username.disabled = true;
			document.loginform.password.disabled = true;
			document.loginform.login.disabled = true;
		}
		if (hashTimer.timerState < 1) {
			window.setTimeout("hashTimer_checkHashTimeout_timer();", hashTimer.hashTimerUpdate);
		}
	}

	// Initialize session timer
	var hashTimer = new hashTimer();
	hashTimer.hashRefreshed();
	hashTimer_checkHashTimeout_timer();
	');

$markerArray['login_image'] = $TBE_TEMPLATE->makeLoginBoxImage();

// Set error div
if ($loginError) {
	$markerArray['div_error'] = '<tr><td colspan="2">'. $TBE_TEMPLATE->wrapInDiv('error', $loginError) .'</td></tr>';
} else {
	$markerArray['div_error'] = '';
}

// check browser
$browserCheck = browserCheck();
if ($browserCheck) {
	$markerArray['browser_warning'] = '<tr><td colspan="2">'. $TBE_TEMPLATE->wrapInDiv('browserwarning', $LANG->getLang('broCheckWarning') . $browserCheck) .'</td></tr>';
} else {
	$markerArray['browser_warning'] = '';
}

// Check maintenance
if ($TUPA_CONF_VARS['SYS']['maintenanceEnabled']) {
	$markerArray['maintenance_message'] = '<tr><td colspan="2"><strong>'. $TBE_TEMPLATE->wrapInDiv('maintenance', '<span class="exmark">!!!</span> '. $LANG->getLang('maintenanceMsg') .' <span class="exmark">!!!</span>') .'</strong></td></tr>';
} else {
	$markerArray['maintenance_message'] = '';
}

$markerArray['label_username'] = $LANG->getLang('loginUser');
$markerArray['value_username'] = isset($_POST['username']) ? $_POST['username'] : '';
$markerArray['label_password'] = $LANG->getLang('loginPass');
$markerArray['submit_value'] = $LANG->getLang('loginButton');

$markerArray['cookie_warning'] = $LANG->getLang('loginCookies');
$markerArray['copyright'] = $LANG->getLang('loginCopyright');

$hiddenFields .= '<input type="hidden" name="tmphash" value="'. $tmpHash .'" />';
$hiddenFields .= '<input type="hidden" name="pfield" value="" />';
$markerArray['hidden_fields'] = $hiddenFields;

// Substitute markers
echo $subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');
?>

