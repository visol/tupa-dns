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
 * Backup functions
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

// Check if the $USER-Object exists
if (!is_object($USER)) die('It\'s not allowed to access this script direcltly!');

// Check login
$USER->loggedin();

class tupa_backup {

	/**
	 * Generates form to configure database and backup/restore them.
	 *
	 * @return	string		content
	 */
	function showBackup() {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		if (!$USER->hasPerm('admin')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}
		$markerArray = lib_div::setDefaultMarkerArray();

		$content = $TBE_TEMPLATE->header($LANG->getLang('backupTitle'));

		$content .= $TBE_TEMPLATE->genMessageField();

		// Initalizize backup class and get config
		require_once(PATH_lib .'class.backup.php');
		$BACKUP = lib_div::makeInstance('backup');
		$BACKUP->returnMessage = 2;
		$BACKUP->getBackupconf();

		$compressionSelectOptions = $TBE_TEMPLATE->compressionSelectOptions($BACKUP->compression);
		$savebackupSelectOptions = $TBE_TEMPLATE->savebackupSelectOptions($BACKUP->save);
		$protocolSelectOptions = $TBE_TEMPLATE->protocolSelectOptions($BACKUP->protocol);
		$currentLocalSize = lib_div::formatSize($BACKUP->maintenance['local']['size'], 0, true);
		$localSizeSelectOptions = $TBE_TEMPLATE->sizeSelectOptions($currentLocalSize[1]);
		$currentRemoteSize = lib_div::formatSize($BACKUP->maintenance['remote']['size'], 0, true);
		$remoteSizeSelectOptions = $TBE_TEMPLATE->sizeSelectOptions($currentRemoteSize[1]);
		$restoreLocalSelectOptions = $TBE_TEMPLATE->restoreSelectOptions($BACKUP, 'local');
		$restoreRemoteSelectOptions = $TBE_TEMPLATE->restoreSelectOptions($BACKUP, 'remote');
		$frequencySelectOptions = $TBE_TEMPLATE->frequencySelectOptions($BACKUP->time['frequency']);
		$weekdaysSelectOptions = $TBE_TEMPLATE->weekdaysSelectOptions($BACKUP->time['weekday']);
		$daysSelectOptions = $TBE_TEMPLATE->daysSelectOptions($BACKUP->time['day']);

		// Get template
		$templateFileContent = $TBE_TEMPLATE->fileContent(lib_div::getSkinFilePath(PATH_templates .'backup.html'));
		// Get template subpart
		$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###BACKUP_SHOW###');
		$subpartBackup = $TBE_TEMPLATE->getSubpart($subpart, '###BACKUP_SHOW_BACKUP###');
		$subpartRestore = $TBE_TEMPLATE->getSubpart($subpart, '###BACKUP_SHOW_RESTORE###');
		$subpartConfig = $TBE_TEMPLATE->getSubpart($subpart, '###BACKUP_SHOW_CONFIG###');

		// Backup
		$markerArray['backup_backup_subtitle'] = $LANG->getLang('backupBackupSubtitle');
		$markerArray['label_use_config'] = $LANG->getLang('labelUseBackupConfig');
		$markerArray['input_use_config'] = '<input type="checkbox" name="useConfig" value="1" onchange="toggleFields(this, \'compression|0|0,dbcreate|0|0,completeinsert|0|0,extendedinsert|0|0\',\'compression|1|0,dbcreate|1|0,completeinsert|1|0,extendedinsert|1|0\', \'backup\');" />'. $LANG->getHelp('helpBackupUseConfig');
		$markerArray['label_compression'] = $LANG->getLang('labelCompression');
		$markerArray['select_compression'] = '<select name="compression" class="'. STYLE_FIELD .'">'. $TBE_TEMPLATE->compressionSelectOptions() .'</select>'. $LANG->getHelp('helpBackupCompression');

		$markerArray['backup_dump_options_subtitle'] = $LANG->getLang('backupDumpOptionsSubtitle');
		$markerArray['label_dump_option_dbcreate'] = $LANG->getLang('labelDumpOptionDbcreate');
		$markerArray['input_dump_option_dbcreate'] = '<input type="checkbox" name="dbcreate" value="1" />'. $LANG->getHelp('helpBackupDumpOptionDbcreate');
		$markerArray['label_dump_option_completeinsert'] = $LANG->getLang('labelDumpOptionCompleteinsert');
		$markerArray['input_dump_option_completeinsert'] = '<input type="checkbox" name="completeinsert" value="1" />'. $LANG->getHelp('helpBackupDumpOptionCompleteinsert');
		$markerArray['label_dump_option_extendedinsert'] = $LANG->getLang('labelDumpOptionExtendedinsert');
		$markerArray['input_dump_option_extendedinsert'] = '<input type="checkbox" name="extendedinsert" value="1" />'. $LANG->getHelp('helpBackupDumpOptionExtendedinsert');

		$hiddenFields = '<input type="hidden" name="cmd" value="backup" />';

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('backupButtonBackup') .'" class="'. STYLE_BUTTON .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		$subpartBackup = $TBE_TEMPLATE->wrapInFormTags($TBE_TEMPLATE->substituteMarkerArray($subpartBackup, $markerArray, '###|###', '1'), 'processBackup(document.backup);', 'backup');

		// Restore
		$markerArray['restore_warning'] = $LANG->getLang('backupRestoreWarning');
		$markerArray['backup_restore_local_subtitle'] = $LANG->getLang('backupRestoreLocalSubtitle');
		$markerArray['label_restore_local'] = $LANG->getLang('labelRestoreLocal');
		$markerArray['select_restore_local'] = '<select name="local" class="'. STYLE_FIELD .'">'. $restoreLocalSelectOptions .'</select>'. $LANG->getHelp('helpBackupRestoreLocal');
		$markerArray['backup_restore_remote_subtitle'] = $LANG->getLang('backupRestoreRemoteSubtitle');
		$markerArray['label_restore_remote'] = $LANG->getLang('labelRestoreRemote');
		$markerArray['select_restore_remote'] = '<select name="remote" class="'. STYLE_FIELD .'">'. $restoreRemoteSelectOptions .'</select>'. $LANG->getHelp('helpBackupRestoreRemote');
		$markerArray['backup_restore_file_subtitle'] = $LANG->getLang('backupRestoreFileSubtitle');
		$markerArray['label_restore_file'] = $LANG->getLang('labelRestoreFile', array('maxSize'=>lib_div::formatSize(lib_div::convertMaxUploadFilesize())));
		// Include IFRAME to upload file
		$markerArray['input_restore_file'] = $TBE_TEMPLATE->addFileUploadField('restoreFile', 'restore', $LANG->getHelp('helpBackupRestoreFile'));

		$hiddenFields = '<input type="hidden" name="cmd" value="restore" />';
		$hiddenFields .= '<input type="hidden" name="restoreval" alt="eitheror|,|local,remote,restoreFile" emsg="'. $LANG->getLang('backupRestoreEitherorError') .'" />';

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('backupButtonRestore') .'" class="'. STYLE_BUTTON .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		$subpartRestore = $TBE_TEMPLATE->wrapInFormTags($TBE_TEMPLATE->substituteMarkerArray($subpartRestore, $markerArray, '###|###', '1'), 'processBackup(document.restore);', 'restore');

		// Config
		$markerArray['backup_config_subtitle'] = $LANG->getLang('backupConfigSubtitle');
		$markerArray['label_notification_email'] = $LANG->getLang('labelNotificationEmail');
		$markerArray['input_notification_email'] = '<input type="text" name="email" class="'. STYLE_FIELD .'" size="30" alt="email|1|bok" emsg="'. $LANG->getLang('emailError') .'" value="'. $BACKUP->email .'" />'. $LANG->getHelp('helpBackupEmail');
		$markerArray['label_compression'] = $LANG->getLang('labelCompression');
		$markerArray['select_compression'] = '<select name="compression" class="'. STYLE_FIELD .'">'. $compressionSelectOptions .'</select>'. $LANG->getHelp('helpBackupCompression');
		$markerArray['label_save_backup'] = $LANG->getLang('labelSavePlace');
		$markerArray['select_save_backup'] = '<select name="save" class="'. STYLE_FIELD .'">'. $savebackupSelectOptions .'</select>'. $LANG->getHelp('helpBackupSaveBackup');
		$markerArray['label_backup_schedule'] = $LANG->getLang('labelBackupSchedule');
		$markerArray['select_backup_schedule'] = '<select name="frequency" onchange="if (this.value == \'\') { toggleFields(this, \'btime|0,weekday|0,day|0\', \'\', \'backup_config\'); } else if (this.value == 1) { toggleFields(this, \'btime|1,weekday|0,day|0\', \'\', \'backup_config\'); } else if (this.value == 2) { toggleFields(this, \'btime|1,weekday|1,day|0\', \'\', \'backup_config\'); } else if (this.value == 3) { toggleFields(this, \'btime|1,weekday|0,day|1\', \'\', \'backup_config\'); }" '. ($BACKUP->time['frequency'] == '' ? 'disabled="disabled"' : '') .' class="'. STYLE_FIELD .'">'. $frequencySelectOptions .'</select>
								<input type="text" name="btime" class="'. STYLE_FIELD .'" size="5" alt="custom|bok" pattern="'. $TUPA_CONF_VARS['REGEX']['time24'] .'" emsg="'. $LANG->getLang('timeError') .'" value="'. $BACKUP->time['btime'] .'" '. ($BACKUP->time['frequency'] == '' ? 'disabled="disabled"' : '') .' />
								<select name="weekday" class="'. STYLE_FIELD .'" '. ($BACKUP->time['frequency'] != 2 ? 'disabled="disabled"' : '') .'>'. $weekdaysSelectOptions .'</select>
								<select name="day" class="'. STYLE_FIELD .'" '. ($BACKUP->time['frequency'] != 3 ? 'disabled="disabled"' : '') .'>'. $daysSelectOptions .'</select>
								'. $LANG->getHelp('helpBackupSchedule');

		$markerArray['backup_dump_options_subtitle'] = $LANG->getLang('backupDumpOptionsSubtitle');
		$markerArray['label_dump_option_dbcreate'] = $LANG->getLang('labelDumpOptionDbcreate');
		$markerArray['input_dump_option_dbcreate'] = '<input type="checkbox" name="dbcreate" value="1" '. ($BACKUP->dumpOptions['dbcreate'] ? 'checked' : '') .' />'. $LANG->getHelp('helpBackupDumpOptionDbcreate');
		$markerArray['label_dump_option_completeinsert'] = $LANG->getLang('labelDumpOptionCompleteinsert');
		$markerArray['input_dump_option_completeinsert'] = '<input type="checkbox" name="completeinsert" value="1" '. ($BACKUP->dumpOptions['complete'] ? 'checked' : '') .' />'. $LANG->getHelp('helpBackupDumpOptionCompleteinsert');
		$markerArray['label_dump_option_extendedinsert'] = $LANG->getLang('labelDumpOptionExtendedinsert');
		$markerArray['input_dump_option_extendedinsert'] = '<input type="checkbox" name="extendedinsert" value="1" '. ($BACKUP->dumpOptions['extended'] ? 'checked' : '') .' />'. $LANG->getHelp('helpBackupDumpOptionExtendedinsert');

		$markerArray['backup_config_local_subtitle'] = $LANG->getLang('backupConfigLocalSubtitle');
		$markerArray['label_local_path'] = $LANG->getLang('labelLocalPath');
		$markerArray['input_local_path'] = '<input type="text" name="path_local" class="'. STYLE_FIELD .'" size="50" value="'. $BACKUP->pathLocal .'" />'. $LANG->getHelp('helpBackupPathLocal');
		$markerArray['label_local_amount'] = $LANG->getLang('labelBackupLocalAmount');
		$markerArray['input_local_amount'] = '<input type="checkbox" name="local_amount_chk" value="1" onchange="toggleFields(this, \'local_amount|1\', \'local_amount|0|\', \'backup_config\');" '. ($BACKUP->maintenance['local']['amount'] > 0 ? 'checked' : '') .' /><input type="text" name="local_amount" class="'. STYLE_FIELD .'" size="5" alt="numeric|bok" emsg="'. $LANG->getLang('backupLocalAmountError') .'" '. ($BACKUP->maintenance['local']['amount'] > 0 ? 'value="'. $BACKUP->maintenance['local']['amount'] .'"' : 'disabled="disabled"') .' />'. $LANG->getHelp('helpBackupLocalAmount');
		$markerArray['label_local_days'] = $LANG->getLang('labelBackupLocalDays');
		$markerArray['input_local_days'] = '<input type="checkbox" name="local_days_chk" value="1" onchange="toggleFields(this, \'local_days|1\', \'local_days|0|\', \'backup_config\');" '. ($BACKUP->maintenance['local']['days'] > 0 ? 'checked' : '') .' /><input type="text" name="local_days" class="'. STYLE_FIELD .'" size="5" alt="numeric|bok" emsg="'. $LANG->getLang('backupLocalDaysError') .'" '. ($BACKUP->maintenance['local']['days'] > 0 ? 'value="'. $BACKUP->maintenance['local']['days'] .'"' : 'disabled="disabled"') .' />'. $LANG->getHelp('helpBackupLocalDays');
		$markerArray['label_local_size'] = $LANG->getLang('labelBackupLocalSize');
		$markerArray['input_local_size'] = '<input type="checkbox" name="local_size_chk" value="1" onchange="toggleFields(this, \'local_size|1,local_size_sel|1\', \'local_size|0|,local_size_sel|0\', \'backup_config\');" '. ($currentLocalSize[0] > 0 ? 'checked' : '') .' /><input type="text" name="local_size" class="'. STYLE_FIELD .'" size="5" alt="numeric|bok" emsg="'. $LANG->getLang('backupLocalSizeError') .'" '. ($currentLocalSize[0] > 0 ? 'value="'. $currentLocalSize[0] .'"' : 'disabled="disabled"') .' /><select name="local_size_sel" class="'. STYLE_FIELD .'" '. ($currentLocalSize[0] > 0 ? '' : 'disabled="disabled"') .'>'. $localSizeSelectOptions .'</select>'. $LANG->getHelp('helpBackupLocalSize');

		$markerArray['backup_config_remote_subtitle'] = $LANG->getLang('backupConfigRemoteSubtitle');
		$markerArray['label_protocol'] = $LANG->getLang('labelProtocol');
		$markerArray['select_protocol'] = '<select name="protocol" class="'. STYLE_FIELD .'">'. $protocolSelectOptions .'</select>'. $LANG->getHelp('helpBackupProtocol');
		$markerArray['label_passive'] = $LANG->getLang('labelPassiveMode');
		$markerArray['input_passive'] = '<input type="checkbox" name="passive" value="1" '. ($BACKUP->passive ? 'checked' : '') .' />'. $LANG->getHelp('helpBackupPassiveMode');
		$markerArray['label_ssh_fingerprint'] = $LANG->getLang('labelSshFingerprint');
		$markerArray['input_ssh_fingerprint'] = '<input type="text" name="ssh_fingerprint" class="'. STYLE_FIELD .'" size="50" alt="length|47|47|bok" emsg="'. $LANG->getLang('rawSshFingerprintError') .'" value="'. $BACKUP->rawSshFingerprint .'" />'. $LANG->getHelp('helpBackupSshFingerprint');
		$markerArray['label_hostport'] = $LANG->getLang('labelHostPort');
		$markerArray['input_host'] = '<input type="text" name="host" class="'. STYLE_FIELD .'" size="30" alt="custom|bok" pattern="('. $TUPA_CONF_VARS['REGEX']['domain'] .')|('. $TUPA_CONF_VARS['REGEX']['IPv4'] .')|('. $TUPA_CONF_VARS['REGEX']['IPv6'] .')" emsg="'. $LANG->getLang('hostIpError') .'" value="'. $BACKUP->host .'" />';
		$markerArray['input_port'] = '<input type="text" name="port" class="'. STYLE_FIELD .'" size="5" alt="number|1|65535|bok" emsg="'. $LANG->getLang('portError') .'" value="'. ($BACKUP->port > 0 ? $BACKUP->port : '') .'" />'. $LANG->getHelp('helpBackupHostPort');
		$markerArray['label_username'] = $LANG->getLang('labelUsername');
		$markerArray['input_username'] = '<input type="text" name="username" class="'. STYLE_FIELD .'" size="20" value="'. $BACKUP->username .'" />'. $LANG->getHelp('helpBackupUsername');
		$markerArray['label_password'] = $LANG->getLang('labelPassword');
		$markerArray['input_password'] = '<input type="password" name="password" class="'. STYLE_FIELD .'" size="20" value="'. $BACKUP->password .'" />'. $LANG->getHelp('helpBackupPassword');
		$markerArray['label_remote_path'] = $LANG->getLang('labelRemotePath');
		$markerArray['input_remote_path'] = '<input type="text" name="path_remote" class="'. STYLE_FIELD .'" size="50" value="'. $BACKUP->pathRemote .'" />'. $LANG->getHelp('helpBackupPathRemote');
		$markerArray['label_remote_amount'] = $LANG->getLang('labelBackupRemoteAmount');
		$markerArray['input_remote_amount'] = '<input type="checkbox" name="remote_amount_chk" value="1" onchange="toggleFields(this, \'remote_amount|1\', \'remote_amount|0|\', \'backup_config\');" '. ($BACKUP->maintenance['remote']['amount'] > 0 ? 'checked' : '') .' /><input type="text" name="remote_amount" class="'. STYLE_FIELD .'" size="5" alt="numeric|bok" emsg="'. $LANG->getLang('backupRemoteAmountError') .'" '. ($BACKUP->maintenance['remote']['amount'] > 0 ? 'value="'. $BACKUP->maintenance['remote']['amount'] .'"' : 'disabled="disabled"') .' />'. $LANG->getHelp('helpBackupRemoteAmount');
		$markerArray['label_remote_days'] = $LANG->getLang('labelBackupLocalDays');
		$markerArray['input_remote_days'] = '<input type="checkbox" name="remote_days_chk" value="1" onchange="toggleFields(this, \'remote_days|1\', \'remote_days|0|\', \'backup_config\');" '. ($BACKUP->maintenance['remote']['days'] > 0 ? 'checked' : '') .' /><input type="text" name="remote_days" class="'. STYLE_FIELD .'" size="5" alt="numeric|bok" emsg="'. $LANG->getLang('backupRemoteDaysError') .'" '. ($BACKUP->maintenance['remote']['days'] > 0 ? 'value="'. $BACKUP->maintenance['remote']['days'] .'"' : 'disabled="disabled"') .' />'. $LANG->getHelp('helpBackupRemoteDays');
		$markerArray['label_remote_size'] = $LANG->getLang('labelBackupLocalSize');
		$markerArray['input_remote_size'] = '<input type="checkbox" name="remote_size_chk" value="1" onchange="toggleFields(this, \'remote_size|1,remote_size_sel|1\', \'remote_size|0|,remote_size_sel|0\', \'backup_config\');" '. ($currentRemoteSize[0] > 0 ? 'checked' : '') .' /><input type="text" name="remote_size" class="'. STYLE_FIELD .'" size="5" alt="numeric|bok" emsg="'. $LANG->getLang('backupRemoteSizeError') .'" '. ($currentRemoteSize[0] > 0 ? 'value="'. $currentRemoteSize[0] .'"' : 'disabled="disabled"') .' /><select name="remote_size_sel" class="'. STYLE_FIELD .'" '. ($currentRemoteSize[0] > 0 ? '' : 'disabled="disabled"') .'>'. $remoteSizeSelectOptions .'</select>'. $LANG->getHelp('helpBackupRemoteSize');

		$hiddenFields = '<input type="hidden" name="cmd" value="save_config" />';

		$markerArray['submit_button'] = '<input type="submit" name="Submit" value="'. $LANG->getLang('backupButtonSave') .'" class="'. STYLE_BUTTON .'" />';
		$markerArray['hidden_fields'] = $hiddenFields;

		// Substitute markers
		$subpartConfig = $TBE_TEMPLATE->wrapInFormTags($TBE_TEMPLATE->substituteMarkerArray($subpartConfig, $markerArray, '###|###', '1'), 'processBackup(document.backup_config);', 'backup_config');

		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###BACKUP_SHOW_BACKUP###', $subpartBackup);
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###BACKUP_SHOW_RESTORE###', $subpartRestore);
		$subpart = $TBE_TEMPLATE->substituteSubpart($subpart, '###BACKUP_SHOW_CONFIG###', $subpartConfig);

		$content .= $subpart;
		return  $content;
	}


