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

// Include needed files and start session
require('init.php');

// Check login
$USER->loggedin();

class upload {
	
	var $content = '';
	var $did = '';			// DIV - id
	var $uploadType = '';		// single or multi
	var $uploadTargetCode = '';	// Code to get target directory
	var $uploadTarget = '';	// The real target path
		
	
	function init() {
		global $USER;
		
		$this->content = '
			<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
     				<html><head>
				<script type="text/javascript" src="lib/js/mainFunctions.js"></script>
     				<link rel="stylesheet" type="text/css" href="'. lib_div::getSkinFilePath(PATH_skin_root .'styles.css') .'" />';
		
		// Check permissions
		if (!$USER->hasPerm('admin')) {
			lib_logging::addLogMessage('backup', 'function', 'permission', 'logMsgNoPermission');
			global $LANG;
			$this->content .= '<p>'. $LANG->getLang('noPagePermissions') .'</p>';
			return;
		}
		
		// Check if we have the GET values set - Show file field
		if ($_GET['did'] && $_GET['type'] && $_GET['target']) {
			$this->did = addslashes($_GET['did']);
			$this->uploadType = intval($_GET['type']);
			$this->uploadTargetCode = addslashes($_GET['target']);
			
			$this->content .= $this->setIframeSizeJsCode();
			
			$this->content .= $this->addUploadJsCode();
			
			$this->content .= $this->formBodyCode($this->getAllowedExt());
		} elseif ($_POST['target'] && $_POST['did'] && $_POST['type'] && $_FILES['file'] && !($_GET['did'] || $_GET['type'] || $_GET['target'])) {
			$this->did = addslashes($_POST['did']);
			$this->uploadType = intval($_POST['type']);
			$this->uploadTargetCode = addslashes($_POST['target']);
			
			if ($this->getTargetPath() === true) {
				$source = $_FILES['file']['tmp_name'];
				$fileName = 'tmp_'. $_FILES['file']['name'];
				$target = $this->uploadTarget . $fileName;
				if (move_uploaded_file($source, $target)) {
					$this->content .= $this->updateParentField($fileName);
					$this->content .= $this->bodyUploadDone($fileName);
				} else {
					global $BACKUP, $LANG;
					lib_logging::addLogMessage('backup', 'store', 'security', 'logMsgBackupUploadAttackError', array('filename' => $fileName));
					$BACKUP->printMessage('error', '', $LANG->getLang('backupUploadAttackError', array('filename' => $fileName)));
				}
			} else {
				$this->content .= $this->addUploadJsCode();
				$this->content .= $this->formBodyCode($this->getAllowedExt());
				$this->content .= '
					</body>
					</html>';
				return;
			}
		} else {
			$this->content .= $this->bodyError();
		}
		
		$this->content .= '
			</body>
			</html>';
	}

	
	function setIframeSizeJsCode() {
		global $TBE_TEMPLATE;
		
		return  $TBE_TEMPLATE->wrapScriptTags('
			function setIframeSize() {
				width = document.forms[0].file.offsetWidth;
				height = document.forms[0].file.offsetHeight;
				if (width > 0 && height > 0) {
					if (parent.browserChk(\'msie\') || parent.browserChk(\'opera\')) {
						width += 2;
						height += 2;
					}
					uploadiframe = parent.document.getElementById(\''. $this->did .'\').firstChild;
					uploadiframe.width = width;
					uploadiframe.height = height;
				}
			}');
	}
	
	
	function addUploadJsCode() {
		switch ($this->uploadType) {
			default:
			case 1:		// Single upload
				return $this->uploadSingleJsCode();
				break;
			case 2:		// Multi upload
				return $this->uploadMultiJsCode();
				break;
		}
	}
	
	
	function uploadSingleJsCode() {
		global $TBE_TEMPLATE;

		return  $TBE_TEMPLATE->wrapScriptTags('
			function upload() {
				par = window.parent;
				form = document.forms[0];
				
				par.cleanMessages();
				if (!par.validateForm(form,false,false,false,false,16)) return false;
				form.style.display = \'none\';
				document.getElementById(\'uploadClock\').style.display = \'block\';
				form.submit();
			}');
	}
	
	
	function uploadMultiJsCode() {
		global $TBE_TEMPLATE;

		return  $TBE_TEMPLATE->wrapScriptTags('
			function upload() {
			// hide old iframe
			var par = window.parent.document;
			var num = par.getElementsByTagName(\'iframe\').length - 1;
			var iframe = par.getElementsByTagName(\'iframe\')[num];
			iframe.className = \'hidden\';
			
			// create new iframe
			var new_iframe = par.createElement(\'iframe\');
			new_iframe.src = \'upload.php\';
			new_iframe.frameBorder = \'0\';
			par.getElementById(\'iframe\').appendChild(new_iframe);
			
			// add image progress
			var images = par.getElementById(\'images\');
			var new_div = par.createElement(\'div\');
			var new_img = par.createElement(\'img\');
			new_img.src = \'indicator.gif\';
			new_img.className = \'load\';
			new_div.appendChild(new_img);
			images.appendChild(new_div);
			
			// send
			var imgnum = images.getElementsByTagName(\'div\').length - 1;
			document.iform.imgnum.value = imgnum;
			document.iform.submit();
		}');
	}
	
	
	function getTargetPath() {
		switch($this->uploadTargetCode) {
			case 'restore':
				// Include backup class
				require_once(PATH_lib .'class.backup.php');
				$BACKUP = lib_div::makeInstance('backup');
				$BACKUP->returnMessage = 3;
				if (!$BACKUP->getBackupConf()) return false;
				if (!$BACKUP->checkLocalPath()) return false;
				$this->uploadTarget = $BACKUP->pathLocal;
				return true;
				break;
		}
		return false;
	}
	
	
	function getAllowedExt() {
		switch($this->uploadTargetCode) {
			case 'restore':
				return 'sql,sql.gz,sql.bz2';
				break;
		}
		return '';
	}
	
	
	function updateParentField($fileName) {
		global $TBE_TEMPLATE;
		
		switch($this->uploadTargetCode) {
			case 'restore':
				if ($this->uploadType == 1) {
					return $TBE_TEMPLATE->wrapScriptTags('
						window.parent.d.restore.'. $this->did .'.value = \''. lib_div::slashJS($fileName) .'\'');
				}
				break;
		}
	}
	
	
	function formBodyCode($allowedExt, $bok=true) {
		global $LANG;
		
		// Remove spaces
		$allowedExt = str_replace(' ', '', $allowedExt);
		
		$hiddenFields = '
			<input type="hidden" name="MAX_FILE_SIZE" value="'. lib_div::convertMaxUploadFilesize() .'" />
			<input type="hidden" name="did" value="'. $this->did .'" />
			<input type="hidden" name="type" value="'. $this->uploadType .'" />
			<input type="hidden" name="target" value="'. $this->uploadTargetCode .'" />';
		
		if ($this->uploadType == 2) {
			$hiddenFields .= '
				<input type="hidden" name="fid" />';
		}
		
		return '
			</head>
			
			<body onload="setIframeSize();">
			<form name="iform" action="'. $_SERVER['SCRIPT_NAME'] .'" method="post" enctype="multipart/form-data">
			<input type="file" name="file" size="40" alt="file|'. $allowedExt .'|0'. ($bok ? '|bok' : '') .'" emsg="'. $LANG->getLang('backupRestoreFileExtError') .'"  onchange="upload()" class="'. STYLE_FIELD .'" />
			'. $hiddenFields .'
			</form>
			<div id="uploadClock" style="display: none;">
				<table cellspacing="0" cellpadding="0" border="0">
					<tr>
						<td><img src="'. lib_div::getSkinFilePath(PATH_skin_root .'images/upload_clock.gif') .'" /></td>
						<td valign="middle"><p>&nbsp;&nbsp;Uploading file...</p></td>
					</tr>
				</table>
			</div>';
	}
	
	
	function bodyUploadDone($fileName) {
		return '
			<table cellspacing="0" cellpadding="0" border="0">
				<tr>
					<td><img src="'. lib_div::getSkinFilePath(PATH_skin_root .'images/icons/ok.png') .'" /></td>
					<td valign="middle"><p>'. $fileName .'</p></td>
				</tr>
			</table>';
	}
	
	
	function bodyError() {
		return '
			</head>
			
			<body>
			<p>Error</p>';
	}
}


$UPLOAD = lib_div::makeInstance('upload');
$UPLOAD->init();
echo $UPLOAD->content;

?>