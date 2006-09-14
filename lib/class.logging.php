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
 * Everything needed to add logging messages
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

class lib_logging {
	
	/**
	 * Inserts a logging message into database.
	 *
	 * @param	string		part (domain, template, user, group, ...)
	 * @param 	string		action (add, edit, delete, login)
	 * @param 	string		type (info, message, error, debug, security, ...)
	 * @param 	string		message translation key
	 * @param 	array		message replacements (array('whatever' => 'replace with this'))
	 * @return 	void
	 */
	function addLogMessage($part, $action, $type, $msg, $msgReplArr='') {
		// serialize message replacements
		$msgReplArr && is_array($msgReplArr) ? $msgRepl = serialize($msgReplArr) : $msgRepl = '';
		
		// Check if $_SESSION['uid'] exists (only used for login page)
		$uid = isset($_SESSION['uid']) ? $_SESSION['uid'] : '0';
		
		$fieldValues = array();
		$fieldValues['tstamp'] = time();
		$fieldValues['usr_id'] = $uid;
		$fieldValues['ip'] = $_SERVER['REMOTE_ADDR'];
		$fieldValues['part'] = $part;
		$fieldValues['action'] = $action;
		$fieldValues['type'] = $type;
		$fieldValues['message'] = $msg;
		$fieldValues['message_repl'] = $msgRepl;
		
		$GLOBALS['TUPA_DB']->exec_INSERTquery('logging', $fieldValues);
	}
	
	
	/**
	 * Inserts a logging message into database.
	 *
	 * @return 	void
	 */
	function logMaintenance() {
		global $TUPA_CONF_VARS;
		$dbLastKeep = '';
		$dbLastKeep1 = '';
		$dbLastKeep2 = '';
		$logMaintenanceDays = $TUPA_CONF_VARS['SYS']['logMaintenanceDays'];
		$logMaintenanceRecords = $TUPA_CONF_VARS['SYS']['logMaintenanceRecords'];
		
		// Delete records over records limit
		if ($logMaintenanceRecords > 0) {
			$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('tstamp', 'logging', '', '', 'tstamp DESC', $logMaintenanceRecords .',1');
			if (mysql_num_rows($res) > 0) {
				$dbLastKeep1 = mysql_result($res, 0, 'tstamp');
			}
		}
		
		// Delete records older than configured days
		if ($logMaintenanceDays > 0) {
			$dbLastKeep2 = time() - $logMaintenanceDays * 86400;
		}
		
		if ($dbLastKeep1 || $dbLastKeep2) {
			$dbLastKeep = max($dbLastKeep1, $dbLastKeep2);
			$GLOBALS['TUPA_DB']->exec_DELETEquery('logging', 'tstamp<='. lib_DB::fullQuoteStr($dbLastKeep));
		}
	}
	
}