	/**
	 * Process the submitted backup data.
	 *
	 * @return	string		messages
	 */
	function processBackup($conf) {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		if (!$USER->hasPerm('admin')) {
			return  $TBE_TEMPLATE->noPermissionMessage();
		}

		$errorArr = array();
		$fd = $conf['formdata'];
		$dataArr = $fd['data'];

		// Clean the data a bit (Remove HTML/PHP tags, ass slashes)
		lib_div::stripTagsOnArray($fd);
		lib_div::addSlashesOnArray($fd);

		//$TBE_TEMPLATE->addMessage('debug', nl2br(print_r($fd, true)));

		switch ($fd['hidden']['cmd']) {
			case 'backup':
//				$mysqldump = '';

				if ($dataArr['useConfig']) {
					$getVars = '?useConfig=1';
				} else {
					unset($dataArr['useConfig']);
					// Generate get vars
					$getVars = '?';
					foreach ($dataArr as $key => $value) {
						$getVars .= '&'. htmlentities($key) .'='. htmlentities($value);
					}
				}
				// JS to open window
				$TBE_TEMPLATE->addMessage('', '', 'window.open("backup/dump.php'. $getVars .'", "mysqldump", "dependent=yes,height=120,width=270,location=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no");');
				break;
			case 'restore':
				// Check if only one field has a value
				$checkFields = array('local', 'remote', 'restoreFile');
				$fieldsSet = 0;
				foreach ($checkFields as $value) {
					//if ($value == 'file') {
					//	$dataArr[$value] = $fd['hidden'][$value];
					//}
					if (strlen($dataArr[$value]) > 0) $fieldsSet++;
				}
				if ($fieldsSet > 1 || $fieldsSet == 0) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('backupRestoreEitherorError'));
					break;
				}

