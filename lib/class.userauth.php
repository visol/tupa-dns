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
 * Authentication handling. Login/Logout/Permissions os users
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

/*
If the user isn't logged on or there is no session or the session terminated,
the user will be redirected to the login page.

If the user is already logged in and there is a valid session, hewill be redirected
to the member page. If the log in form isn't complete or the username or the
password is wrong, the function will return an error.
*/

class lib_userauth {
	// $_SESSION variables
	var $session_ip = 'ip';
	var $session_uid = 'uid';
	var $session_lastAccess = 'lastAccess';

	// declare $_POST variables
	var $post_username = 'username';
	var $post_password = 'pfield';

	// Database field:
	var $DB_table_name = 'users'; // enter the name of the table where the user data is saved
	var $DB_field_username = 'username'; // enter the name of the field where the usernames are stored
	var $DB_field_password = 'password'; // enter the name of the field where the passwords are stored
	var $DB_field_uid = 'id'; // enter the name of the field where the user id's are stored

	// Other variables
	var $member_area = 'start.php'; // page only for logged in members
	var $login_page = 'index.php'; // page with the login form
	var $login_page_fb = 'index.html'; // Fallback page if login page not exists (can happen if init.php was called inside a subdirectory)
	var $logout_page = 'logout.php'; // page with the login form

	var $username;
	var $password;
	var $uid;



	/**
	 * Verify if user has got a session and if the user's IP corresonds to the IP in the session.
	 *
	 * @return	boolean
	 */
	function verifySession() {
		global $TUPA_CONF_VARS;
		if (!isset($_SESSION[$this->session_ip]) || !isset($_SESSION[$this->session_uid]) || $_SESSION[$this->session_ip] != $_SERVER['REMOTE_ADDR']) {
			return false;
		} else {
			// **********************************************
			// Set / Get the session time, check it and:
			// - set current time when valid
			// - destroy session and logout when invalid
			// **********************************************
			// Set configured maxlifetime from config
			$sessionTTL = $TUPA_CONF_VARS['SYS']['sessionTTL'];
			ini_set('session.gc_maxlifetime', $sessionTTL);
			if (session_is_registered($this->session_lastAccess) && $_SESSION[$this->session_lastAccess] > 0) {
				if (time() > ($_SESSION[$this->session_lastAccess] + $sessionTTL)) {
					$this->logout(true);
				}
			}
			$_SESSION[$this->session_lastAccess] = time();
			return true;
		}
	}


	/**
	 * redirect the browser to the value in $page.
	 *
	 * @param	string		page to redirect to
	 */
	function redirect($page) {
		if ($page == $this->login_page) {
			if (!file_exists($page)) {
				$page = $this->login_page_fb;
			}
		}

		header("Location: ".$page);
		exit();
	}


	/**
	 * Verify username, password, ip and hash with MySQL database.
	 *
	 * @return	boolean
	 */
	function verifyDB() {
		global $TUPA_CONF_VARS, $LANG;

		// Get authentication infos
		$sessionId = session_id();
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'authentication', 'sessionid='. lib_DB::fullQuoteStr($sessionId));
		if (mysql_num_rows($res) == '1') {
			$authData = mysql_fetch_assoc($res);
		} else {
			lib_logging::addLogMessage('auth', 'login', 'error', 'logMsgAuthNoHash', array('userName'=>$this->username));
			return $LANG->getLang('loginErrorUser');
		}

		// Check time (hash is only valid a specified time)
		$hashValidTime = $TUPA_CONF_VARS['SYS']['loginHashTimeout'];
		if (time() - $authData['tstamp'] > $hashValidTime) {
			lib_logging::addLogMessage('auth', 'login', 'error', 'logMsgHashTimeout', array('userName'=>$this->username));
			return $LANG->getLang('loginErrorTimeout');
		}

		// Check remote IP
		if ($authData['ip'] != $_SERVER['REMOTE_ADDR']) {
			lib_logging::addLogMessage('auth', 'login', 'error', 'logMsgAuthIpNotMatch', array('userName'=>$this->username));
			return $LANG->getLang('loginErrorIp');
		}

