<?php
/**
 * Handles all cronjobs
 *
 * @package 	TUPA
 * @author 	Urs Weiss <urs@tupa-dns.org>
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

### Create a crontab entry like this:
### */2 * * * * root php /path/to/your/tupa/directory/crontab/cron_120s.php > /dev/null

if ($_SERVER['REMOTE_ADDR']) {
	die('It\'s not allowed to call this script from a remote host!');
}

require('../init.php');


## Base config
#########################

$demoData = $TUPA_CONF_VARS['CRON']['insertRrdDemoData'];
$configName = $TUPA_CONF_VARS['CRON']['pdnsConfigName'];

$tupaStatsDb = PATH_site .'stats/db/';



## Update PDNS statistic (RRD)
#########################

// Check db directory
if (is_dir($tupaStatsDb) && is_writable($tupaStatsDb) && lib_div::checkProgramInPath('rrdtool') && ($demoData || lib_div::checkProgramInPath('pdns_control'))) {
	$includeScript = PATH_site .'crontab/cron_rrd.php';
	if (file_exists($includeScript)) {
		require_once($includeScript);
	}
}



## Check backup execution
#########################

// Get backup configuration and check time to execute
require_once(PATH_lib .'class.backup.php');
$BACKUP = lib_div::makeInstance('backup');
$BACKUP->cronExec = true;
$BACKUP->returnMessage = 0;	// to return no error if no config found

// Check if there is a config and if crontab backup is enabled
if ($BACKUP->getBackupconf() && $BACKUP->save && $BACKUP->time['frequency']) {
	$BACKUP->returnMessage = 2;	// adds error messages to array ($TBE_TEMPLATE->message)
	
	if (time() >= $BACKUP->nextExec) {
		if (!$nextExec = lib_div::calcNextBackupExec($BACKUP->time['frequency'], $BACKUP->time['btime'], $BACKUP->time['weekday'], $BACKUP->time['day'])) {
			// Error calculate
			lib_logging::addLogMessage('backup', 'execute', 'error', 'logMsgBackupCalcNextExecError');
			$BACKUP->printMessage('error', '', $LANG->getLang('backupCalcNextExecError'));
		} else {
			// Update next_exec fild in database
			$updateFields = array(
				'next_exec' => $nextExec
			);
			$GLOBALS['TUPA_DB']->exec_UPDATEquery('backup_config', '1=1', $updateFields);
			
			// Execute backup
			$BACKUP->runBackup();
		}
		
		// Any errors? Send notofication.
		if (count($TBE_TEMPLATE->message) > 0 && $TBE_TEMPLATE->message[0]['type'] == 'error') {
			$messages = '';
			if (lib_div::validEmail($BACKUP->email)) {
				foreach ($TBE_TEMPLATE->message as $value) {
					$messages .= $value['type'] .":\n". $value['content'] ."\n\n";
				}
				
				// Get servers hostname
				exec('hostname', $output);
				$hostname = $output[0];
				
				lib_div::sendMail($BACKUP->email, $LANG->getLang('backupCronExecErrorSubject', array('hostname' => $hostname)), $messages);
			}
		}
	}
	
}


?>