				// Initalizize backup class
				require_once(PATH_lib .'class.backup.php');
				$BACKUP = lib_div::makeInstance('backup');
				$BACKUP->returnMessage = 2;

				foreach ($checkFields as $value) {
					if ($dataArr[$value]) {
						$BACKUP->restoreBackup($value, $dataArr[$value]);
					}
				}
				break;
			case 'save_config':
				// Validate the data dependig of the save value (local or remote backup)
				$regexDomain = '/'. $TUPA_CONF_VARS['REGEX']['domain'] .'/';
				$regexIPv4 = '/'. $TUPA_CONF_VARS['REGEX']['IPv4'] .'/';
				$regexIPv6 = '/'. $TUPA_CONF_VARS['REGEX']['IPv6'] .'/';
				$regexTime24 = '/'. $TUPA_CONF_VARS['REGEX']['time24'] .'/';

				if ($dataArr['save']) {
					if (!is_numeric($dataArr['save'])) $errorArr[] = $LANG->getLang('backupSaveError');
					switch ($dataArr['save']) {
						case 1:		// Local
							if ($dataArr['path_local'] == '') $errorArr[] = $LANG->getLang('backupPathLocalError');
							if ($dataArr['host'] != '' && !(preg_match($regexDomain, $dataArr['host']) || preg_match($regexIPv4, $dataArr['host']) || preg_match($regexIPv6, $dataArr['host']))) $errorArr[] = $LANG->getLang('hostIpError');
							break;
						case 2:		// Remote
							if ($dataArr['path_local'] == '') $errorArr[] = $LANG->getLang('backupPathLocalError');
							if ($dataArr['host'] == '' || !(preg_match($regexDomain, $dataArr['host']) || preg_match($regexIPv4, $dataArr['host']) || preg_match($regexIPv6, $dataArr['host']))) $errorArr[] = $LANG->getLang('hostIpError');
							if ($dataArr['username'] == '') $errorArr[] = $LANG->getLang('usernameError');
							if ($dataArr['path_remote'] == '') $errorArr[] = $LANG->getLang('backupPathRemoteError');
							break;
					}

					// Check backup settings
					if ($dataArr['frequency']) {
						if (!is_numeric($dataArr['frequency'])) $errorArr[] = $LANG->getLang('backupFrequencyError');
						if ($dataArr['btime'] == '' || !preg_match($regexTime24, $dataArr['btime'])) $errorArr[] = $LANG->getLang('backupTimeError');

						switch ($dataArr['frequency']) {
							case 2:		// Weekly
								if (!is_numeric($dataArr['weekday']) || $dataArr['weekday'] < 0 || $dataArr['weekday'] > 7)  $errorArr[] = $LANG->getLang('backupWeekdayError');
								break;
							case 3:		// Monthly
								if (!is_numeric($dataArr['day']) || $dataArr['weekday'] < 1 || $dataArr['weekday'] > 31)  $errorArr[] = $LANG->getLang('backupDayError');
								break;
						}
					}
				}
				// Other checks which have to mach in any way
				if ($dataArr['port'] != '' && !is_numeric($dataArr['port'])) $errorArr[] = $LANG->getLang('portError');
				if ($dataArr['ssh_fingerprint'] != '' && strlen($dataArr['ssh_fingerprint']) != 47) $errorArr[] = $LANG->getLang('sshFingerprintError');
				if ($dataArr['local_amount_chk'] == 1 && $dataArr['local_amount'] == '' || $dataArr['local_amount'] != '' && !is_numeric($dataArr['local_amount'])) $errorArr[] = $LANG->getLang('backupLocalAmountError');
				if ($dataArr['local_days_chk'] == 1 && $dataArr['local_days'] == '' || $dataArr['local_days'] != '' && !is_numeric($dataArr['local_days'])) $errorArr[] = $LANG->getLang('backupLocalDaysError');
				if ($dataArr['local_size_chk'] == 1 && $dataArr['local_size'] == '' || $dataArr['local_size'] != '' && !is_numeric($dataArr['local_size'])) $errorArr[] = $LANG->getLang('backupLocalSizeError');
				if ($dataArr['remote_amount_chk'] == 1 && $dataArr['remote_amount'] == '' || $dataArr['remote_amount'] != '' && !is_numeric($dataArr['remote_amount'])) $errorArr[] = $LANG->getLang('backupRemoteAmountError');
				if ($dataArr['remote_days_chk'] == 1 && $dataArr['remote_days'] == '' || $dataArr['remote_days'] != '' && !is_numeric($dataArr['remote_days'])) $errorArr[] = $LANG->getLang('backupRemoteDaysError');
				if ($dataArr['remote_size_chk'] == 1 && $dataArr['remote_size'] == '' || $dataArr['remote_size'] != '' && !is_numeric($dataArr['remote_size'])) $errorArr[] = $LANG->getLang('backupRemoteSizeError');

