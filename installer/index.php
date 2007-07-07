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

// die('The installer is locked by die()-function! To enable it you have to add a comment on line 28 in "installer/index.php".');

// *******************************
// Define constants
// *******************************
define('PATH_site', dirname(dirname(__FILE__)).'/');
define('PATH_lib', PATH_site.'lib/');
define('PATH_config', PATH_site.'config/');


// *******************************
// Include defualt library
// *******************************
require_once(PATH_lib.'class.div_functions.php');

// *******************************
// Include database library
// *******************************
//require_once(PATH_lib.'class.db.php');		// The database library
//$TUPA_DB = lib_div::makeInstance('lib_DB');

// *******************************
// Include configuration
// *******************************
if (@is_file(PATH_lib .'config_default.php')) {
	require(PATH_lib .'config_default.php');
} else {
	die ('The default configuration file was not included.');
}
if (@is_file(PATH_config .'config_site.inc.php')) {
	require(PATH_config .'config_site.inc.php');
}

// *******************************
// Include database library
// *******************************
require_once(PATH_lib.'class.db.php');		// The database library
$TUPA_DB = lib_div::makeInstance('lib_DB');

require_once(PATH_lib.'class.config.php');

/**
 * Installer functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */
class installer extends  lib_config {

	var $logout = false;
	var $scriptSelf = 'index.php';
	var $action = '';
	var $part = '';
	var $subpart = '';
	var $globalHead = '';
	var $cookie_name = 'TupaInstaller';
	var $menuitems = array(
		'about' => 'About',
		'password' => 'Change password!',
		'check' => 'Check requirements',
		'db_config' => 'Database settings',
		'db_update' => 'Update database',
		'data_import' => 'Data import/migration',
		'add_admin' => 'Create admin user',
		'finish' => 'Logout'
	);

	var $loggedin=0;			// This is set, if the password check was ok. The function init() will exit if this is not set
	var $silent=0;				// If set, the check routines don't add to the message-array
	var $messageFunc_nl2br=1;
	var $sections=array();		// Used to gather the message information.
	var $fatalError=0;			// This is set if some error occured that will definitely prevent TYpo3 from running.
	var $sendNoCacheHeaders=1;
	var $setAllCheckBoxesByDefault=0;
	var $dbUpdateCheckboxPrefix = 'db_update_fields';
	var $dbUpdateFields = '';
	var $config;				// Holds submitted form values
	var $config_array = array();

	var $installSqlFile = 'tupa-install-mysql.sql';


	function installer() {

		if ($this->sendNoCacheHeaders)	{
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Expires: 0");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
		}

		// check for logout
		if ($_GET['logout']==1) {
			// delete cookies
			setcookie($this->cookie_name.'_key');
			setcookie($this->cookie_name);
			$this->logout = true;
		}

		$this->part = $_GET['part'];
		if (!key_exists($this->part, $this->menuitems) && $this->part != 'logout') $this->part = 'about';

		if (isset($_POST['subpart'])) $this->subpart = $_POST['subpart'];
		if (isset($_POST['db_update_fields'])) $this->dbUpdateFields = $_POST['db_update_fields'];
		if (isset($_POST['config'])) $this->config = $_POST['config'];
		lib_div::stripTagsOnArray($this->config);
		lib_div::addSlashesOnArray($this->config);

		// Set form action
		$this->action = $this->scriptSelf .'?'. ($this->part ? '&part='. rawurlencode($this->part) : '');

		// Get the unique key if any set
		$uKey = $_COOKIE[$this->cookie_name.'_key'];
		if (!$uKey)	{
			$uKey = md5(uniqid(rand()));
			setcookie($this->cookie_name.'_key', $uKey, 0);
		}

		// Check if the password from "installer-pweord.inc.php" combined with uKey matches the sKey cookie. If not, ask for password.
		$sKey = $_COOKIE[$this->cookie_name];

		if (!$this->logout && md5(TUPA_installer_password.$uKey) == $sKey || $this->checkPassword($uKey)) {
			$this->loggedin=1;
		}
	}



	/**
	 * Returns true if submitted password is ok. Else displays a form in which to enter password.
	 *
	 * @param	string		Generated key
	 * @return	boolean
	 */
	function checkPassword($uKey) {
		$password = $_POST['pword'];

		if ($password && md5($password) == TUPA_installer_password) {
			$sKey = md5(TUPA_installer_password.$uKey);
			setcookie($this->cookie_name, $sKey, 0);

			return true;
		} else {
			$content = '<form action="'.$this->action.'" method="POST">
			<input type="password" name="pword" size="25" class="field"><input type="submit" value="Log in" class="button"><br />
			The Install Tool Password is <i>not</i> the admin password of TUPA.<br />
			The default password is "tupainstall". If you don\'t know the current password, you can set a new one by setting the variable "$installer_password" in "config/config_site.inc.php" to the md5() hash value of the password you desire.'.
				($password ? '<br /><br />The password you just tried has this md5-value: <br />'. md5($password) : '')
				.'
			</form>';

			$this->message("Password", "Please enter the Installer Password", $content, 1);
			echo $this->outputWrapper($this->printAll());

			return false;
		}
	}


