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
 * Functions to dump/restore a mysql database
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

class backup {
	var $cronExec = false;
	var $mysqldumpArr = array();
	var $mysqldumpReturn = '';
	var $mysqldump = '';
	var $return = '';
	var $fileName = '';
	var $filePrefix = 'tupa_mysqldump_';
	var $contentEncoding = '';
	var $mimeType = '';
	var $email = '';
	var $compression = '';
	var $dumpOptions = array(
		'dbcreate' => '',
		'complete' => '',
		'extended' => ''
	);
	var $time = array(
		'frequency' => '',
		'weekday' => '',
		'day' => '',
		'btime' => ''
	);
	var $pathLocal = '';
	var $pathRemote = '';
	var $save = '';
	var $protocol = '';
	var $passive = '';
	var $sshFingerprint = '';
	var $rawSshFingerprint = '';
	var $host = '';
	var $port = '';
	var $username = '';
	var $password = '';
	var $transferMode = '';
	var $connId = '';
	var $sftpId = '';
	var $maintenance = array(
		'local' => array (
			'amount' => '',
			'days' => '',
			'size' => ''
		),
		'remote' => array (
			'amount' => '',
			'days' => '',
			'size' => ''
		)
	);
	var $returnMessage = 1;		// 1=>echo (in popup) 2=>addMessage (in PHP)  3=>addMessage (in JS for $UPLOAD only!)
	
	
	/**
	 * Initial backup function.
	 *
	 * @return 	void
	 */
	function runBackup() {
		// Check $_SERVER['_'] value again in class to set cronExec var to prevent the database can be downloaded later in script when no user verification could be made.
//		if (!$_SERVER['REMOTE_ADDR'] && $_SERVER['_']) $this->cronExec = true;
//	echo $this->cronExec;
		if (array_key_exists('dump', $_GET) && $_GET['dump'] == 1 && !$this->cronExec) {
			$this->compression = intval($_GET['compression']);
			$this->dumpAndSend();
		} elseif ((array_key_exists('dump', $_GET) && $_GET['dump'] == 2 && $_GET['useConfig'] == 1) || $this->cronExec) {
			$this->dumpAndStore();
		} elseif (!$this->cronExec) {
			$dumpNr = 1;
			if (array_key_exists('useConfig', $_GET) && $_GET['useConfig'] == 1) $dumpNr = 2;
			// Open a window with message and redirect to dump
			$this->openRedirectDump($dumpNr);
		}
	}
	
	
	function restoreBackup($place, $filename, $addi=array()) {
		global $TBE_TEMPLATE, $LANG;
		
		$skipWrite = false;
		
		if (!$this->getBackupconf()) return;
		
		$this->fileName = basename($filename);
		
		switch ($place) {
			case 'local':
				if (!$this->checkLocalPath()) return;
				break;
			case 'remote':
				if (!$this->checkLocalPath()) return;
				if (!$this->checkRemotePath()) return;
				if (!$this->transferBackup('local')) return;
				break;
				
			case 'restoreFile':
				// Remove value from formfield "file"
				$TBE_TEMPLATE->addMessage('', '', 'd.restore.'. $place .'.value = \'\'; d.getElementById(\''. $place .'\').firstChild.src = \''. $TBE_TEMPLATE->fileUploadSrc('restoreFile', 'restore') .'\';');
				break;
		}
		
		// Read file
		$fullPath = $this->pathLocal . $filename;
		if (!$this->checkLocalPath()) return;
		if (!is_file($fullPath)) {
			lib_logging::addLogMessage('backup', 'file', 'error', 'logMsgBackupFileNotExistsError');
			$this->printMessage('error', 'backupFileNotExistsError', $LANG->getLang('backupCreateLocalDirError'));
			return;
		}
		
		// Decompress file when needed
		if (substr($this->fileName, strlen($this->fileName) - 4) == '.bz2') {
			if (function_exists('bzopen') && $bz = bzopen($fullPath, 'r')) {
				$this->mysqldump = bzread($bz, filesize($fullPath)*35);
				bzclose($bz);
			} else {
				lib_logging::addLogMessage('backup', 'execute', 'error', 'logMsgBackupDecompressError');
				$this->printMessage('error', '', $LANG->getLang('backupDecompressError'));
				$this->printMessage('error', '', $LANG->getLang('functionNotFound', array('function' => 'bzdecompress')));
				return;
			}
		} elseif (substr($this->fileName, strlen($this->fileName) - 3) == '.gz') {
			if (function_exists('gzuncompress')) {
				$this->mysqldump = lib_div::getFileContent($fullPath);
				if (!$this->mysqldump = lib_div::gzdecode($this->mysqldump)) {
					lib_logging::addLogMessage('backup', 'execute', 'error', 'logMsgBackupDecompressError');
					$this->printMessage('error', '', $LANG->getLang('backupDecompressError'));
					return;
				}
			} else {
				lib_logging::addLogMessage('backup', 'execute', 'error', 'logMsgBackupDecompressError');
				$this->printMessage('error', '', $LANG->getLang('backupDecompressError'));
				$this->printMessage('error', '', $LANG->getLang('functionNotFound', array('function' => 'gzuncompress')));
				return;
			}
		} else {
			// skip write to tmp-file later
			$skipWrite = true;
		}
		
		if (!$skipWrite) {
			//$this->deleteFile('local', $fullPath);
			$fullPath = $this->pathLocal . md5(uniqid(rand())) .'.sql';
			
			if (!lib_div::writeFile($fullPath, $this->mysqldump)) {
				lib_logging::addLogMessage('backup', 'store', 'error', 'logMsgBackupDecompressError');
				$this->printMessage('error', '', $LANG->getLang('backupDecompressError'));
				return;
			}
		}
		
		// Execute restore command		
		$mysqlCommand = 'mysql -h'. TUPA_db_host .' -P'. TUPA_db_port .' -u'. TUPA_db_username .' -p'. TUPA_db_password .' -D "'. TUPA_db .'" < '. $fullPath;
		
		$execResult = array();
		$execReturn = '';
		exec($mysqlCommand, $execResult, $execReturn);

		// Delete temp files
		if ($place == 'remote' || $place == 'restoreFile') $this->deleteFile('local', $fullPath);
		
		switch ($execReturn) {
			case 0:
				lib_logging::addLogMessage('backup', 'execute', 'info', 'logMsgBackupRestoreSuccess');
				$this->printMessage('success', '', $LANG->getLang('backupRestoreSuccess'));
				break;
			default:
				lib_logging::addLogMessage('backup', 'execute', 'error', 'logMsgBackupRestoreFailedDefault', array('errno'=>$execReturn));
				$this->printMessage('error', '', $LANG->getLang('backupRestoreFailedDefault', array('errno'=>$execReturn)));
				break;
		}
	}
	
	
	/**
	 * Gets backup configuration and sets class vars.
	 *
	 * @return 	void
	 */
	function getBackupConf() {
		global $LANG;
		
		// Get configuration
		$res = $GLOBALS['TUPA_DB']->exec_SELECTquery('*', 'backup_config', '', '', '', '');
		if (mysql_error()) {
			lib_logging::addLogMessage('backup', 'getConf', 'db', 'logMsgDbError', array('mysqlError' => mysql_error()));
			$this->printMessage('error', 'backupConfigErrorTitle', $LANG->getLang('dbError', array('mysqlError' => mysql_error())));
			return false;
		} elseif (mysql_num_rows($res) == 1) {
			$row = mysql_fetch_assoc($res);
			// unserializes have to be done before htmlspecialchars
			$this->dumpOptions = unserialize($row['dumpOptions']);
			$this->maintenance = unserialize($row['maintenance']);
			$this->time = unserialize($row['time']);
			lib_div::stripSlashesOnArray($row);
			lib_div::htmlspecialcharOnArray($row);
		} elseif (!$this->cronExec && mysql_num_rows($res) == 0) {
			lib_logging::addLogMessage('backup', 'getConf', 'db', 'logMsgNoBackupConfig');
			$this->printMessage('error', 'backupConfigErrorTitle', $LANG->getLang('backupNoBackupConfig'));
			return false;
		} elseif (!$this->cronExec) {
			lib_logging::addLogMessage('backup', 'getConf', 'db', 'logMsgBackupConfigError');
			$this->printMessage('error', 'backupConfigErrorTitle', $LANG->getLang('backupBackupConfigError'));
			return false;
		}
		
		// Set config vars
		$this->email = $row['email'];
		$this->compression = $row['compression'];
		$this->pathLocal = lib_div::checkAddTrailingSlash($row['path_local']);
		$this->save = $row['save'];
			
		$this->protocol = $row['protocol'];
		$this->passive = $row['passive'];
		$this->sshFingerprint = strtoupper(str_replace(':', '', $row['ssh_fingerprint']));
		$this->rawSshFingerprint = $row['ssh_fingerprint'];
		$this->pathRemote = lib_div::checkAddTrailingSlash($row['path_remote']);
		$this->host = $row['host'];
		$this->port = $row['port'];
		$this->username = $row['username'];
		$this->password = $row['password'];
		$this->nextExec = $row['next_exec'];
		
		// Set a default time if noone set in database
		if ($this->time['btime'] == '') $this->time['btime'] = '00:00';
		return true;
	}
	
	
	/**
	 * Generates a simple html page with a message an redirects to the script which generates the mysqldump file.
	 *
	 * @return 	void
	 */
	function openRedirectDump($dNr) {
		global $LANG;
		
		// create simple html site which prints a message and redirect to itself to dump the database
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
			<html>
				<head>
					<title>TUPA dump database</title>
					<meta http-equiv="Refresh" content="0;url=dump.php?dump='. $dNr .'&'. $_SERVER['QUERY_STRING'] .'">
				</head>
				<body>
					<p style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; margin: 10px;">'. $LANG->getLang('backupStartDbDump'. $dNr, array('DB' => '<strong>'. TUPA_db .'</strong>')) .'
					</p>
				</body>
			</html>';
	}
	
	
	/**
	 * Dumps the database and send generated file to client.
	 *
	 * @return 	void
	 */
	function dumpAndSend() {
		// Set dump options
		$dumpOptions = array(
			'dbcreate'	=> $_GET['dbcreate'],
			'complete'	=> $_GET['completeinsert'],
			'extended'	=> $_GET['extendedinsert']
		);
		
		// Execute MySQL dump
		exec($this->createDumpCommand($dumpOptions), $this->mysqldumpArr, $this->mysqldumpReturn);
		
		if ($this->checkDumpSuccessfull()) {
			$this->parseDumpArr();
			
			// send headers
			$this->sendFileHeaders();
			
			// Download data
			print $this->mysqldump;
			
		}
	}
	
	
	/**
	 * Dumps the database and stores it local ore ona a remote server.
	 *
	 * @return 	void
	 */
	function dumpAndStore() {
		global $LANG;
		
		if ($this->getBackupConf()) {
			if (!$this->cronExec && $this->save == 0) {		// "0" means backup disabled
				lib_logging::addLogMessage('backup', 'execute', 'warning', 'logMsgBackupDisabled');
				$this->printMessage('error', 'backupDisabledTitle', $LANG->getLang('backupDisabled'));
				return;	
			}

			if (!$this->checkLocalPath()) return;
			
			if ($this->save == 2) {	// "2" means remote backup (we have to check connection)
				if (!$this->checkRemotePath()) return;
			}

			// Execute MySQL dump
			exec($this->createDumpCommand($this->dumpOptions), $this->mysqldumpArr, $this->mysqldumpReturn);

			if ($this->checkDumpSuccessfull()) {
				$this->parseDumpArr();
				
				// Store backup on local dir
				if (!$this->storeLocal()) {
					$this->closeRemoteConnection();
					return;
				}
				
				// Transfer backup
				if ($this->save == 2) {
					if (!$this->transferBackup()) {
						$this->closeRemoteConnection();
						return;
					}
				
					// Remote maintenance
					$this->remoteMaintenance();
					
					$this->closeRemoteConnection();
				}
				// Local maintenance
				$this->localMaintenance();
			}
			
			// Successfull message
			if ($this->cronExec) {
				lib_logging::addLogMessage('backup', 'execute', 'info', 'logMsgBackupDone');
			}
			$this->printMessage('success', 'backupDoneTitle', $LANG->getLang('backupDone'));
		}
	}
	
	
	/**
	 * Creates the mysqldump command for later execution.
	 *
	 * @return 	string	mysqldump command
	 */
	function createDumpCommand($dO) {
		$mdOptions = '';
		
		// chek for additional mysqldump options
		if (key_exists('dbcreate', $dO) && !$dO['dbcreate']) $mdOptions .= ' --no-create-db';
		if (key_exists('complete', $dO) && $dO['complete']) $mdOptions .= ' --complete-insert';
		if (key_exists('extended', $dO) && $dO['extended']) $mdOptions .= ' --extended-insert';
		
		// get mysqldump
		return  'mysqldump -u'. TUPA_db_username .' -p'. TUPA_db_password .' --databases --add-drop-table --lock-tables'. $mdOptions .' '. TUPA_db;
	}
	
	
	/**
	 * Checks if the mysqldump command was executed successfully and gives back an error message if not.
	 *
	 * @return 	bool	successfull or not
	 */
	function checkDumpSuccessfull() {
		global $LANG;
		
		if ($this->mysqldumpReturn || preg_match('/^mysqldump/', $this->mysqldumpArr[0])) {
			// If we haven an error in mysqldump:
			$error = '';
			switch ($this->mysqldumpReturn) {
				case 0:
					$error = $LANG->getLang('backupUnknownOptionError');
					break;
				case 2:
					$error = $LANG->getLang('backupFalseUsrPwdError');
					break;
				case 127:
					$error = $LANG->getLang('backupMysqldumpNotFoundError');
					break;
			}
			$this->printMessage('error', 'backupDumpErrorTitle', $error);
			return false;
		} else {
			return true;
		}
	}
	
	
	/**
	 * Converts the returned dump result array into a string, sets fiename, mime-type, encoding, and compresses the data if configured.
	 *
	 * @return 	void
	 */
	function parseDumpArr() {
		// get result in one string
		foreach ($this->mysqldumpArr as $line) {
			$this->mysqldump .= $line ."\n";
		}
		
		// Create filename
		$this->fileName = $this->filePrefix . date("Ymd-His") .'.sql';
		$this->transferMode = FTP_ASCII;

		// text/x-sql is correct MIME type, however safari ignores further Content-Disposition header, so we must force it to download it this way...
		$this->mimeType = preg_match('/Safari/', $_SERVER['HTTP_USER_AGENT']) ? 'application/octet-stream' : 'text/x-sql';
			
		// Compress data
		if ($this->compression == 2 && function_exists('bzcompress')) {
			$this->mysqldump = bzcompress($this->mysqldump);
			$this->fileName  .= '.bz2';
			// browsers don't like this:
			//$content_encoding = 'x-bzip2';
			$this->mimeType = 'application/x-bzip2';
			$this->transferMode = FTP_BINARY;
		} else if ($this->compression == 1 && function_exists('gzencode')) {
			$this->mysqldump = gzencode($this->mysqldump);
			$this->fileName  .= '.gz';
			// Needed to avoid recompression by server modules like mod_gzip.
			// It seems necessary to check about zlib.output_compression
			// to avoid compressing twice
			if (!@ini_get('zlib.output_compression') && !$this->cronExec) {
				$this->contentEncoding = 'x-gzip';
				$this->mimeType = 'application/x-gzip';
			}
			$this->transferMode = FTP_BINARY;
		}
	}
	
	
	/**
	 * Sends the headers used for file download.
	 *
	 * @return 	bool	successfull or not
	 */
	function sendFileHeaders() {
		$browser = $_SERVER['HTTP_USER_AGENT'];
		
		if (!empty($this->contentEncoding)) {
			header('Content-Encoding: ' . $this->contentEncoding);
		}
			
		header('Content-Type: ' . $this->mimeType);
		header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
		header('Content-Disposition: attachment; filename="' . $this->fileName . '"');
		header('Content-Length: '.strlen($this->mysqldump));
		if (preg_match('/MSIE 5.5/', $browser) || preg_match('/MSIE 6.0/', $browser)) {
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
		} else {
			header('Pragma: no-cache');
		}
	}
	
	
	/**
	 * Prints an error message in popup window.
	 *
	 * @return 	void
	 */
	function printMessage($type, $siteTitleKey, $msg) {
			global $LANG;
			$title = $siteTitleKey ? $LANG->getLang($siteTitleKey) : '';
			if ($this->returnMessage == 1) {
				$type = $LANG->getLang($type);
				echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">	
					<html>
						<head>
							<title>'. $title .'</title>
						</head>
						<body>
							<p style="font-family: Arial,Helvetica,sans-serif; font-size: 12px; margin: 10px;"><strong>'. $type .'</strong><br />'. $msg .'</p>
						</body>
					</html>';
			} elseif ($this->returnMessage == 2) {
				global $TBE_TEMPLATE;
				$TBE_TEMPLATE->addMessage($type, $msg);
			} elseif ($this->returnMessage == 3) {
				global $UPLOAD, $TBE_TEMPLATE;
				$UPLOAD->content .= $TBE_TEMPLATE->wrapScriptTags('
					window.parent.addMessage(\''. $type .'\', \''. lib_div::convertMsgForJS($msg) .'\');');
			}
	}
	
	
	/**
	 * Checks the local path and tries to create the directory if it not exists.
	 *
	 * @return 	bool		Successfull or not
	 */
	function checkLocalPath() {
		global $LANG;
		
		if (!$this->pathLocal) {
			// Add log error message and stop processing
			lib_logging::addLogMessage('backup', 'dir', 'error', 'logMsgBackupNoLocalDirConfError');
			$this->printMessage('error', 'backupConfigErrorTitle', $LANG->getLang('backupNoLocalDirConfError'));
			return false;
		} elseif (!is_dir($this->pathLocal)) {
			if (!lib_div::mkdirRecursive($this->pathLocal)) {
				// Add log error message and stop processing
				lib_logging::addLogMessage('backup', 'dir', 'error', 'logMsgBackupCreateLocalDirError', array('dir' => $this->pathLocal));
				$this->printMessage('error', 'backupDirectoryErrorTitle', $LANG->getLang('backupCreateLocalDirError', array('dir' => $this->pathLocal)));
				return false;
			}
		}
		
		// Check if local dir is writable
		if (!is_writable($this->pathLocal)) {
			// Add log error message and stop processing
			lib_logging::addLogMessage('backup', 'dir', 'error', 'logMsgBackupLocalDirNotWriteableError', array('dir' => $this->pathLocal));
			$this->printMessage('error', 'backupDirectoryErrorTitle', $LANG->getLang('backupLocalDirNotWriteableError', array('dir' => $this->pathLocal)));
			return false;
		}
		
		return true;
	}
	
	
	/**
	 * Checks the connection to the remote host and the path. Tries to create the directory if it not exists.
	 *
	 * @return 	bool		Successfull or not
	 */
	function checkRemotePath() {
		global $LANG;
		
		// check if we have a path first before trying to connect
		if (!$this->pathRemote) {
			lib_logging::addLogMessage('backup', 'dir', 'error', 'logMsgBackupNoRemoteDirConfError');
			$this->printMessage('error', 'backupConfigErrorTitle', $LANG->getLang('backupNoRemoteDirConfError'));
			return false;
		}
		
		// connect if not already done
		if (!$this->openRemoteConnection()) return false;

		switch ($this->protocol) {
			case 0:		// FTP
				// Change into directory or try to create it if it not exists
				if (!@ftp_chdir($this->connId, $this->pathRemote)) {
					if (!lib_div::ftp_mkdirRecursive($this->connId, $this->pathRemote)) {
						ftp_close($this->connId);
						lib_logging::addLogMessage('backup', 'dir', 'error', 'logMsgBackupCreateRemoteDirError', array('dir' => $this->pathRemote));
						$this->printMessage('error', 'backupDirectoryErrorTitle', $LANG->getLang('backupCreateRemoteDirError', array('dir' => $this->pathRemote)));
						return false;
					}
				}
				break;
			case 1:		// SSH
				$stdio  = ssh2_exec($this->connId, 'cd '. $this->pathRemote);
				$stderr = ssh2_fetch_stream($stdio, SSH2_STREAM_STDERR);
				stream_set_blocking($stdio, true);
				
//				while($data = fread($stdio, 4096)) {
//					$dataStd .= $data;
//				}
				while($data = fread($stderr, 1024)) {
					$dataErr .= $data;
				}
				fclose($stdio);
				fclose($stderr);
				
				if ($dataErr) {
					if (!lib_div::ssh_mkdirRecursive($this->connId, $this->sftpId, $this->pathRemote)) {
						lib_logging::addLogMessage('backup', 'dir', 'error', 'logMsgBackupCreateRemoteDirError', array('dir' => $this->pathRemote));
						$this->printMessage('error', 'backupDirectoryErrorTitle', $LANG->getLang('backupCreateRemoteDirError', array('dir' => $this->pathRemote)));
						return false;
					}
				}
				break;
		}
		return true;
	}
	
	
	/**
	 * Transfers the backup file to remote server.
	 *
	 * @param 	string		Direction (to remote or local)
	 * @return 	bool		Successfull or not
	 */
	function transferBackup($direction = 'remote') {
		global $LANG;

		$remote = $this->pathRemote . $this->fileName;
		
		if ($direction == 'remote') {		// Transfer TO remote server
			$local = $this->pathLocal . $this->fileName;
			switch ($this->protocol) {
				case 0:		// FTP
					if (!ftp_put($this->connId, $remote, $local, $this->transferMode)) {
						lib_logging::addLogMessage('backup', 'store', 'error', 'logMsgBackupTransferRemoteError');
						$this->printMessage('error', 'backupTransferErrorTitle', $LANG->getLang('backupTransferRemoteError'));
						return false;
					}
					lib_div::ftpChmod($this->connId, 0700, $this->fileName);
					break;
				case 1:		// SSH
					if (!ssh2_scp_send($this->connId, $local, $remote, 0700)) {
						lib_logging::addLogMessage('backup', 'store', 'error', 'logMsgBackupTransferRemoteError');
						$this->printMessage('error', 'backupTransferErrorTitle', $LANG->getLang('backupTransferRemoteError'));
						return false;
					}
					break;
			}
		} elseif ($direction == 'local') {	// Transfer FROM remote server
			$this->transferMode = substr($this->fileName, strlen($this->fileName) - 3) == 'sql' ? FTP_ASCII : FTP_BINARY;
			$this->fileName = 'tmp_'. $this->fileName;
			$local = $this->pathLocal . $this->fileName;
			switch ($this->protocol) {
				case 0:		// FTP
					if (!ftp_get($this->connId, $local, $remote, $this->transferMode)) {
						lib_logging::addLogMessage('backup', 'store', 'error', 'logMsgBackupTransferLocalError');
						$this->printMessage('error', 'backupTransferErrorTitle', $LANG->getLang('backupTransferLocalError'));
						return false;
					}
					break;
				case 1:		// SSH
					if (!ssh2_scp_recv($this->connId, $remote, $local)) {
						lib_logging::addLogMessage('backup', 'store', 'error', 'logMsgBackupTransferLocalError');
						$this->printMessage('error', 'backupTransferErrorTitle', $LANG->getLang('backupTransferLocalError'));
						return false;
					}
					break;
			}
			chmod($local, 0700);
		}
		return true;
	}
	
	
	/**
	 * Stores the created backup to local directory
	 *
	 * @return 	bool		Successfull or not
	 */
	function storeLocal() {
		global $LANG;
		$storePath = $this->pathLocal . $this->fileName;
		if (!lib_div::writeFile($storePath, $this->mysqldump)) {
			lib_logging::addLogMessage('backup', 'store', 'error', 'logMsgBackupWriteLocalError', array('file' => $storePath));
			$this->printMessage('error', 'backupWriteErrorTitle', $LANG->getLang('backupWriteLocalError', array('file' => $storePath)));
			 return false;
		}
		return true;
	}
	
	
	/**
	 * Connect to remote server
	 *
	 * @return 	bool		Connection sucessfull
	 */
	function openRemoteConnection() {
		global $LANG;
		
		if ($this->connId) return;
		switch ($this->protocol) {
			case 0:		// FTP
				if (!$this->port) $this->port = 21;
			
				if (!function_exists('ftp_connect')) {
					lib_logging::addLogMessage('backup', 'function', 'error', 'logMsgFunctionNotFound', array('function' => 'ftp_connect'));
					$this->printMessage('error', 'backupFunctionErrorTitle', $LANG->getLang('functionNotFound', array('function' => 'ftp_connect')));
					return false;
				}

				// connect to FTP server
				if (!$this->connId = @ftp_connect($this->host, $this->port)) {
					lib_logging::addLogMessage('backup', 'connect', 'error', 'logMsgBackupFtpConnectError', array('host' => $this->host, 'port' => $this->port));
					$this->printMessage('error', 'backupConnectErrorTitle', $LANG->getLang('backupFtpConnectError', array('host' => $this->host, 'port' => $this->port)));
					return false;
				}

				// Try to login
				if (!@ftp_login($this->connId, $this->username, $this->password)) {
					ftp_close($this->connId);
					lib_logging::addLogMessage('backup', 'connect', 'error', 'logMsgBackupFtpLoginError');
					$this->printMessage('error', 'backupLoginErrorTitle', $LANG->getLang('backupFtpLoginError'));
					return false;
				}
				
				// Set passive mode
				ftp_pasv($this->connId, ($this->passive == 1 ? true : false));
				break;
			case 1:		// SSH
				if (!$this->port) $this->port = 22;
			
				// Connect to SSH server
				if (!function_exists('ssh2_connect')) {
					lib_logging::addLogMessage('backup', 'function', 'error', 'logMsgFunctionNotFound', array('function' => 'ssh2_connect'));
					$this->printMessage('error', 'backupFunctionErrorTitle', $LANG->getLang('functionNotFound', array('function' => 'ssh2_connect')));
					return false;
				}
				
				 // connect to SSH server
				if (!$this->connId = @ssh2_connect($this->host, $this->port)) {
					lib_logging::addLogMessage('backup', 'connect', 'error', 'logMsgBackupSshConnectError', array('host' => $this->host, 'port' => $this->port));
					$this->printMessage('error', 'backupConnectErrorTitle', $LANG->getLang('backupSshConnectError', array('host' => $this->host, 'port' => $this->port)));
					return false;
				}

				// Verify fingerprint
				if (@ssh2_fingerprint($this->connId, SSH2_FINGERPRINT_HEX) != $this->sshFingerprint) {
					lib_logging::addLogMessage('backup', 'connect', 'error', 'logMsgBackupSshFingerprintError');
					$this->printMessage('error', 'backupConnectErrorTitle', $LANG->getLang('backupSshFingerprintLoginError'));
					return false;
				}
				
				// Try to login
				if (!@ssh2_auth_password($this->connId, $this->username, $this->password)) {
					lib_logging::addLogMessage('backup', 'connect', 'error', 'logMsgBackupSshLoginError');
					$this->printMessage('error', 'backupLoginErrorTitle', $LANG->getLang('backupSshLoginError'));
					return false;
				}
				
				// Initialize SFTP subsystem
				$this->sftpId = ssh2_sftp($this->connId);
				break;
		}
		return true;
	}
	
	
	/**
	 * Closes the remote connection if any
	 *
	 * @return 	void
	 */
	function closeRemoteConnection() {
		if ($this->connId) {
			switch ($this->protocol) {
				case 0:		// FTP
					@ftp_close($this->connId);
					break;
				case 1:		// SSH
					// There is no command to disconnect a SSH connection..
					break;
			}
		}
	}
	
	
	/**
	 * Prepares to delete old backups on local backup directory
	 *
	 * @return 	void
	 */
	function localMaintenance() {
		if ($fileArr = $this->getLocalFileListArray()) {
			$this->maintenanceTasks('local', $fileArr, $this->pathLocal);
		}	
	}
	
	
	/**
	 * Prepares to delete old backups on remote backup directory
	 *
	 * @return 	void
	 */
	function remoteMaintenance() {
		if ($fileArr = $this->getRemoteFileListArray()) {
			$this->maintenanceTasks('remote', $fileArr, $this->pathRemote);
		}
	}
	
	
	
	
	/**
	 * Gets an array of files with name, mtime and size on the local backup directory.
	 *
	 * @return 	void
	 */
	function getLocalFileListArray() {
		$fileArr = array();
		$path = $this->pathLocal;
		$tmpSortTime = array();
		
		// Get all needed file data
		if (is_dir($path)) {
			if ($dh = opendir($path)) {
				while (($file = readdir($dh)) !== false) {
					if (is_file($path . $file) && preg_match('/^'. $this->filePrefix .'/', $file)) {
						$fileTime = filemtime($path . $file);
						$tmpSortTime[] = $fileTime;
						$fileArr[] = array(
							'name' => $file,
							'mtime' => $fileTime,
							'size' => filesize($path . $file)
						);
					}
				}
				closedir($dh);
				array_multisort($tmpSortTime, SORT_DESC, $fileArr);
				unset($tmpSortTime);
			}
		}
		return count($fileArr) > 0 ? $fileArr : false;
	}
	
	
	/**
	 * Gets an array of files with name, mtime and size on the remote backup directory.
	 *
	 * @return 	void
	 */
	function getRemoteFileListArray() {
		if ($this->save != 2) return;
		
		$this->openRemoteConnection();
		$path = $this->pathRemote;
		$listArr = array();
		$tmpSortTime = array();
		$fileArr = array();
		
		// Get list of file names
		if ($this->connId) {
			switch ($this->protocol) {
				case 0:		// FTP
					$listArr = ftp_nlist($this->connId, $path);
					break;
				case 1:		// SSH
					$stream = ssh2_exec($this->connId, 'ls -1 '. $path);
					stream_set_blocking($stream, true);		
					if($stream) {
						while ($line = fgets($stream)) {
							$listArr[] = $line;
						}
					}
					break;
			}
		}
		
		foreach ($listArr as $file) {
			if (preg_match('/^'. $this->filePrefix .'/', $file)) {
				$sqlPos = strpos($file, '.sql');
				$datePart = substr($file, $sqlPos-15, 15);
				$fileTime = mktime(substr($datePart, 9,2), substr($datePart, 11,2), substr($datePart, 13,2), substr($datePart, 4,2), substr($datePart, 6,2), substr($datePart, 0,4));
				$tmpSortTime[] = $fileTime;
				$fileArr[] = array(
					'name' => trim($file),
					'mtime' => $fileTime,
					'size' => $this->getRemoteSize($path . $file)
				);
			}
		}
		array_multisort($tmpSortTime, SORT_DESC, $fileArr);
		unset($tmpSortTime);
		
		return count($fileArr) > 0 ? $fileArr : false;
	}
	
	
	/**
	 * Gets size of a file on a remote server.
	 *
	 * @param 	string		Path to file
	 * @return 	integer		File size in b
	 */
	function getRemoteSize($path) {
		switch ($this->protocol) {
			case 0:		// FTP
				return ftp_size($this->connId, $path);
				break;
			case 1:		// SSH
				// Will not work somhow
				//$fileInfo = ssh2_sftp_lstat($this->sftpId, $path);
				
				// More complicate but working solution
				$stdio  = ssh2_exec($this->connId, 'stat --format=%s '. $path);
				stream_set_blocking($stdio, true);
				
				$fileSize = trim(fgets($stdio, 1024));
				fclose($stdio);
				
				return $fileSize;
				break;
		}
	}
	
	
	/**
	 * Maintenance tasks to perform on local or remote server.
	 *
	 * @param 	string		For which location (local or remote)
	 * @param 	array		Array with all needed data (name, mtime and size)
	 * @param 	string		Path to delete in
	 * @return 	void
	 */
	function maintenanceTasks($place, $fileArr, $path) {
		// Amount
		if ($this->maintenance[$place]['amount']) {
			$arrayCount = count($fileArr);
			for ($i = $this->maintenance[$place]['amount']; $i < $arrayCount; $i++) {
				// Delete file and unset var in array
				if ($this->deleteFile($place, $path . $fileArr[$i]['name'])) {
					unset($fileArr[$i]);
				}
			}
		}
		
		// Days
		if ($this->maintenance[$place]['days']) {
			$timeLimit = time() - $this->maintenance[$place]['days'] * 86400;
			$arrayCount = count($fileArr);
			for ($i = 0; $i < $arrayCount; $i++) {
				if ($fileArr[$i]['mtime'] < $timeLimit) {
					if ($this->deleteFile($place, $path . $fileArr[$i]['name'])) {
						unset($fileArr[$i]);
					}
				}
			}
		}
		
		// Size
		if ($this->maintenance[$place]['size']) {
			$totalSize = 0;
			while (list($i, $value) = each($fileArr)) {
				$totalSize += $value['size'];
				if ($totalSize > $this->maintenance[$place]['size']) {
					if ($this->deleteFile($place, $path . $fileArr[$i]['name'])) {
						unset($fileArr[$i]);
					}
				}
			}
		}
	}
	
	
	/**
	 * Deletes a file on local or remote server.
	 *
	 * @param 	string		Where to delete files (local or remote)
	 * @return 	bool
	 */
	function deleteFile($place, $file) {
		switch ($place) {
			case 'local':
				if (is_file($file)) {
					if (unlink($file)) return true;
				}
				break;
			case 'remote':
				switch ($this->protocol) {
					case 0:		// FTP
						if (ftp_delete($this->connId, $file)) return true;
						break;
					case 1:		// SSH
					echo $file;
						if (ssh2_sftp_unlink($this->sftpId, $file)) return true;
						break;
				}
				break;
		}
		return false;
	}
	
	
}

?>