				// Convert sizes into bytes
				if ($dataArr['local_size'] != '') $dataArr['local_size'] = lib_div::convertToBytes($dataArr['local_size'], $dataArr['local_size_sel']);
				if ($dataArr['remote_size'] != '') $dataArr['remote_size'] = lib_div::convertToBytes($dataArr['remote_size'], $dataArr['remote_size_sel']);

				// Any erros? return them
				if (count($errorArr) > 0) {
					foreach ($errorArr as $value) {
						$TBE_TEMPLATE->addMessage('error', $value);
					}
					return;
				}

				// Set dump options
				$dataArr['dumpOptions'] = serialize(array(
					'dbcreate' => $dataArr['dbcreate'],
					'complete' => $dataArr['completeinsert'],
					'extended' => $dataArr['extendedinsert']
				));

				// Set maintenances
				$dataArr['maintenance'] = serialize(array(
					'local' => array(
						'amount' => $dataArr['local_amount'],
						'days' => $dataArr['local_days'],
						'size' => $dataArr['local_size']
					),
					'remote' => array(
						'amount' => $dataArr['remote_amount'],
						'days' => $dataArr['remote_days'],
						'size' => $dataArr['remote_size']
					)
				));


				// Calculate next backup time
				$dataArr['next_exec'] = lib_div::calcNextBackupExec($dataArr['frequency'], $dataArr['btime'], $dataArr['weekday'], $dataArr['day']);