	/**
	 * Init function
	 *
	 * @param	string		Generated key
	 * @return	void
	 */
	function init() {
		if (!$this->loggedin) exit;

		switch ($this->part) {
			case 'password':
				$this->changePassword();
				break;
			case 'check':
				$this->checkExtensions();
				$this->checkConfiguration();
				$this->checkDirectories();
				$this->checkFiles();
				break;
			case 'db_config':
				$this->configDb();
				break;
			case 'db_update':
				$this->updateDb();
				break;
			case 'add_admin':
				$this->addAdminUser();
				break;
			case 'data_import':
				$this->dataImport();
				/*$this->message('Import', 'Data Import - Still to come', 'There will be an import function for existing PDNS domains in a later release of TUPA. And maybe also to import Bind-Data somehow. Till then you have to do this manually in your database:<br />
				- Get all ID\'s of your domains in database
				- Create a table with them in OpenOffice, Excel or similar (structure is "dom_id", "usr_id")
				- Set the "usr_id" to "1" on all domains
				- Save the table as CSV and import it to table "domain_owners"<br />
				<strong>For Bind users:</strong>
				There is a script called zone2sql in the PDNS package to convert Bind named.conf files to SQL.
				==> <a href="http://downloads.powerdns.com/documentation/html/migration.html" target="_blank" >More informations</a>');*/
				break;
			case 'finish':
				$this->message('Configuration finished', 'Notice', 'If you have done all steps above, you have everything you need.
					You can '. $this->linkIt($this->scriptSelf .'?part=logout', 'logout') .' now and you are redirected to the login page of TUPA. Enjoy it...', 1);
				break;
			case 'logout':
				setcookie($this->cookie_name);
				setcookie($this->cookie_name .'_key');
				$header = 'Location: http://'.$_SERVER['HTTP_HOST'].dirname(dirname($_SERVER['PHP_SELF']));
				// Windows makes backslashes sometimes...
				$header = str_replace('\\', '/', $header);
				header($header);
				break;
			case 'about':
			default:
				$legendHead = 'Header legend';
				$this->messageFunc_nl2br = 0;
				$this->message('Installer - READ THIS!', 'How to use the installer', 'The installer is made to install or update TUPA on your server.<br />
				The database for TUPA has to be the same as you use for PDNS. <strong>Its not possible to install it to an other database</strong>. So the PDNS database, tables and an user <strong  style="color: red">must exist before</strong> starting the installation.<br />
				The installation/update is splitted into 8 parts:
				<ol>
				<li><strong>Informations:</strong><br />
				Installation/Update instructions and legend of different headers.</li>
				<li><strong>Change the installer password:</strong><br />
				You should at least change the installer password. Better you uncoment line 28 in "installer/index.php" after you finished the installation/update to lock it permanently. The installer password is submitted in plain text!</li>
				<li><strong>Check requirements:</strong><br />
				This part checks some php configurations and directories. Make sure you don\'t have any errors there (red header).</li>
				<li><strong>Database settings:</strong><br />
				Configures your MySQL settings and checks the connection to the database. The next steps are only possible when these settings are correct.</li>
				<li><strong>Database update:</strong><br />
				Compares the configured database structure with the one of the new TUPA version. If there are differences you have to update them to make TUPA working properly.</li>
				<li><strong>Data import/migration:</strong> (optional)<br />
				You can import existing PDNS domains from database, or migrate from an existing "PowerDNS Administration"- or "PowerAdmin"- installation. Migration from Bind zone files is also passible with an additional script in the PDNS package.</li>
				<li><strong>Admin user:</strong><br />
				After updating your database you have to create the user "admin" if you install TUPA the first time. This creates the group "Administration" and the user "admin" in your database.</li>
				<li><strong>Installation/Update finished:</strong><br />
				Congratulations. The installation is finished and you can login to your new installed or updated TUPA version.</li>
				</ol>', 0);
				$this->messageFunc_nl2br = 1;
				$this->message($legendHead, 'Information', 'Only an information about something.', 0);
				$this->message($legendHead, 'Check was successful', 'The checked value, option or setting is OK.', -1);
				$this->message($legendHead, 'Notice', 'Not an error, but maybe important.', 1);
				$this->message($legendHead, 'Warning', 'Indicates that something may not be correct and you should check you settings.',2);
				$this->message($legendHead, 'Error', 'Something is definitly wrong which you have to fix.',3);
				break;
		}
		echo $this->outputWrapper($this->printAll());
	}






	/**
	 * Checks the needed extensions.
	 *
	 * @return 	void
	 */
	function checkExtensions() {
		$head = 'PHP Extensions (Modules)';
		if (extension_loaded('mysql')) {
			$this->message($head, 'MySQL extension loaded', '', -1);
		} else {
			$this->message($head, 'MySQL extension', 'The extension "mysql" is not loaded in you configuration.
				Please check if the php_mysql package is installed.', 3);
		}
		if (extension_loaded('xml')) {
			$this->message($head, 'XML extension loaded', '', -1);
		} else {
			$this->message($head, 'XML extension', 'The extension "xml" (XML Support) is not loaded in you configuration.
				Please check if the xml package is installed.', 3);
		}

		if (extension_loaded('pcre')) {
			$this->message($head, 'pcre extension loaded', '', -1);
		} else {
			$this->message($head, 'pcre extension', 'The extension "pcre" (Perl Compatible Regular Expressions) is not loaded in you configuration
				You have to install and enable this extension.', 3);
		}

		if (extension_loaded('session')) {
			$this->message($head, 'session extension loaded', '', -1);
		} else {
			$this->message($head, 'session extension', 'The extension "session" (Session support) is not loaded in you configuration
				You have to install and enable this extension.', 3);
		}

		if (extension_loaded('zlib')) {
			$this->message($head, 'zlib extension loaded', '', -1);
		} else {
			$this->message($head, 'zlib extension', 'If you want to compress your database backups with gzip you should install the zlib extension.', 1);
		}

		if (extension_loaded('bz2')) {
			$this->message($head, 'bz2 extension loaded', '', -1);
		} else {
			$this->message($head, 'bz2 extension', 'If you want to compress your database backups with bz2 you should install the bz2 extension.', 1);
		}

		if (extension_loaded('ftp')) {
			$this->message($head, 'ftp extension loaded', '', -1);
		} else {
			$this->message($head, 'ftp extension', 'If you want to transfer your database to a remote server using FTP you should install the ftp extension.', 1);
		}

		if (extension_loaded('ssh2')) {
			$this->message($head, 'ssh2 extension loaded', '', -1);
		} else {
			$this->message($head, 'ssh2 extension', 'If you want to transfer your database to a remote server using SSH you should install the ssh2 extension.', 1);
		}
	}






	/**
	 * Checks some important PHP configuration values.
	 *
	 * @return 	void
	 */
	function checkConfiguration() {
		$head = 'php.ini settings';
		if (ini_get('register_globals')) {
			$this->message($head, 'Register globals enabled', '<i>register_globals='. ini_get('register_globals') .'</i>
				TUPA works with register globals enabled, but you should disable it anyway for security reasons', 2);
		} else $this->message($head, 'Register globals disabled', '',-1);

		if (!ini_get('magic_quotes_gpc')) {
			$this->message($head, 'magic_quotes_gpc','
				<i>magic_quotes_gpc = '. ini_get('magic_quotes_gpc') .'</i>
				Incoming " and \' chars in values by GET or POST method are currently <i>not</i> escaped. TUPA is designed to cope with that but it may be on the expense of a minor performance loss, because all incoming values are addslashes()\'ed.
			', 1);
		} else $this->message($head, 'Magic quotes GPC enabled','<i>magic_quotes_gpc = '.ini_get('magic_quotes_gpc').'</i>', -1);

		/*if (!ini_get('safe_mode')) {
			$this->message($head, 'Safe mode disabled', '<i>save_mode='. ini_get('safe_mode'). '</i>
					Safe mode should be enabled for security reasons', 2);
		} else {
			$this->message($head, 'Safe mode enabled', '',-1);
			if (!ini_get('safe_mode_exec_dir')) {
				$this->message($head, 'No savemode exec dir configured', '<i>save_mode_exec_dir='. ini_get('safe_mode_exec_dir'). '</i>
				When <i>save_mode</i> is enabled, you should also configure an ec dir and only copy nessesary program files into it. On Linux systems you can also (or should) use symbolic links.', 2);

			} else $this->message($head, 'Safe mode exec dir configured', '<i>safe_mode_exec_dir = '.ini_get('safe_mode_exec_dir').'</i>',-1);
		}*/
		if (ini_get('safe_mode')) {
			$this->message($head, 'Safe mode enabled', '<i>save_mode='. ini_get('safe_mode'). '</i>
				Safe mode has to be desabled for full functional application.', 3);
		} else $this->message($head, 'Safe mode disabled','<i>safe_mode = '.ini_get('safe_mode').'</i>', -1);

		/*if (!ini_get('open_basedir'))	{
			$this->message($head, 'open_basedir','
				<i>open_basedir = '. ini_get('open_basedir') .'</i>
				No open_basedir value configured. You should configure the directory of your TUPA installation.
			', 2);
		} else $this->message($head, 'open_basedir','<i>open_basedir = '.ini_get('open_basedir').'</i>', -1);*/

		if (ini_get('open_basedir'))	{
			$this->message($head, 'open_basedir','<i>open_basedir = '. ini_get('open_basedir') .'</i>
				Open basedir has to be disabled for TUPA', 3);
		} else $this->message($head, 'Open basedir disabled','<i>open_basedir = '.ini_get('open_basedir').'</i>', -1);

		if (!ini_get('allow_url_fopen'))	{
			$this->message($head, 'allow_url_fopen','<i>allow_url_fopen = '. ini_get('allow_url_fopen') .'</i>
				Allow URL fopen has to be enabled.', 3);
		} else $this->message($head, 'Allow URL fopen enabled','<i>allow_url_fopen = '.ini_get('allow_url_fopen').'</i>', -1);
	}






	/**
	 * Checks the directory permissions.
	 *
	 * @return 	void
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function checkDirectories() {
		$head = 'Directories';

		$uniqueName = md5(uniqid(microtime()));

			// The requirement level (the integer value, ie. the second value of the value array) has the following meanings:
			// -1 = not required, but if it exists may be writable or not
			//  0 = not required, if it exists the dir should be writable
			//  1 = required, don't has to be writable
			//  2 = required, has to be writable

		$checkWrite=array(
			"skins/" => array('Contains the skins directories. Has not to be writable at the moment.', 1),
			"lang/" => array('Contains the language directories. Has not to be writable at the moment.', 1),
			"stats/" => array('Contains PDNS statistic RRD charts. Has to be writable for webserver because it generates the chart into it.', 2),
			"stats/db/" => array('Contains PDNS statistic round robin database (RRD). Has to be writable for the user you use to run crontab and access the PDNS data. This normally is NOT the apache user. So you may can ignore this warning.', 1),
		);

		reset($checkWrite);
		while(list($relpath,$descr)=each($checkWrite))	{
			$general_message = $descr[0];
			if (!@is_dir(PATH_site.$relpath))	{
				if ($descr[1])	{	// required...
					$this->message($head, $relpath." directory does not exist","
					<em>Full path: ".PATH_site.$relpath."</em>
					".$general_message."

					This error should not occur as ".$relpath." must always be accessible in the root of a TYPO3 website.
					",3);
				} else {
					if ($descr[1] == 0) {
						$msg = 'This directory does not necessarily have to exist but if it does it must be writable.';
					} else {
						$msg = 'This directory does not necessarily have to exist and if it does it can be writable or not.';
					}
					$this->message($head, $relpath." directory does not exist","
					<em>Full path: ".PATH_site.$relpath."</em>
					".$general_message."

					".$msg."
					",2);
				}
			} else {
				$file = PATH_site.$relpath.$uniqueName;
				@touch($file);
				if (@is_file($file))	{
					unlink($file);
					if ($descr[2])	{ $this->config_array[$descr[2]]=1; }
					$this->message($head, $relpath." writable","",-1);
				} else {
					$severity = ($descr[1]==2 || $descr[1]==0) ? 3 : 2;
					if ($descr[1] == 0 || $descr[1] == 2) {
						$msg = "The directory ".$relpath." must be writable!";
					} elseif ($descr[1] == -1 || $descr[1] == 1) {
						$msg = "The directory ".$relpath." does not neccesarily have to be writable.";
					}
					$this->message($head, $relpath." directory not writable","
					<em>Full path: ".$file."</em>
					".$general_message."

					Tried to write this file (with touch()) but didn't succeed.
					".$msg."
					",$severity);
				}
			}
		}
	}




	/**
	 * Checks files.
	 *
	 * @return 	void
	 */
	function checkFiles() {
		$head = 'Files / Programs';

		if (PHP_OS == 'WINNT') {
			$this->message($head, "", "", 2);
			return;
		}
			// The requirement level (the integer value, ie. the second value of the value array) has the following meanings:
			//  0 = required program, has to be in $PATH and executable
			//  1 = file has to be writeable
			//  2 =

//		$envPath = lib_div::trimExplode(':', $_ENV['PATH']);

		$checkFile=array(
			"config/config_site.inc.php" => array('The configuration file has to be writeable by apache user.', 1),
			"pdns_control" => array('"pdns_control" not found in $PATH ('. $_ENV['PATH'] .').', 0),
			"rrdtool" => array('"rrdtool" not found in PATH ('. $_ENV['PATH'] .').', 0),
			"php" => array('"php" executable not found in PATH ('. $_ENV['PATH'] .').<br />It is needed for cronjobs (Backup / PDNS stats)', 0),
		);

		reset($checkFile);
		while(list($file, $descr)=each($checkFile))	{
			$general_message = $descr[0];

			switch ($descr[1]) {
				case 0:
					if (lib_div::checkProgramInPath($file, $descr[1])) {
						$this->message($head, '"'. $file .'" found and executable.', '', -1);
					} else {
						$this->message($head, 'Program "'. $file .'" not found!', $general_message .'
							Program not found, installed or executable. It has to be within $PATH enviroment variable to work.', 3);
					}
					break;
				case 1:
					if (file_exists(PATH_site.$file) && is_writable(PATH_site.$file)) {
						$this->message($head, '"'. $file .'" exists and is writeable.', '', -1);
					} else {
						$this->message($head, 'File "'. $file .'" not found or not writeable!', $general_message, 3);
					}
					break;
			}
		}
	}



	/**
	 * Function to change the installer password
	 *
	 * @return 	void
	 */
	function changePassword() {
		switch ($this->subpart) {
			case 'updatePassword':
				// Load config
				$siteConfig = $this->writeToConfig_control();
				// Set new password in config array
				$this->setValueInConfigFile($siteConfig, '$installer_password', md5($this->config['installerPassword']));
				//$this->message('tmp', 'tmp', print_r($siteConfig, true));
				// Write new config file
				if ($this->writeToConfig_control($siteConfig) == 'nochange') {
					$this->message('Change password', 'Failed', 'Could not update password', 3);
					break;
				}
				// Load page again to fall back to login screen
				header('Location: '. $this->action);
				break;

			default:
				$head = 'Change password';
				// Check if config directory is writable first
				if (!@is_dir(PATH_config) || !@is_writable(PATH_config .'config_site.inc.php')) {
					$this->message($head, 'File not writable', 'The file "config/config_site.inc.php" has to be writable by apache user to change password!', 3);
					break;
				}
				$subpart = 'updatePassword';
				$this->message($head, 'Form', '<form action="'. $this->action .'" name="chpwd" onSubmit="if(this.elements[0].value!=this.elements[1].value) { alert(\'The password fields don\\\'t match!\'); return false; }" method="POST">
					Enter new password:
					<input type="password" name="config[installerPassword]" class="field"><br />Enter again:
					<input type="password" name="installerPassword_check" class="field">
					<input type="hidden" name="subpart" value="'. htmlspecialchars($subpart) .'">
					<input type="submit" value="Set new password" class="button"><br />
					</form>'
				 , 1);
				 break;
		}
	}



	/**
	 * Function to change the database configuration
	 *
	 * @return 	void
	 */
	function configDb() {
		$head = 'Database configuration';
		$error = false;

		switch ($this->subpart) {
			case 'updateDbConfig':
				// Load config
				$siteConfig = $this->writeToConfig_control();
				// Set new values in config array
				if (!ereg("[^[:alnum:]:\.-:]", $this->config['dbhost']) && strlen($this->config['dbhost']) < 51) {
					$this->setValueInConfigFile($siteConfig, '$tupa_db_host', trim($this->config['dbhost']));
				} else {
					$this->message($head, 'DB host', 'DB host longer than 50 chars or is not alphnumeric (a-zA-Z0-9_.-:). It was not saved.', 3);
					$error = true;
				}
				if ($this->config['dbport'] <= 65535 && $this->config['dbport'] >= 1) {
					$this->setValueInConfigFile($siteConfig, '$tupa_db_port', trim($this->config['dbport']));
				} else {
					$this->message($head, 'DB port', 'Port has to be between 1 and 65535 (default 3306). It was not saved.', 3);
					$error = true;
				}
				if (strlen($this->config['dbuser']) < 51) {
					$this->setValueInConfigFile($siteConfig, '$tupa_db_username', trim($this->config['dbuser']));
				} else {
					$this->message($head, 'DB user', 'DB user longer than 50 chars is not allowed. It was not saved.', 3);
					$error = true;
				}
				if (strlen($this->config['dbpass']) < 51) {
					$this->setValueInConfigFile($siteConfig, '$tupa_db_password', trim($this->config['dbpass']));
				} else {
					$this->message($head, 'DB password', 'DB password longer than 50 chars is not allowed. It was not saved.', 3);
					$error = true;
				}
				if (strlen($this->config['dbdb']) < 51) {
					$this->setValueInConfigFile($siteConfig, '$tupa_db', trim($this->config['dbdb']));
				} else {
					$this->message($head, 'DB name', 'DB name longer than 50 chars is not allowed. It was not saved.', 3);
					$error = true;
				}

				// Write new config file
				if ($this->writeToConfig_control($siteConfig) == 'nochange') {
					$this->message('Change database settings', 'Failed', 'Could not update settings', 3);
					break;
				}
				// Load page again to fall back to login screen
				if (!$error) {
					header('Location: '. $this->action);
				}
				break;

			default:
				$this->checkDatabaseAccess($head);

				$databaseOptions = $this->getDatabaseOptions(TUPA_db);
				$subpart = 'updateDbConfig';
				$this->message($head, 'Enter new database settings', '<form action="'. $this->action .'" method="post">DB Host:
					<input type="text" name="config[dbhost]" value="'. TUPA_db_host .'" class="field">
					DB Port:
					<input type="text" name="config[dbport]" value="'. TUPA_db_port .'" class="field">
					DB User:
					<input type="text" name="config[dbuser]" value="'. TUPA_db_username .'" class="field">
					DB Password:
					<input type="password" name="config[dbpass]" value="'. TUPA_db_password .'" class="field">
					Database:
					<select name="config[dbdb]">'. $databaseOptions .'</select>
					<input type="hidden" name="subpart" value="'. htmlspecialchars($subpart) .'">
					<input type="submit" value="Set new config" class="button">
					</form>'
				 , 1);
				 break;
		}
	}


	/**
	 * Imports data from existing PDNS installation.
	 * Raw pdns, PowerAdmin or PowerDNS Administarion
	 *
	 * @return 	void
	 */
	function dataImport() {
		global $TUPA_CONF_VARS;

		$head = 'Data import/migration';
		$this->globalHead = $head;

		if (!$this->checkDatabaseAccess($head, true)) return;

		$this->message($head, 'Select source', '<form action="'. $this->action .'" method="post">I want to import/migrate from:
			<select name="config[source]" onchange="this.form.submit();" class="button">
				<option value="">&gt;&gt; Please select &lt;&lt;</option>
				<option value="pdns" '. ($this->config['source'] == 'pdns' ? 'selected' : '' ) .'>Raw PowerDNS</option>
				<option value="powerAdmin" '. ($this->config['source'] == 'powerAdmin' ? 'selected' : '' ) .'>PowerAdmin</option>
				<option value="powerDnsAdministration" '. ($this->config['source'] == 'powerDnsAdministration' ? 'selected' : '' ) .'>PowerDNS Administration</option>
				<option value="bind" '. ($this->config['source'] == 'bind' ? 'selected' : '' ) .'>Bind DNS</option>
			</select>&nbsp;<input type="submit" value="Go" class="button" />

			</form>
		');

		switch ($this->config['source']) {
			case 'pdns':
				if ($this->config['startMigration'] == 1) {
					$this->migratePdns();
					break;
				}

				$this->message($head, 'Import from Raw PDNS', 'Use this option if you want to import existing PDNS domains. This assigns all domains to the main admin user you created in the installer before.
				<form action="'. $this->action .'" method="post"><input type="submit" value="Start migration" class="button" /><input type="hidden" name="config[source]" value="'. $this->config['source'] .'" /><input type="hidden" name="config[startMigration]" value="1" />

				</form>');
				break;
			case 'powerAdmin':
				if ($this->config['startMigration'] == 1) {
					$this->migratePowerAdmin();
					break;
				}

				$this->message($head, 'Migrate from PowerAdmin', 'You can migrate your domains and users from an existing PowerAdmin installation. It was made for <strong>Version 1.2.7</strong>, but should also work with earlier/later versions if the database structure of the "zones" and "users" tables have not changed.
				<span style="color: red;">Please make a backup of all data before running this migration tool. <strong>It is still in experimental state</strong> and not very well tested yet.</span>
				You can give us some <a href="http://www.tupa-dns.org/Contact.9.0.html" target="_blank">feedback</a> if everything worked fine or you had problems with it.
				<form action="'. $this->action .'" method="post"><input type="submit" value="Start migration" class="button" /><input type="hidden" name="config[source]" value="'. $this->config['source'] .'" /><input type="hidden" name="config[startMigration]" value="1" />

				</form>');

				$this->message($head, 'Migration deletes data !!', 'When migrating from PowerAdmin you will lose two fields in database which are not used in TUPA:
				- column "comment" in table "zones" (Comments to single domains)
				- column "active" table "users" (Which enables or disables users)', 2);

				$this->messageFunc_nl2br = 0;
				$this->message($head, 'How are my existing users migrated?', '
				<table cellpadding="0" cellspacing="2">
					<tr>
						<td><strong>PowerAdmin user level&nbsp;&nbsp;&nbsp;</strong></td>
						<td><strong>TUPA permissions</strong></td>
					</tr>
					<tr>
						<td>1</td>
						<td>show/edit own domains</td>
					</tr>
					<tr>
						<td>5</td>
						<td>domain administrator</td>
					</tr>
					<tr>
						<td>10</td>
						<td>domain/user administrator</td>
					</tr>
				</table>
				', 1);

				break;
			case 'powerDnsAdministration':
				if ($this->config['startMigration'] == 1) {
					$this->migratePowerDnsAdministration();
					break;
				}

				$this->message($head, 'Migrate from PowerDNS Administrator', 'You can migrate your domains and users from an existing PowerDNS Administration installation. It was made for <strong>Version 0.4</strong>, but should also work with earlier/later versions if the database structure has not changed.
				<span style="color: red;">Please make a backup of all data before running this migration tool. <strong>It is still in experimental state</strong> and not very well tested yet.</span>
				You can give us some <a href="http://www.tupa-dns.org/Contact.9.0.html" target="_blank">feedback</a> if everything worked fine or you had problems with it.
				PowerDNS Administration has no SOA record in it\'s templates which is needed by TUPA. Please enter you primary nameserver and hostmaster e-Mail address:
				<form action="'. $this->action .'" onsubmit="if (validateForm(this,false,false,false,false,8)) { return true; } else { return false; };" method="post"><input type="text" name="config[soa_primary]" class="button" size="30" alt="custom" pattern="'. $TUPA_CONF_VARS['REGEX']['templateDomain'] .'" emsg="Please add a correct FQDN" value="'. $TUPA_CONF_VARS['DNS']['defaultSoaPrimary'] .'" />
				<input type="text" name="config[soa_hostmaster]" class="button" size="30" alt="email|1" emsg="Please enter a valid e-Mail address" value="'. $TUPA_CONF_VARS['DNS']['defaultSoaHostmaster'] .'" />
				<input type="submit" value="Start migration" class="button" /><input type="hidden" name="config[source]" value="'. $this->config['source'] .'" /><input type="hidden" name="config[startMigration]" value="1" />

				</form>');

				$this->messageFunc_nl2br = 0;
				$this->message($head, 'How are my existing users migrated?', '
				<table cellpadding="0" cellspacing="2">
					<tr>
						<td><strong>PowerDNS Administration&nbsp;&nbsp;&nbsp;</strong></td>
						<td><strong>TUPA permissions</strong></td>
					</tr>
					<tr>
						<td>Superuser</td>
						<td>domain/user administrator</td>
					</tr>
					<tr>
						<td>User administration</td>
						<td>user administrator</td>
					</tr>
					<tr>
						<td>New domains</td>
						<td>show/add own domains</td>
					</tr>
					<tr>
						<td>New templates</td>
						<td>show/add own templates</td>
					</tr>
					<tr>
						<td>Edit own domains</td>
						<td>show/edit own domains</td>
					</tr>
					<tr>
						<td>Edit other domains</td>
						<td>show/edit group domains</td>
					</tr>
					<tr>
						<td>Edit templates</td>
						<td>show/edit own templates</td>
					</tr>
					<tr>
						<td>Delete own domains</td>
						<td>show/delete own domains</td>
					</tr>
					<tr>
						<td>Delete other domains</td>
						<td>show/delete group domains</td>
					</tr>
					<tr>
						<td>Delete templates</td>
						<td>delete own templates</td>
					</tr>
					<tr>
						<td>Edit other domains &<br />Delete other domains</td>
						<td valign="top">domain administrator</td>
					</tr>
				</table>
				', 1);

				break;
			case 'bind':
				$this->message($head, 'Migrate from Bind DNS', 'To migrate from Bind zone files you have to convert and import them into your pdns database first.
				There is a program called zone2sql which is part of the PDNS package to convert Bind zone files to SQL statements. (<a href="http://downloads.powerdns.com/documentation/html/migration.html" target="_blank" >zone2sql manual</a>)
				After you imported the generated sql file into you database you can use the "Raw PowerDNS"-option in the selectbox above to make them visible in TUPA.');
				break;
			default:
				$this->message($head, 'Data import - migration (optional)', 'You can import/migrate from:
					- Raw PDNS database
					- PowerAdmin
					- PowerDNS Administration
					- Bind DNS
				', 1);
				//<input type="hidden" name="subpart" value="'. htmlspecialchars($subpart) .'">
				break;
		}

	}


	/**
	 * Migrates existing PDNS domains to TUPA
	 *
	 * @return 	void
	 */
	function migratePdns() {
		$head = $this->globalHead;

		if (!$this->checkDatabaseAccess($head, true)) return;

		// Check if needed tables exists
		if (!$GLOBALS['TUPA_DB']->admin_check_table_exists('domains')) {
			$this->message($head, 'Table not found', 'The table "domains" was not found in the database. This table holds the domains to import in TUPA. Are you sure you have a running PDNS Server with existing domains?', 3);
			return;
		}
		// Check if there are any records in the table
		$domains = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('id', 'domains', '', '', true);
		if ($domains['count'] == 0) {
			$this->message($head, 'No records in table', 'There are no records in the table "domains". Nothing to import.', 1);
			return;
		}

		if (!$GLOBALS['TUPA_DB']->admin_check_table_exists('domain_owners')) {
			$this->message($head, 'Table not found', 'The table "domain_owners" was not found in the database. Please update your database first.', 3);
			return;
		}
		// Check if there are any records in the table
		$domainOwn = $GLOBALS['TUPA_DB']->exec_SELECTgetIdList('dom_id', 'domain_owners', '', '', true);

		if ($domains['count'] == $domainOwn['count']) {
			$this->message($head, 'Records already imported', 'All existing domains were imported already.', 1);
			return;
		} elseif ($domains['count'] < $domainOwn['count']) {
			$deleteRelations = array_diff($domainOwn['list'], $domains['list']);
			$deleteRelations = implode(',', $deleteRelations);
			$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('domain_owners', 'dom_id IN ('. lib_DB::fullQuoteStrList($deleteRelations) .')');

			if (mysql_error()) {
				$this->message($head, 'Database error', 'An error occured while deleting relations:<br />'. mysql_error(), 3);
				return;
			}
			$this->message($head, 'Relation error', 'There were more domain-owner relation than domains. They were deleted successfully.', 1);
			return;
		}

		if ($domainOwn['count'] == 0) {	// All domains have to be imported
			$insertValues = $this->genMultiAdminInsertValues($domains['list']);
			$res = $GLOBALS['TUPA_DB']->exec_simpleINSERTquery('domain_owners', '(dom_id,usr_id)', $insertValues);

			if (mysql_error()) {
				$this->message($head, 'Database error', 'An error occured while inserting relations:<br />'. mysql_error(), 3);
				return;
			}
			$this->message($head, 'Import successful', 'Your existing PDNS domains were imported sucessfully. They should now be visible in TUPA.', -1);
		} elseif ($domainOwn['count'] > 0) {	// There are somedomains which are not owned by someone
			$missingDom = array_diff($domains['list'], $domainOwn['list']);
			$insertValues = $this->genMultiAdminInsertValues($missingDom);
			$res = $GLOBALS['TUPA_DB']->exec_simpleINSERTquery('domain_owners', '(dom_id,usr_id)', $insertValues);

			if (mysql_error()) {
				$this->message($head, 'Database error', 'An error occured while inserting missing relations:<br />'. mysql_error(), 3);
				return;
			}
			$this->message($head, 'Import successful', 'There were some relations already. The missing PDNS domains were imported sucessfully. They should now all be visible in TUPA.', -1);
		} else {		// Something is absolutly wrong if this occurs
			$this->message($head, 'Domain owners error', 'There was an error with domain owners. Execution aborted!', 3);
			return;
		}
	}


	/**
	 * Migrates existing PowerAdmin database to TUPA
	 *
	 * @return 	void
	 */
	function migratePowerAdmin() {
		$head = $this->globalHead;
		$error = array();
		$uFields = array();

		if (!$this->checkDatabaseAccess($head, true)) return;

		// Check tables and columns of PDNS Admin
		if (!$GLOBALS['TUPA_DB']->admin_check_table_exists('zones,users')) {
			$this->message($head, 'Table(s) not found', 'One or multiple tables of your PowerAdmin not found. Are you sure PowerAdmin is installed and running?', 3);
			return;
		}

		if (!$GLOBALS['TUPA_DB']->admin_check_column_exists('zones', 'id,domain_id,owner,comment') || !$GLOBALS['TUPA_DB']->admin_check_column_exists('users', 'fullname,description,active,level')) {
			$this->message($head, 'Column(s) not found', 'One or multiple needed columns of your PowerAdmin not found. Are you sure PowerAdmin is installed and running? Or you may already migrated you installation?', 3);
			return;
		}

		if (!$GLOBALS['TUPA_DB']->admin_check_table_exists('groups,authentication,logging,backup_config')) {
			$this->message($head, 'Table not found', 'One or multiple tables not found in the database. Please update your database first.<br /><strong>But don\'t drop or remove any fields!</strong>', 3);
			return;
		}

		// Add new group "Users"
		$insertFields = array(
			'name' => 'Users'
		);
		$GLOBALS['TUPA_DB']->exec_INSERTquery('groups', $insertFields);
		$uFields['grp_id'] = mysql_insert_id();

		// Get all user data
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'users', '');
		while ($row = mysql_fetch_assoc($res)) {
			// Convert permissions
			$oldPerm = $row['permission'];
			switch ($oldPerm) {
				case 1:		// Superadmin or "New domain"
					$uFields['perm_DOM_show'] = 1;
					$uFields['perm_DOM_edit'] = 1;
					break;
				case 5:
					$uFields['perm_ADM_domain'] = 1;
					break;
				case 10:
					if ($row['id'] == 1) {	// Main admin user
						$uFields['perm_ADM_admin'] = 1;
					} else {
						$uFields['perm_ADM_user'] = 1;
						$uFields['perm_ADM_domain'] = 1;
					}
					break;
			}

			$dataArr = lib_div::splitSpecialPartsFromFormdata($uFields, 'users');

			$res2 = $GLOBALS['TUPA_DB']->exec_UPDATEquery('users', 'id='. lib_DB::fullQuoteStr($row['id']) , $dataArr);
			if (mysql_error()) $error[] = htmlspecialchars($row['username']) .' / '. mysql_error();
		}

		if (count($error) > 0) {
			$errors = '';
			foreach ($error as $value) {
				$errors .= '<br />- '. $value;
			}
			$this->message($head, 'Migration failed', 'An error occured while migrating from PowerAdmin. The usersnames which made problems are:'. $errors, 3);
			return;
		}

		// Delete old columns
		$GLOBALS['TUPA_DB']->delColumn('users', 'active');
		$GLOBALS['TUPA_DB']->delColumn('users', 'level');
		$GLOBALS['TUPA_DB']->delColumn('zones', 'comment');

		// Change table name (zones)
		$GLOBALS['TUPA_DB']->renameTable('zones', 'domain_owners');

		// Set the field name changes in an array
		$changeFieldNames = array(
			'domain_owners' => array(
				0 => array(0 => 'domain_id', 1 => 'dom_id', 2 => 'int(11) NOT NULL default \'0\''),
				1 => array(0 => 'owner', 1 => 'usr_id', 2 => 'int(11) NOT NULL default \'0\'')
			),
			'users' => array(
				0 => array(0 => 'fullname', 1 => 'name', 2 => 'int(11) NOT NULL default \'0\''),
				0 => array(0 => 'description', 1 => 'notice', 2 => 'int(11) NOT NULL default \'0\'')
			)
		);

		if (!$GLOBALS['TUPA_DB']->changeFieldNames($changeFieldNames, true)) {
			$this->message($head, 'Database error', 'A database error occured while renaming fileds:<br />'. mysql_error(), 3);
			return;
		}

		$this->message($head, 'Migration successful', 'The migration from your PowerAdmin seems to be successfully finished. You should now be able to use TUPA with your existing users.<br /><strong>But you should now first go back to "Update database", check all checkboxes and write it to database.</strong>', -1);
	}


	/**
	 * Migrates existing Power DNS Administration database to TUPA
	 *
	 * @return 	void
	 */
	function migratePowerDnsAdministration() {
		global $TUPA_CONF_VARS;

		$head = $this->globalHead;
		$error = array();
		$uFields = array();

		if (!$this->checkDatabaseAccess($head, true)) return;

		// Check tables and columns of PDNS Admin
		if (!$GLOBALS['TUPA_DB']->admin_check_table_exists('domain_owners,templates,template_records,users')) {
			$this->message($head, 'Table(s) not found', 'One or multiple tables of your PowerDNS Administration not found. Are you sure PowerDNS Administration is installed and running?', 3);
			return;
		}

		if (!$GLOBALS['TUPA_DB']->admin_check_column_exists('users', 'company,permission') || !$GLOBALS['TUPA_DB']->admin_check_column_exists('domain_owners', 'domain_id,user_id') || !$GLOBALS['TUPA_DB']->admin_check_column_exists('templates', 'created_by') || !$GLOBALS['TUPA_DB']->admin_check_column_exists('template_records', 'template_id')) {
			$this->message($head, 'Column(s) not found', 'One or multiple needed columns of your PowerDNS Administration not found. Are you sure PowerDNS Administration is installed and running? Or you may already migrated you installation?', 3);
			return;
		}

		if (!$GLOBALS['TUPA_DB']->admin_check_table_exists('groups,authentication,logging,backup_config')) {
			$this->message($head, 'Table not found', 'One or multiple tables not found in the database. Please update your database first.<br /><strong>But don\'t drop or remove any fields!</strong>', 3);
			return;
		}

		// Get all user data
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'users', '');
		while ($row = mysql_fetch_assoc($res)) {
			// Check/Add group from company
			$res2 = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'groups', 'name LIKE '. lib_DB::fullQuoteStr($row['company']));
			if (mysql_num_rows($res2) == 0) {
				// Insert new group
				$insertFields = array(
					'name' => lib_DB::fullQuoteStr($row['company'])
				);
				$GLOBALS['TUPA_DB']->exec_INSERTquery('groups', $insertFields);
				$uFields['grp_id'] = mysql_insert_id();
			} else {
				// Set group ID
				$row2 = mysql_fetch_assoc($res2);
				$uFields['grp_id'] = $row2['id'];
			}

			// Convert permissions
			$oldPerm = $row['permission'];
			for ($i=0; $i<strlen($oldPerm); $i++) {
				$sPerm = $oldPerm[$i];
				switch ($i) {
					case 0:		// Superadmin or "New domain"
						if ($sPerm == 2) {
							if ($row['id'] == 1) {	// Main admin user
								$uFields['perm_ADM_admin'] = 1;
							} else {
								$uFields['perm_ADM_user'] = 1;
								$uFields['perm_ADM_domain'] = 1;
							}
						} else {
							if (!$sPerm) $uFields['perm_DOM_show'] = $sPerm;
							$uFields['perm_DOM_add'] = $sPerm;
						}
						break;
					case 1:
						if (!$sPerm) $uFields['perm_TMPL_show'] = $sPerm;
						$uFields['perm_TMPL_add'] = $sPerm;
						break;
					case 2:
						if (!$sPerm) $uFields['perm_DOM_show'] = $sPerm;
						$uFields['perm_DOM_edit'] = $sPerm;
						break;
					case 3:
						if (!$sPerm) $uFields['perm_DOMGROUP_show'] = $sPerm;
						$uFields['perm_DOMGROUP_edit'] = $sPerm;
						break;
					case 4:
						if (!$sPerm) $uFields['perm_TMPL_show'] = $sPerm;
						$uFields['perm_TMPL_edit'] = $sPerm;
						break;
					case 5:
						if (!$sPerm) $uFields['perm_DOM_show'] = $sPerm;
						$uFields['perm_DOM_del'] = $sPerm;
						break;
					case 6:
						if (!$sPerm) $uFields['perm_DOMGROUP_show'] = $sPerm;
						$uFields['perm_DOMGROUP_del'] = $sPerm;
						break;
					case 7:
						if (!$sPerm) $uFields['perm_TMPL_show'] = $sPerm;
						$uFields['perm_TMPL_del'] = $sPerm;
						break;
					case 8:
						$uFields['perm_ADM_user'] = $sPerm;
						break;
				}
			}

			// Check special perms
			if (strlen($oldPerm) > 1 && $oldPerm[3] && $oldPerm[6]) {
				$uFields['perm_DOM_show'] = 0;
				$uFields['perm_DOM_add'] = 0;
				$uFields['perm_DOM_edit'] = 0;
				$uFields['perm_DOMGROUP_edit'] = 0;
				$uFields['perm_DOM_del'] = 0;
				$uFields['perm_DOMGROUP_del'] = 0;
				$uFields['perm_ADM_domain'] = 1;
			}

			$dataArr = lib_div::splitSpecialPartsFromFormdata($uFields, 'users');

			$res2 = $GLOBALS['TUPA_DB']->exec_UPDATEquery('users', 'id='. lib_DB::fullQuoteStr($row['id']) , $dataArr);
			if (mysql_error()) $error[] = htmlspecialchars($row['username']) .' / '. mysql_error();
		}


		if (count($error) > 0) {
			$errors = '';
			foreach ($error as $value) {
				$errors .= '<br />- '. $value;
			}
			$this->message($head, 'Migration failed', 'An error occured while migrating from PowerDNS Administration. The usersnames which made problems are:'. $errors, 3);
			return;
		}

		// Delete old columns
		$GLOBALS['TUPA_DB']->delColumn('users', 'company');
		$GLOBALS['TUPA_DB']->delColumn('users', 'permission');

		// Set the field name changes in an array
		$changeFieldNames = array(
			'domain_owners' => array(
				0 => array(0 => 'domain_id', 1 => 'dom_id', 2 => 'int(11) NOT NULL default \'0\''),
				1 => array(0 => 'user_id', 1 => 'usr_id', 2 => 'int(11) NOT NULL default \'0\'')
			),
			'templates' => array(
				0 => array(0 => 'created_by', 1 => 'usr_id', 2 => 'int(11) NOT NULL default \'0\'')
			),
			'template_records' => array(
				0 => array(0 => 'template_id', 1 => 'tmpl_id', 2 => 'int(11) NOT NULL default \'0\'')
			)
		);

		if (!$GLOBALS['TUPA_DB']->changeFieldNames($changeFieldNames, true)) {
			$this->message($head, 'Database error', 'A database error occured while renaming fileds:<br />'. mysql_error(), 3);
			return;
		}

		// Remove domain placeholder "$DOMAIN" in name field of template records and replace it in content fields
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'template_records', '');
		while ($row = mysql_fetch_assoc($res)) {
			$name = str_replace('$DOMAIN', '', $row['name']);
			if ($name[strlen($name) - 1] == '.') $name = substr($name, 0, strlen($name) - 1);
			$content = str_replace('$DOMAIN', '%DOMAIN%', $row['content']);
			$updateFields = array(
				'name' => $name,
				'content' => $content,
			);
			$res2 = $GLOBALS['TUPA_DB']->exec_UPDATEquery('template_records', 'id='. lib_DB::fullQuoteStr($row['id']), $updateFields);
			if (mysql_error()) $error = true;
		}
		if ($error === true) {
			$this->message($head, 'Error(s) while migrating template records', 'While replacing PowerDNS Administration\'s domain placeholder whith the one from TUPA some errors occured. You have to check them in the administration interface!<br />'. mysql_error(). $errors, 2);
		}

		// Fix prios which are 0 and not allowed in TUPA
		$updateFields = array(
			'prio' => null
		);
		$res = $GLOBALS['TUPA_DB']->exec_UPDATEquery('template_records', 'prio=0', $updateFields);

		// PowerDNS Administration has no SOA records in tables which are needed in TUPA. So we add a default one.
		// Set the SOA record
		$dataArr = array();
		$dataArr['ttl'] = $TUPA_CONF_VARS['DNS']['defaultSoaTTL'];
		$dataArr['type'] = 'SOA';
		$dataArr['content'] = $this->config['soa_primary'] .' '. $this->config['soa_hostmaster'] .' 0 '. $TUPA_CONF_VARS['DNS']['defaultSoaRefresh'] .' '. $TUPA_CONF_VARS['DNS']['defaultSoaRetry'] .' '. $TUPA_CONF_VARS['DNS']['defaultSoaExpire'] .' '. $TUPA_CONF_VARS['DNS']['defaultSoaTTL'];

		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'templates', '');
		while ($row = mysql_fetch_assoc($res)) {
			// Make sure that template really has no SOA record
			$res2 = $GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'template_records', 'tmpl_id='. lib_DB::fullQuoteStr($row['id']) .' AND type=\'SOA\'');
			if (mysql_num_rows($res2) == 0) {
				// Add SOA record for template
				$dataArr['tmpl_id'] = $row['id'];
				$res2 = $GLOBALS['TUPA_DB']->exec_INSERTquery('template_records', $dataArr);
				if (mysql_error()) $error = true;
			}
		}
		if ($error === true) {
			$this->message($head, 'Error(s) while creating SOA records', 'Some SOA records for your templates are maybe not created sucessfully: You have to open them in the administration interface and safe them again manually to make sure the SOA exists in database, or there will be errors when creating now domains with a template.<br />'. mysql_error(). $errors, 2);
		}

		$this->message($head, 'Migration successful', 'The migration from your PowerDNS Administration seems to be successfully finished. You should now be able to use TUPA with your existing users.<br /><strong>But you should now first go back to "Update database", check all checkboxes and write it to database.</strong>', -1);
	}


	/**
	 * Generates extended mysql values to insert multiple records in one query
	 *
	 * @param 	string		List of values
	 * @return 	string		List to use in mysql query
	 */
	function genMultiAdminInsertValues($list) {
		$insertValues = array();
		foreach ($list as $value) {
			$insertValues[] = '('. $value .',1)';
		}
		return implode(',', $insertValues);
	}


	/**
	 * Checks the database access
	 *
	 * @param 	string		Head (Title)
	 * @param 	boolean	Don't print success message
	 * @return 	void
	 */
	function checkDatabaseAccess($head, $hideSucc=false) {
		$cInfo='
			Username: <strong>'. TUPA_db_username .'</strong>
			Password: <strong>'. TUPA_db_password .'</strong>
			Host: <strong>'. TUPA_db_host .'</strong>
		';
		if ($GLOBALS['TUPA_DB']->sql_pconnect(TUPA_db_host, TUPA_db_port, TUPA_db_username, TUPA_db_password))	{
			if (!$hideSucc) $this->message($head, 'Connected to SQL database successfully', trim($cInfo), -1, 1);
			if (!TUPA_db)	{
				$this->message($head, 'No database selected', 'Currently you have no database selected. Please select one.', 3);
				return false;
			} elseif (!$GLOBALS['TUPA_DB']->sql_select_db(TUPA_db))  {
				$this->message($head, 'Database', TUPA_db .' could not be selected as database!	Please select another one.', 3, 1);
				return false;
			} else  {
				if (!$hideSucc) $this->message($head, 'Database', '<strong>'. TUPA_db .'</strong> is selected as database.', -1, 1);
				return true;
			}
		} else {
			$this->message($head, 'Could not connect to SQL database!', 'Connecting to SQL database failed with these settings:
			'. trim($cInfo) .'

			Make sure you\'re using correct database settings.', 3);
			return false;
		}
	}



	/**
	 * Function tho check the current database with the "new" version
	 *
	 * @return 	void
	 */
	function updateDb() {
		$head = 'Update database';

		if (!$this->checkDatabaseAccess($head, true)) return;

		switch ($this->subpart) {
			default:
				if($fileContent = lib_div::getFileContent($this->installSqlFile))    {
					$FDfile = $this->getFieldDefinitions_sqlContent($fileContent);
					if (!count($FDfile))	{
						die ("Error: There were no 'CREATE TABLE' definitions in the provided file");
					}

					// Updating database...
					if (is_array($this->dbUpdateFields))	{
						//$this->message('SQL', 'Output', print_r($this->dbUpdateFields, true), 1);
						$FDdb = $this->getFieldDefinitions_database();
						$diff = $this->getDatabaseExtra($FDfile, $FDdb);
						$update_statements = $this->getUpdateSuggestions($diff);
						$diff = $this->getDatabaseExtra($FDdb, $FDfile);
						$remove_statements = $this->getUpdateSuggestions($diff,"remove");

						$this->performUpdateQueries($update_statements["add"], $this->dbUpdateFields);
						$this->performUpdateQueries($update_statements["change"], $this->dbUpdateFields);
						$this->performUpdateQueries($remove_statements["change"], $this->dbUpdateFields);
						$this->performUpdateQueries($remove_statements["drop"], $this->dbUpdateFields);

						$this->performUpdateQueries($update_statements["create_table"], $this->dbUpdateFields);
						$this->performUpdateQueries($remove_statements["change_table"], $this->dbUpdateFields);
						$this->performUpdateQueries($remove_statements["drop_table"], $this->dbUpdateFields);
					}

					//$this->message('SQL', 'Output', print_r($FDfile, true), 1);
					$FDdb = $this->getFieldDefinitions_database();
					//$this->message('SQL', 'Output', print_r($FDdb, true), 1);
					$diff = $this->getDatabaseExtra($FDfile, $FDdb);
					//$this->message('SQL', 'Output', print_r($diff, true), 1);
					$update_statements = $this->getUpdateSuggestions($diff);
					$diff = $this->getDatabaseExtra($FDdb, $FDfile);
					$remove_statements = $this->getUpdateSuggestions($diff,"remove");
					$tLabel = "Update database tables and fields";

					$this->messageFunc_nl2br = 0;
					if ($remove_statements || $update_statements)	{
						$formContent = $this->generateUpdateDatabaseForm("get_form",$update_statements,$remove_statements);
						$this->message($tLabel,'Table and field definitions should be updated','
						There seems to be a number of differencies between the database and the selected SQL-file.
						Please select which statements you want to execute in order to update your database:<br /><br />
						If you want to migrate you existing "PowerDNS Administration"- or "PowerAdmin"- installation, <strong>don\'t</strong>
						select the "remove" and "drop" checkboxes before you have migrated your installation!<br />
						'.$formContent
						,2);
					} else {
						$formContent = $this->generateUpdateDatabaseForm("get_form",$update_statements,$remove_statements);
						$this->message($tLabel,'Table and field definitions are OK.','
						The tables and fields in the current database corresponds perfectly to the database in the selected SQL-file.
						',-1);
					}

					//$this->message('SQL', 'Output', print_r($FDfile, true), 1);
				}
		}
	}


	/**
	 * Function to add an admin user if not already exists
	 *
	 * @return 	void
	 */
	function addAdminUser() {
		$head = 'Add admin user';

		if (!$this->checkDatabaseAccess($head, true)) return;

		switch ($this->subpart) {
			case 'addAdmin':
				$adminPassword = md5($this->config['adminPassword']);

				// Add Administration group (ID 1)
				$dbArr = array(
					'id' => 1,
					'name' => 'Administration',
					'max_users' => 0,
					'max_domains' => 0,
					'max_templates' => 0
				);
				$GLOBALS['TUPA_DB']->exec_INSERTquery('groups', $dbArr);
				if (mysql_error()) {
					$this->message($head, 'Group creation', 'Could not add group for admin user. This is not normal! Error:
					'. mysql_error(), 3);
					break;
				} else {
					$this->message($head, 'Group created', 'Group created successfully.', -1);
				}

				// Add admin user
				$dbArr = array(
					'id' => 1,
					'username' => 'admin',
					'password' => $adminPassword,
					'name' => 'Admin',
					'firstname' => 'User',
					'email' => 'your@email.com',
					'grp_id' => 1,
					'max_domains' => 0,
					'max_templates' => 0,
					'permissions' => 'a:6:{s:3:"ADM";a:3:{s:5:"admin";s:1:"1";s:4:"user";s:0:"";s:6:"domain";s:0:"";}s:3:"USR";a:4:{s:4:"show";s:0:"";s:3:"add";s:0:"";s:4:"edit";s:0:"";s:6:"delete";s:0:"";}s:3:"DOM";a:4:{s:4:"show";s:0:"";s:3:"add";s:0:"";s:4:"edit";s:0:"";s:6:"delete";s:0:"";}s:8:"DOMGROUP";a:4:{s:4:"show";s:0:"";s:3:"add";s:0:"";s:4:"edit";s:0:"";s:6:"delete";s:0:"";}s:4:"TMPL";a:4:{s:4:"show";s:0:"";s:3:"add";s:0:"";s:4:"edit";s:0:"";s:6:"delete";s:0:"";}s:9:"TMPLGROUP";a:4:{s:4:"show";s:0:"";s:3:"add";s:0:"";s:4:"edit";s:0:"";s:6:"delete";s:0:"";}}'
				);
				$GLOBALS['TUPA_DB']->exec_INSERTquery('users', $dbArr);
				if (mysql_error()) {
					$this->message($head, 'User creation', 'Could not add admin user. This is not normal! Error:
					'. mysql_error(), 3);
					break;
				} else {
					$this->message($head, 'User created', 'User "admin" created successfully.', -1);
				}
				break;

			default:
				// Check if admin user already exists (when the user is updating to new version)
				$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('id', 'users', 'id=\'1\'');
				if (mysql_num_rows($res) == 1) {
					$this->message($head, 'Admin user exists', 'The admin user (ID 1) already exists in database from earlier installation. So you can go to the next step.', 1);
				} else {
					$subpart = 'addAdmin';
					$this->message($head, 'Admin user password', '<form action="'. $this->action .'" name="chpwd" onSubmit="if(this.elements[0].value!=this.elements[1].value) { alert(\'The password fields don\\\'t match!\'); return false; }" method="post">Enter password for user "admin":
						<input type="password" name="config[adminPassword]" class="field"><br />Enter again:
						<input type="password" name="adminPassword_check" class="field">
						<input type="hidden" name="subpart" value="'. htmlspecialchars($subpart) .'">
						<input type="submit" value="Create admin user" class="button"><br />
						</form>'
					 , 1);
				}
				break;
		}
	}






	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$type: ...
	 * @param	[type]		$arr_update: ...
	 * @param	[type]		$arr_remove: ...
	 * @param	[type]		$action_type: ...
	 * @return	[type]		...
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function generateUpdateDatabaseForm($type, $arr_update, $arr_remove)	{
		switch($type)	{
			case "get_form":
				$content="";
				$content.=$this->generateUpdateDatabaseForm_checkboxes($arr_update["add"],"Add fields");
				$content.=$this->generateUpdateDatabaseForm_checkboxes($arr_update["change"],"Changing fields",1,0,$arr_update["change_currentValue"]);
				$content.=$this->generateUpdateDatabaseForm_checkboxes($arr_remove["change"],"Remove unused fields (rename with prefix)",$this->setAllCheckBoxesByDefault,1);
				$content.=$this->generateUpdateDatabaseForm_checkboxes($arr_remove["drop"],"Drop fields (really!)",$this->setAllCheckBoxesByDefault);

				$content.=$this->generateUpdateDatabaseForm_checkboxes($arr_update["create_table"],"Add tables");
				$content.=$this->generateUpdateDatabaseForm_checkboxes($arr_remove["change_table"],"Removing tables (rename with prefix)",$this->setAllCheckBoxesByDefault,1,$arr_remove["tables_count"],1);
				$content.=$this->generateUpdateDatabaseForm_checkboxes($arr_remove["drop_table"],"Drop tables (really!)",$this->setAllCheckBoxesByDefault,0,$arr_remove["tables_count"],1);

				$form = $this->getUpdateDbFormWrap($content);
				return $form;
			break;
			default:
			break;
		}
	}

	/**
	 * [Describe function...]
	 *
	 * @param	[type]		$action_type: ...
	 * @param	[type]		$content: ...
	 * @param	[type]		$label: ...
	 * @return	[type]		...
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function getUpdateDbFormWrap($content, $label="Write to database")	{
		$form = '<form action="'. $this->action .'" method="POST">'.$content.'<br /><input type="submit" value="'.$label.'" class="button" /></form>';
		return $form;
	}


	/**
	 * Returns the options for an selectbox of available databases (with access-check based on username/password)
	 *
	 * @return	[type]		...
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function getDatabaseOptions($selectedId='') {
		$dbArr=array();
		$options = '';
		if ($GLOBALS['TUPA_DB']->sql_pconnect(TUPA_db_host, TUPA_db_port, TUPA_db_username, TUPA_db_password))	{
			$dbArr = $GLOBALS['TUPA_DB']->admin_get_dbs();
		}
		foreach ($dbArr as $value) {
			$selected = $value == $selectedId ? 'selected' : '';
			$options .= '<option value="'. $value .'" '. $selected .'>'. $value .'</option>';
		}
		if ($options == '') {
			$options .= '<option value="">[ no databases found ]</option>';
		} else {
			$options =  '<option value="">[ select database ]</option>'. $options;
		}
		return $options;
	}



	/**
	 * Setting a message in the message-log and sets the fatalError flag if error type is 3.
	 *
	 * @return 	void
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function message($head, $short_string="", $long_string="", $type=0)	{	// type: -1=OK sign, 0=message, 1=notification, 2=warning , 3=error
		if ($type==3) { $this->fatalError=1; }
		if ($this->messageFunc_nl2br)	{
			$long_string = nl2br(trim($long_string));
		} else {
			$long_string = trim($long_string);
		}
		if (!$this->silent) $this->printSection($head, $short_string, $long_string, $type);
	}




	/**
	 * This "prints" a section with a message to the ->sections array
	 *
	 * @param	[type]		$head: ...
	 * @param	[type]		$short_string: ...
	 * @param	[type]		$long_string: ...
	 * @param	[type]		$type: ...
	 * @return	[type]		...
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function printSection($head, $short_string, $long_string, $type)	{
		$icon='';

		$bgCol =' bgcolor=#bad3dd';
		switch($type)	{
			case "3":
				$bgCol =' bgcolor="red"';
				$icon = 'images/icons/error.gif';
			break;
			case "2":
				$icon = 'images/icons/warning.gif';
			break;
			case "1":
				$icon = 'images/icons/note.gif';
			break;
			case "-1":
				$icon = 'images/icons/ok.gif';
			break;
			default:
				$bgCol =' bgcolor="#e8dbb7"';
			break;
		}
		if (!trim($short_string) && $long_string) {
			$this->sections[$head][] = $long_string;
		} elseif (!trim($short_string)) {
			$this->sections[$head][] = '';
		} else {
			$this->sections[$head][ ]= '
			<tr><td'.$bgCol.' nowrap>'.($icon?'<img src="'.$icon.'" width="18" height="16" align="top">':'').'<span class="msg-short">'.$short_string.'</span></td></tr>'.(trim($long_string)?'
			<tr><td><p class="msg-long">'.$long_string.'</p></td></tr>' : '');
		}
	}


		/**
	 * This prints all the messages in the ->section array
	 *
	 * @return	[type]		...
	 * @author  			Kaspar Skarhoj <kasperYYYY@typo3.com>
	 */
	function printAll()	{
		reset($this->sections);
		$out="";
		while(list($header,$valArray)=each($this->sections))	{
			$out.='
			<tr><td><span class="msg-head"><br />'.$header.'</span></td></tr>
			';
			$out.=implode($valArray,chr(10));
		}
		return '<table width="100%" border="0" cellpadding="2" cellspacing="2">'.$out.'</table>';
	}



	/**
	 * This wraps and returns the main content of the page into proper html-code.
	 *
	 * @param	string		Site content
	 * @return	string		Wrapped content
	 */
	function outputWrapper($content)	{
		$out = '
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta name="GENERATOR" content="TUPA-'. TUPA_branch .', http://www.whtiy.ch, &#169; Urs Weiss 2005-2006." />
 		<title>TUPA Installer</title>
 		<link rel="stylesheet" type="text/css" href="styles.css" />';

	if ($this->part == 'data_import' && $this->config['source'] == 'powerDnsAdministration') {
		$out .= '
		<script type="text/javascript" src="../lib/fValidate/fValidate.config.js"></script>
		<script type="text/javascript" src="../lib/fValidate/fValidate.core.js"></script>
		<script type="text/javascript" src="../lib/fValidate/fValidate.lang-enUS.js"></script>
		<script type="text/javascript" src="../lib/fValidate/fValidate.validators.js"></script>';
	}

		$out .= '
	</head>
	<body bgcolor="white" alink="maroon" link="maroon" vlink="maroon">'.$this->contentBeforeTable.'
		<div align="center">
			<div id="main-div">
				<div align="center"><span class="site-title">TUPA Installer</span></div>'.
				//'.($this->step?$this->stepHeader():$this->menu()).$content.'<HR>'.$this->note123().$this->endNotes().'
				$this->menu() . $content .'
			</div>
		</div>
	</body>
</html>';
		return $out;
	}



	/**
	 * Generates the installer menu
	 *
	 * @return	string		Menu
	 */
	function menu()	{
		if (!$this->loggedin) return;

		reset($this->menuitems);
		$c=0;
		$out=array();
		while(list($k,$v) = each($this->menuitems))	{
			$bgColor = ($this->part==$k ? ' bgColor="#bad3dd"' : ' bgColor="#F4F0E8"');
			$c++;
			$out[]='<tr><td onclick="location.href=\''. $this->scriptSelf.'?&part='. $k .'\'"'.$bgColor.'><a href="'.$this->scriptSelf.'?&part='. $k .'">'.$c.': '.$v.'</a></td></tr>';
		}

		$code = '<table border="0" cellpadding="1" cellspacing="1">'.implode($out,chr(10)).'</table>';
		$code = '<table border="0" cellpadding="0" cellspacing="0" bgColor="#9f9f9f"><tr><td>'.$code.'</td></tr></table>';
		return '<div align="center" id="menu">'.$code.'</div>';
	}


	/**
	 * This creates a link to the given $url. If $link is set, that'll be the link-text
	 *
	 * @param	string		URL
	 * @param	string		Text of link
	 * @param	string		target of link
	 * @return	string		Full link
	 */
	function linkIt($url, $link='', $target='')	{
		return '<a href="'.$url.'" '. ($target ? 'target="'. $target .'"' : '') .'>'.($link?$link:$url).'</a>';
	}
}

$INSTALL = new installer();
$INSTALL->init();
?>