		// Everything ok, check the password
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', $this->DB_table_name, '`'. $this->DB_field_username .'` = '. lib_DB::fullQuoteStr($this->username));
		$row = mysql_fetch_assoc($res);
		$num = mysql_num_rows($res);

		if($num == 1) {
			$dbPass = md5($row[$this->DB_field_password] . $authData['hash']);
			if ($this->password == $dbPass) {
				$this->uid = $row[$this->DB_field_uid];
				// remove db record
				$GLOBALS['TUPA_DB']->exec_DELETEquery('authentication', 'sessionid='. lib_DB::fullQuoteStr($authData['sessionid']));
	        			return true;
			} else {
				lib_logging::addLogMessage('auth', 'login', 'security', 'logMsgWrongPassword', array('userName'=>$this->username));
				return $LANG->getLang('loginErrorUser');
			}
	    	} else {
	    		lib_logging::addLogMessage('auth', 'login', 'security', 'logMsgWrongUserPassword', array('userName'=>$this->username));
	        		return $LANG->getLang('loginErrorUser');
		}
	}


	/**
	 * Write username, email and IP into the session.
	 *
	 */
	function writeSession() {
		$_SESSION[$this->session_ip] = $_SERVER['REMOTE_ADDR'];
		$_SESSION[$this->session_uid] = $this->uid;
	}


	/**
	 * Verify if login form fields were filled out.
	 *
	 * @return	boolean
	 */
	function verifyForm() {
		if (isset($_POST[$this->post_username]) && isset($_POST[$this->post_password]) && $_POST[$this->post_username] != "" && $_POST[$this->post_password] != "") {
			$this->username = $_POST[$this->post_username];
			$this->password = $_POST[$this->post_password];
			return true;
	    	} else {
	        		return false;
		}
	}


	/**
	* Check login
	* If the user is already logged in or there is a valid session,
	* he will be redirected to the configured page. If the log in
	* form isn't completed or the username or the password is
	* wrong, the function will return the error message.
	 *
	 * @return	mixed		boolean or error message
	 */
	function login() {
		global $TUPA_CONF_VARS, $LANG;

		// Check maintenance
		if ($TUPA_CONF_VARS['SYS']['maintenanceEnabled'] && @$_POST[$this->post_username] != 'admin') {
			return;
		}

		// verify if user is already logged in
		$v_session = $this->verifySession();
		if ($v_session) {
		    	$this->redirect($this->member_area);
		}

		// Verify if cookies are enabled (check the session entrie)
		if (!isset($_COOKIE['tupa_dns']) && isset($_POST[$this->post_username]) && isset($_POST[$this->post_password])) {
			lib_logging::addLogMessage('auth', 'login', 'error', 'logMsgNoCookies', array('userName'=>$_POST[$this->post_username]));
			return $LANG->getLang('loginErrorCookie');
		}

		// verify if login form is complete
		$v_form = $this->verifyForm();
		if (!$v_form) {
			if (isset($_POST[$this->post_username]) && isset($_POST[$this->post_password])) {
				return $LANG->getLang('loginErrorForm');
			}
		}

		// verify if form's data coresponds to database's data
		if ($v_form) {
			$v_db = $this->verifyDB();
			if ($v_db === true) {
				$this->writeSession();
				lib_logging::addLogMessage('auth', 'login', 'info', 'logMsgLoginSuccess');
				$this->redirect($this->member_area);
			} else {
				return $v_db;
			}
		}
	}


	/**
	 * Logout the user.
	 *
	 */
	function logout($expired=false) {
		if ($expired) {
			lib_logging::addLogMessage('auth', 'logout', 'info', 'logMsgSessionExpired');
		} else {
			lib_logging::addLogMessage('auth', 'logout', 'info', 'logMsgLogoutSuccess');
		}
		$_SESSION = array();
		session_destroy();
		header("Location: ".$this->login_page);
	}


	/**
	* Check if user is logged in.
	*  If the user isn't logged in on or the session is terminated, the
	* user will be redirected to the login page.
	*
	*/
	function loggedin() {
		// verify if user is already logged in
		$v_session = $this->verifySession();
		if (!$v_session) {
			$this->redirect($this->login_page);
		}
	}


	/**
	 * Write authentication hash to database.
	 * The authentication hash is generated while loading the login page and
	 * used to additionaly crypt the password before submitting. So the
	 * subbmitted, crypted password is different every time. The hash is only
	 * valid a defined time.
	 *
	 * @param	string		generated md5 hash
	 */
	function setAuthHash($tmpHash) {
		global $TUPA_CONF_VARS;

		$data = array();
		$data['tstamp'] = time();
		$data['sessionid'] = session_id();
		$data['ip'] = $_SERVER['REMOTE_ADDR'];
		$data['hash'] = $tmpHash;

		// Check if an identical session id allready exists in db
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('sessionid', 'authentication', 'sessionid='. lib_DB::fullQuoteStr($data['sessionid']));
		if (mysql_num_rows($res) > '0') {
			// Update record
			$GLOBALS['TUPA_DB']->exec_UPDATEquery('authentication', 'sessionid='. lib_DB::fullQuoteStr($data['sessionid']), $data);
		} else {
			// Insert data
			$GLOBALS['TUPA_DB']->exec_INSERTquery('authentication', $data);
		}

		// Clean up auth table by removing all records wich are to old
		if (rand(1,100) <= $TUPA_CONF_VARS['SYS']['authKeyMaintenance']) {
			$cleanTo = time() - $TUPA_CONF_VARS['SYS']['loginHashTimeout'] - 10;
			$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('authentication', 'tstamp<='. lib_DB::fullQuoteStr($cleanTo));
		}
	}



	/**
	 * Gets the permissions from user database record and unserializes it.
	 *
	 * @return	array		User permissions array
	 */
	function getPermissions() {
		if (isset($_SESSION[$this->session_uid])) {

			$query = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', $this->DB_table_name, "`". $this->DB_field_uid ."` = '". $_SESSION[$this->session_uid] ."'");
		        	$row = mysql_fetch_assoc($query);
			$num = mysql_num_rows($query);
			if($num == 1) {
				return unserialize($row['permissions']);
			} else {
				$this->logout();
			}
		}
	}



	/**
	 * Gets the user preferences from users record and override the default values.
	 *
	 */
	function loadUserPreferences($usr_id) {
		if (isset($_SESSION[$this->session_uid]) && $usr_id == $_SESSION[$this->session_uid]) {
			global $TUPA_CONF_VARS;
			// Get the user preferences and override $TUPA_CONF_VARS array
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('preferences', 'users', 'id='. lib_DB::fullQuoteStr($_SESSION[$this->session_uid]), '', '', '1');
			$row = mysql_fetch_assoc($res);
			$prefArr = unserialize($row['preferences']);
			if (is_array($prefArr)) {
				$TUPA_CONF_VARS = lib_div::array_merge_recursive_overrule($TUPA_CONF_VARS, $prefArr, '1');
			}
		}
	}


	/**
	 * Gets the group preferences from group record and override the default values. Group Permissions are also overwritten by user preferences.
	 *
	  * @param	integer		Id of updated group
	 */
	function loadGroupPreferences($grp_id) {
		if (isset($_SESSION[$this->session_uid]) && $grp_id == $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION[$this->session_uid])) {
			global $TUPA_CONF_VARS;
			// Get the user preferences and override $TUPA_CONF_VARS array
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('preferences', 'groups', 'id='. lib_DB::fullQuoteStr($grp_id), '', '', '1');
			$row = mysql_fetch_assoc($res);
			$prefArr = unserialize($row['preferences']);
			if (is_array($prefArr)) {
				$TUPA_CONF_VARS = lib_div::array_merge_recursive_overrule($TUPA_CONF_VARS, $prefArr, '1');
			}

			// Load user preferences
			$this->loadUserPreferences($_SESSION[$this->session_uid]);
		}
	}



	/**
	 * Checks if the user has one of the submitted permissions.
	 *
	 * @param	string		list of allowed
	 * @return	boolean
	 */
	function getPerm($allowedList) {
		$allowedArr = lib_div::trimExplode(',', $allowedList, '1');
		foreach ($allowedArr as $value) {
			$pos = lib_div::trimExplode('=>', $value);
			$perms = $this->perm;
			if (isset($perms[$pos[0]]) && isset($perms[$pos[0]][$pos[1]])) {
				if ($perms[$pos[0]][$pos[1]] == '1') return true;
			}
		}
		return false;
	}



	/**
	 * Checks if the logged in user is the owner of the current record.
	 *
	 * @param	integer		ID of record
	 * @param	string		table of record
	 * @param	string		table of record
	 * @param	boolean	check if group of users matchs
	 * @return	boolean
	 */
	function isOwnerOfRecord($recordId, $table, $checkGroup=false) {
		if ($this->getPerm('ADM=>admin') || $this->hasPerm($table .'_admin')) return true;

		// Current id of logged in user
		$currentUserId = $_SESSION['uid'];

		// Get owner id of record
		// if $table is users we already have it
		if ($table == 'users') {
			$recordOwnerId = $recordId;
		} else {
			$sqlWhere = 'id = '. lib_DB::fullQuoteStr($recordId);
			if ($table == 'domains') {
				$sqlSelect = 'domain_owners.usr_id';
				$sqlFrom = $table .', domain_owners';
				$sqlWhere .= ' AND domains.id = domain_owners.dom_id';
			} else {
				$sqlSelect = 'usr_id';
				$sqlFrom = $table;
			}
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery($sqlSelect, $sqlFrom, $sqlWhere, '', '', '1');
			$recordOwnerId = mysql_result($res, 0);
		}

		// Check if they match when $checkGroup is not set
		if ($recordOwnerId == $currentUserId) {
			return true;
		// If $checkGroup is set, get the groups of the users an compare them
		} elseif ($checkGroup) {
			$recordOwnerGroupId = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($recordOwnerId);
			$currentUserGroupId = $GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($currentUserId);

			if ($recordOwnerGroupId == $currentUserGroupId) return true;
		}
		return false;
	}



	/**
	 * Checks if the user is allowed to insert a record with given owner ID in this part and returns the owner ID if true or his own ID if false.
	 *
	 * @param	string		Part (domains or templates)
	 * @param	integer		Owner ID to insert for
	 * @return	integer		ID to insert
	 */
	function checkOwnerIdOfRecord($part, $ownerId) {
		global $TBE_TEMPLATE, $USER, $LANG;
		if ($ownerId != $_SESSION['uid']) {
			if ($USER->hasPerm($part .'_admin')) {
				$usr_id = $ownerId;
			} else {
				$userIds = $GLOBALS['TUPA_DB']->exec_SELECTgetUserIdsOfGroup($GLOBALS['TUPA_DB']->exec_SELECTgetGroupIdOfUser($_SESSION['uid']));
				if (lib_div::inList($ownerId, $userIds)) {
					$usr_id = $ownerId;
				} else {
					$ownerName = $GLOBALS['TUPA_DB']->exec_SELECTgetUserName($ownerId);
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('add'. lib_div::firstUpper($part, true) .'OwnerError'));
					lib_logging::addLogMessage($part, 'add', 'permission', 'logMsg'. lib_div::firstUpper($part, true) .'OwnerError', array('userName'=>$ownerName));
					$usr_id = $_SESSION['uid'];
				}
			}
		} else {
			$usr_id = $_SESSION['uid'];
		}
		return $usr_id;
	}



	/**
	 * Holds all permissions in central place.
	 *
	 * @param	string		list of parts to check
	 * @return	boolean
	 */
	function hasPerm($partList) {
		// Admin rights? return true.
		if ($this->getPerm('ADM=>admin')) return true;

		$partArr = lib_div::trimExplode(',', $partList, '1');
		foreach ($partArr as $part) {
			switch ($part) {
				## MENUES ##
				###########
				case 'menu_domains':
					if ($this->getPerm('ADM=>domain, DOM=>show, DOMGROUP=>show')) return true;
					break;

				case 'menu_templates':
					if ($this->getPerm('ADM=>domain, TMPL=>show, TMPLGROUP=>show')) return true;
					break;

				case 'menu_groups':
					if ($this->getPerm('ADM=>user')) return true;
					break;

				case 'menu_users':
					if ($this->getPerm('ADM=>user, USR=>show')) return true;
					break;

				## DOMAINS ##
				############
				case 'domains_admin':
					if ($this->getPerm('ADM=>domain')) return true;
					break;

				case 'domains_show':
					if ($this->getPerm('ADM=>domain, DOM=>show')) return true;
					break;

				case 'domains_show_group':
					if ($this->getPerm('ADM=>domain, DOMGROUP=>show')) return true;
					break;

				case 'domains_add':
					if ($this->getPerm('ADM=>domain, DOM=>add')) return true;
					break;

				case 'domains_add_group':
					if ($this->getPerm('ADM=>domain, DOMGROUP=>add')) return true;
					break;

				case 'domains_add_with_template':
					if ($this->getPerm('ADM=>domain, TMPL=>show, TMPLGROUP=>show')) return true;
					break;

				case 'domains_edit':
					if ($this->getPerm('ADM=>domain, DOM=>edit')) return true;
					break;

				case 'domains_edit_group':
					if ($this->getPerm('ADM=>domain, DOMGROUP=>edit')) return true;
					break;

				case 'domains_delete':
					if ($this->getPerm('ADM=>domain, DOM=>delete')) return true;
					break;

				case 'domains_delete_group':
					if ($this->getPerm('ADM=>domain, DOMGROUP=>delete')) return true;
					break;


				## TEMPLATES ##
				##############
				case 'templates_admin':
					if ($this->getPerm('ADM=>domain')) return true;
					break;

				case 'templates_show_group':
					if ($this->getPerm('ADM=>domain, TMPLGROUP=>show')) return true;
					break;

				case 'templates_show':
					if ($this->getPerm('ADM=>domain, TMPL=>show')) return true;
					break;

				case 'templates_add':
					if ($this->getPerm('TMPL=>add, TMPLGROUP=>add, ADM=>domain')) return true;
					break;

				case 'templates_edit':
					if ($this->getPerm('ADM=>domain, TMPL=>edit')) return true;
					break;

				case 'templates_edit_group':
					if ($this->getPerm('ADM=>domain, TMPLGROUP=>edit')) return true;
					break;

				case 'templates_delete':
					if ($this->getPerm('ADM=>domain, TMPL=>delete')) return true;
					break;

				case 'templates_delete_group':
					if ($this->getPerm('ADM=>domain, TMPLGROUP=>delete')) return true;
					break;

				## USERS ##
				##########
				case 'users_admin':
					if ($this->getPerm('ADM=>user')) return true;
					break;

				case 'users_show_group':
					if ($this->getPerm('ADM=>user, USR=>show')) return true;
					break;

				case 'users_add':
					if ($this->getPerm('ADM=>user, USR=>add')) return true;
					break;

				case 'users_edit':
				case 'users_edit_group':
					if ($this->getPerm('ADM=>user,USR=>edit')) return true;
					break;

				case 'users_delete':
				case 'users_delete_group':
					if ($this->getPerm('ADM=>user, USR=>delete')) return true;
					break;

				## GROUPS ##
				###########
				case 'groups_admin':
				case 'groups_show':
				case 'groups_add':
				case 'groups_edit':
				case 'groups_delete':
					if ($this->getPerm('ADM=>user')) return true;
					break;

				## LOGGING ##
				############
				case 'logging_show': // Only admin users for the moment
					break;
			}
		}
		return false;
	}
}

?>