				// Set backup time
				$dataArr['time'] = serialize(array(
					'frequency' => $dataArr['frequency'],
					'btime' => $dataArr['btime'],
					'weekday' => $dataArr['weekday'],
					'day' => $dataArr['day']
				));

				// Unset check- and select-boxes
				unset(	$dataArr['local_amount_chk'],
					$dataArr['local_days_chk'],
					$dataArr['local_size_chk'],
					$dataArr['local_size_sel'],
					$dataArr['remote_amount_chk'],
					$dataArr['remote_days_chk'],
					$dataArr['remote_size_chk'],
					$dataArr['remote_size_sel'],
					$dataArr['dbcreate'],
					$dataArr['completeinsert'],
					$dataArr['extendedinsert'],
					$dataArr['local_amount'],
					$dataArr['local_days'],
					$dataArr['local_size'],
					$dataArr['remote_amount'],
					$dataArr['remote_days'],
					$dataArr['remote_size'],
					$dataArr['frequency'],
					$dataArr['btime'],
					$dataArr['weekday'],
					$dataArr['day']
				);

				// Clean table and insert new values
				$res = $GLOBALS['TUPA_DB']->exec_DELETEquery('backup_config', '');
				if (mysql_error()) {
					$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
					lib_logging::addLogMessage('backup', 'delete', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
					return;
				} else {
					$res = $GLOBALS['TUPA_DB']->exec_INSERTquery('backup_config', $dataArr);
					if (mysql_error()) {
						$TBE_TEMPLATE->addMessage('error', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
						lib_logging::addLogMessage('backup', 'add', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
						return;
					} else {
						$TBE_TEMPLATE->addMessage('success', $LANG->getLang('backupUpdateConfigSuccess'));
					}
				}
				break;
		}

	}


}
?>