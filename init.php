<?php
/**
 * Initialisation
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


// **********************************
// Set session name and start it
// **********************************
session_name('tupa_dns');
session_start();

// *************
// Set umask
// *************
umask(0022);

// *******************************
// Define constants
// *******************************
define('PATH_site', dirname(__FILE__) .'/');
define('PATH_lib', PATH_site.'lib/');
define('PATH_config', PATH_site.'config/');
define('PATH_lang', PATH_site .'lang/');


// *******************************
// Include defualt library
// *******************************
require_once(PATH_lib.'class.div_functions.php');

// ***********************************
// Make the debug function global
// ***********************************
function debug($var='',$brOrHeader=0) {
	lib_div::debug($var, $brOrHeader);
}

// ***************************************************************
// Include dfault configuration and overwrite with site config
// Template specific config is included at the end of this file.
// This makes it possible to configure RRD charts values
// template specific
// ***************************************************************
if (@is_file(PATH_lib .'config_default.php')) {
	require(PATH_lib .'config_default.php');
} else {
	die ('The default configuration file was not included.');
}
if (@is_file(PATH_config .'config_site.inc.php')) {
	require(PATH_config .'config_site.inc.php');
}

// **************************************
// Set error reporting for debugging
// **************************************
$debugMode = $TUPA_CONF_VARS['SYS']['debugMode'];
if ($debugMode) {
	// Show ALL errors
	error_reporting(E_ALL);
	$startMT = lib_div::microtime_float();
} else {
	// Show everything wothout notices (normally PHP default)
	error_reporting(E_ALL ^ E_NOTICE);
}

// *******************************
// Include database library
// *******************************
require_once(PATH_lib.'class.db.php');		// The database library
$TUPA_DB = lib_div::makeInstance('lib_DB');


// *********************
// Libraries included
// *********************
require_once(PATH_lib.'class.userauth.php');
require_once(PATH_lib.'class.template.php');
require_once(PATH_lib.'class.menu.php');
require_once(PATH_lib.'class.language.php');
require_once(PATH_lib.'class.logging.php');
require_once(PATH_lib.'class.config.php');
require_once(PATH_lib.'class.xmlparser.php');

// ********************************
// Additional JsCalendar libraries
// ********************************
require_once(PATH_lib.'js/jscalendar/calendar.php');


// *******************************
// Checking environment
// *******************************
if (lib_div::int_from_ver(phpversion())<4001000) die ('TUPA runs with PHP4.1.0+ only');
if (isset($_POST['GLOBALS']) || isset($_GET['GLOBALS'])) die('You cannot set the GLOBALS-array from outside the script.');
if (!get_magic_quotes_gpc())	{
	lib_div::addSlashesOnArray($_GET);
	lib_div::addSlashesOnArray($_POST);
	$HTTP_GET_VARS = $_GET;
	$HTTP_POST_VARS = $_POST;
}


// *************************
// Connect to the database
// *************************
// Before we connect to the db, we check if the mysql extension really is active
if (!extension_loaded('mysql')) {
	lib_div::printError ('The MySQL extension is not loaded in php. Check you configuration!','PHP Error');
	exit;
}

if ($GLOBALS['TUPA_DB']->sql_pconnect(TUPA_db_host, TUPA_db_port, TUPA_db_username, TUPA_db_password)) {
	if (!TUPA_db)	{
		lib_div::printError ('No database selected','Database Error');
		exit;
	} elseif (!$GLOBALS['TUPA_DB']->sql_select_db(TUPA_db))	{
		lib_div::printError ('Cannot connect to the current database, "'.TUPA_db.'"','Database Error');
		exit;
	}
} else {
	lib_div::printError ('The current username, password or host was not accepted when the connection to the database was attempted to be established!','Database Error');
	exit;
}


// *******************************
// Some other constants
// *******************************
define('STYLE_FIELD', $TUPA_CONF_VARS['SYS']['styleField']);
define('STYLE_BUTTON', $TUPA_CONF_VARS['SYS']['styleButton']);
// IE png fix
$msie='/msie\s(5\.[5-9]|[6]\.[0-9]*).*(win)/i';
if( !isset($_SERVER['HTTP_USER_AGENT']) || !preg_match($msie,$_SERVER['HTTP_USER_AGENT']) || preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT'])) { $pngFix = false; } else { $pngFix = true; }
define('IE_PNGFIX', $pngFix);

// *******************************
// User authentication
// *******************************
$USER = lib_div::makeInstance('lib_userauth');
$USER->perm = $USER->getPermissions();
if (isset($_SESSION[$USER->session_uid])) $USER->loadGroupPreferences($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']));

// *******************************
// Skin constants
// *******************************
define('PATH_skin_root', 'skins/'. $TUPA_CONF_VARS['SKINS']['skin'] .'/');
define('PATH_skin_root_fallback', 'skins/'. $TUPA_CONF_VARS['SKINS']['skinFallback'] .'/');
define('PATH_images', PATH_skin_root .'images/');
define('PATH_images_fallback', PATH_skin_root_fallback .'images/');
define('PATH_templates', PATH_site . PATH_skin_root .'templates/');
define('PATH_templates_fallback', PATH_site . PATH_skin_root_fallback .'templates/');

// Include template specific (RRD) config
if (@is_file(PATH_site . PATH_skin_root .'config_skin.inc.php')) {
	require(PATH_site . PATH_skin_root .'config_skin.inc.php');
}

// Users language - cut to first part for jsCalendar
$language = explode('-', $TUPA_CONF_VARS['SYS']['language'] );
$jsCalendar = new DHTML_Calendar('lib/js/jscalendar/', $language['0'], 'calendar-win2k-1', $TUPA_CONF_VARS['SYS']['optimizeJsCode']);

$LANG = lib_div::makeInstance('lib_lang');
// Set localiced date and time fomating
define('DATE_FORMAT', '%'. str_replace(' ', $LANG->getLang('dateSplitter') .'%', $LANG->getLang('dateFormat')));
define('TIME_FORMAT', '%'. str_replace(' ', $LANG->getLang('timeSplitter') .'%', $LANG->getLang('timeFormat')));

$MENU = lib_div::makeInstance('lib_menu');
?>