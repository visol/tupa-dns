<?php
/**
 * Genrates JavaScript for table/form modification.
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
require ('../../init.php');

// Check login
$USER->loggedin();

// Get the path and sizes of the used icons
$icons = array();
$icons['add']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/add.png');
$icons['add']['size']	= @getimagesize(PATH_site . $icons['add']['path']);
$icons['up']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/up.png');
$icons['up']['size']	= @getimagesize(PATH_site . $icons['up']['path']);
$icons['down']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/down.png');
$icons['down']['size']	= @getimagesize(PATH_site . $icons['down']['path']);
$icons['del']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/garbage.png');
$icons['del']['size']	= @getimagesize(PATH_site . $icons['del']['path']);

?>

var counter = '1';
var attr = new Array();
var ffield;
var newRow;
var domain;

// Some used regular expressions
regexDomain = '<?=addslashes($TUPA_CONF_VARS['REGEX']['templateDomain']) ?>';
regexHost = '<?=addslashes($TUPA_CONF_VARS['REGEX']['host']) ?>';
regexIPv4 = '<?=addslashes($TUPA_CONF_VARS['REGEX']['IPv4']) ?>';
regexIPv6 = '<?=addslashes($TUPA_CONF_VARS['REGEX']['IPv6']) ?>';
regexPTR = '<?=addslashes($TUPA_CONF_VARS['REGEX']['PTR']) ?>';
regexHINFO = '<?=addslashes($TUPA_CONF_VARS['REGEX']['HINFO']) ?>';
regexRP = '<?=addslashes($TUPA_CONF_VARS['REGEX']['RP']) ?>';
regexSRVC = '<?=addslashes($TUPA_CONF_VARS['REGEX']['SRV_CONTENT']) ?>';
regexSRVN = '<?=addslashes($TUPA_CONF_VARS['REGEX']['SRV_NAME']) ?>';
regexUrl = '<?=addslashes($TUPA_CONF_VARS['REGEX']['url']) ?>';

//function addRow(tableid, loc, addFirst, valueArr, domain, updateRF) {
function addRow(tableid, loc, addFirst, valueArr, updateRF) {
	if (!domain) domain = 'domain.tld';
	if (updateRF !== false) updateRF = true;

	var currentRow = document.getElementById(tableid).lastChild.childNodes[loc];
	if (addFirst) {
		counter = '1';
	}
	newRow = document.createElement('TR');

	// Name
	attr = new Array();
	attr['type'] = 'text';
	attr['id'] = 'name_' + counter;
	attr['name'] = 'name_' + counter;
	attr['size'] = '15';
	if (valueArr) attr['value'] = valueArr['name'];
	if (!valueArr) attr['disabled'] = true;
	attr['class'] = 'field';
	attr['className'] = 'field';
	addTableCell('input');

	// Domain placeholder
	attr = new Array();
	attr['value'] = '.' + domain;
	addTableCell('text');

	// TTL
	attr = new Array();
	attr['type'] = 'text';
	attr['id'] = 'ttl_' + counter;
	attr['name'] = 'ttl_' + counter;
	attr['size'] = '5';
	if (valueArr) attr['value'] = valueArr['ttl'];
	if (!valueArr) attr['disabled'] = true;
	attr['class'] = 'field';
	attr['className'] = 'field';
	addTableCell('input');

	// IN
	attr = new Array();
	attr['value'] = 'IN';
	addTableCell('text');

	// Type (A / NS / MX / ...)
	options = new Array();
	isSel = '';
	valueList = 'A,AAAA,MX,NS,PTR,CNAME,TXT,HINFO,RP,SRV';
	<?
	if ($TUPA_CONF_VARS['DNS']['enableFancyRecords']) echo 'valueList += \',MBOXFW,URL\';';
	?>
	valueList = valueList.split(',');
	options[0] = new Option('<?=$LANG->getLang('selectTemplateType') ?>', '');
	for (i=0; i < valueList.length; i++) {
		options[i+1] = new Option(valueList[i], valueList[i]);
		if (valueArr && valueArr['type']==valueList[i]) isSel = i+1;
	}

	attr = new Array();
	attr['isSel'] = isSel;
	attr['id'] = 'type_' + counter;
	attr['name'] = 'type_' + counter;
	attr['size'] = '1';
	attr['class'] = 'field';
	attr['className'] = 'field';
	attr['options'] = options;
	attr['onchange'] = 'updateRowFields(\'records-table\', this.parentNode.parentNode.rowIndex);';
	addTableCell('select');

	// Prio
	attr = new Array();
	attr['type'] = 'text';
	attr['id'] = 'prio_' + counter;
	attr['name'] = 'prio_' + counter;
	attr['size'] = '3';
	if (valueArr) attr['value'] = valueArr['prio'];
	if (!valueArr || (valueArr['type'] != 'MX' && valueArr['type'] != 'SRV')) attr['disabled'] = true;
	attr['class'] = 'field';
	attr['className'] = 'field';
	addTableCell('input');

	// Content
	attr = new Array();
	attr['type'] = 'text';
	attr['id'] = 'content_' + counter;
	attr['name'] = 'content_' + counter;
	attr['size'] = '15';
	if (valueArr) attr['value'] = valueArr['content'];
	if (!valueArr) attr['disabled'] = true;
	attr['class'] = 'field';
	attr['className'] = 'field';
	addTableCell('input');

	// Add after link
	attr = new Array();
	var img = document.createElement('IMG');
	img.src = '<?= $icons['add']['path'] ?>';
	img.alt = '<?= $LANG->getLang('altAddAfter') ?>';
	img.border = '0';
	<?
	if (IE_PNGFIX) {
		echo 'img.style.width = \''. $icons['add']['size'][0] .'px\';';
		echo 'img.style.height = \''. $icons['add']['size'][1] .'px\';';
		echo 'img.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\''. $icons['add']['path'] .'\\\', sizingMethod=scale)\';';
	} else {
		echo 'img.height = \''. $icons['add']['size'][1] .'\';';
		echo 'img.width = \''. $icons['add']['size'][0] .'\';';
	}
	?>
	attr.length = '0';
	attr['href'] = 'javascript:void(0);';
	attr['onclick'] = 'addRow(\'records-table\', this.parentNode.parentNode.rowIndex)';
	attr['img'] = img;
	addTableCell('link');

	// Move up link
	attr = new Array();
	var img = document.createElement('IMG');
	img.src = '<?= $icons['up']['path'] ?>';
	img.alt = '<?=$LANG->getLang('altMoveUp') ?>';
	img.border = '0';
	<?
	if (IE_PNGFIX) {
		echo 'img.style.width = \''. $icons['up']['size'][0] .'px\';';
		echo 'img.style.height = \''. $icons['up']['size'][1] .'px\';';
		echo 'img.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\''. $icons['up']['path'] .'\\\', sizingMethod=scale)\';';
	} else {
		echo 'img.height = \''. $icons['up']['size'][1] .'\';';
		echo 'img.width = \''. $icons['up']['size'][0] .'\';';
	}
	?>
	attr.length = '0';
	attr['href'] = 'javascript:void(0);';
	attr['onclick'] = 'moveRowUp(\'records-table\', this.parentNode.parentNode.rowIndex)';
	attr['img'] = img;
	addTableCell('link');

	// Move up link
	attr = new Array();
	var img = document.createElement('IMG');
	img.src = '<?= $icons['down']['path'] ?>';
	img.alt = '<?=$LANG->getLang('altMoveDown') ?>';
	img.border = '0';
	<?
	if (IE_PNGFIX) {
		echo 'img.style.width = \''. $icons['down']['size'][0] .'px\';';
		echo 'img.style.height = \''. $icons['down']['size'][1] .'px\';';
		echo 'img.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\''. $icons['down']['path'] .'\\\', sizingMethod=scale)\';';
	} else {
		echo 'img.height = \''. $icons['down']['size'][1] .'\';';
		echo 'img.width = \''. $icons['down']['size'][0] .'\';';
	}
	?>
	attr.length = '0';
	attr['href'] = 'javascript:void(0);';
	attr['onclick'] = 'moveRowDown(\'records-table\', this.parentNode.parentNode.rowIndex)';
	attr['img'] = img;
	addTableCell('link');

	// Delete link
	attr = new Array();
	var img = document.createElement('IMG');
	img.src = '<?= $icons['del']['path'] ?>';
	img.alt = '<?=$LANG->getLang('altRemoveLine') ?>';
	img.border = '0';
	<?
	if (IE_PNGFIX) {
		echo 'img.style.width = \''. $icons['del']['size'][0] .'px\';';
		echo 'img.style.height = \''. $icons['del']['size'][1] .'px\';';
		echo 'img.style.filter = \'progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\\\''. $icons['del']['path'] .'\\\', sizingMethod=scale)\';';
	} else {
		echo 'img.height = \''. $icons['del']['size'][1] .'\';';
		echo 'img.width = \''. $icons['del']['size'][0] .'\';';
	}
	?>
	attr.length = '0';
	attr['href'] = 'javascript:void(0);';
	attr['onclick'] = 'removeRow(\'records-table\', this.parentNode.parentNode.rowIndex)';
	attr['img'] = img;
	addTableCell('link');

	// Sorting
	attr = new Array();
	attr['type'] = 'hidden';
	attr['name'] = 'tupasorting_' + counter;
//	attr['class'] = 'field';
	addTableCell('input');

	// Record id
	attr = new Array();
	attr['type'] = 'hidden';
	attr['name'] = 'id_' + counter;
//	attr['class'] = 'field';
	if (valueArr) attr['value'] = valueArr['id'];
	addTableCell('input');

	counter++;
	currentRow.parentNode.insertBefore(newRow, currentRow.nextSibling);

	if (updateRF) {
		updateRows(tableid);
	}

}


function addRowsToEdit(tableid, values, curDom) {
	domain = curDom;
	rowArr = values.split('|');
	isFirst = 1;
	for (n=0; n < rowArr.length; n++) {
		fieldArr = rowArr[n].split(',');
		valueArr = new Array();
		for (k=0; k < fieldArr.length; k++) {
			tmpValueArr = fieldArr[k].split('=');
			tmpValue = mergeArrayValues(tmpValueArr);
			valueArr[tmpValueArr[0]] = tmpValue;
		}
		updateRF = n < rowArr.length - 1 ? false : true;

		addRow(tableid, n, isFirst, valueArr, updateRF);
		isFirst = 0;
	}
}

function mergeArrayValues(tmpValueArr) {
	tmpValue = '';
	first = true;
	if (tmpValueArr.length>2) {
		for (m=1; m < tmpValueArr.length; m++) {
			if (!first) {
				tmpValue += '=';
			}
			tmpValue += tmpValueArr[m];
			first = false;
		}
	} else {
		tmpValue = tmpValueArr[1];
	}
	return tmpValue;
}


function removeRow(tableid, loc) {
	var currentRow = document.getElementById(tableid).lastChild.childNodes[loc];
	currentRow.parentNode.removeChild(currentRow);

	updateRows(tableid);
}


function moveRowUp(tableid, loc) {
	if (loc > '1') {
		var currentRow = document.getElementById(tableid).lastChild.childNodes[loc];
		var prevRow = document.getElementById(tableid).lastChild.childNodes[loc-1];
		currentRow.parentNode.insertBefore(currentRow, prevRow);

		updateRows(tableid);
	}
}


function moveRowDown(tableid, loc) {
	if (loc < document.getElementById(tableid).lastChild.childNodes.length-1) {
		var currentRow = document.getElementById(tableid).lastChild.childNodes[loc];
		var nextRow = document.getElementById(tableid).lastChild.childNodes[loc+1];
		currentRow.parentNode.insertBefore(nextRow, currentRow);

		updateRows(tableid);
	}
}



function addTableCell(type) {
	var newCell = document.createElement('TD');
	type = type.toUpperCase();

	switch (type) {
		case 'INPUT':
			ffield = document.createElement('INPUT');
			addAttr();
			break;
		case 'TEXT':
			ffield = document.createTextNode(attr['value']);
			break;
		case 'LINK':
			ffield = document.createElement('A');
			if (attr['img']) {
				ffield.appendChild(attr['img']);
				delete (attr['img']);
			} else if (attr['text']) {
				ffield.appendChild = document.createTextNode(attr['text']);
				delete (attr['text']);
			}
			addAttr();
			break;
		case 'SELECT':
			ffield = document.createElement('SELECT');
			for (i=0;i < attr['options'].length;i++) {
//				ffield.options.add(attr['options'][i], i);
				ffield.options[ffield.length] = attr['options'][i];
			}
			ffield.selectedIndex = attr['isSel'];
			delete (attr['options']);
			delete (attr['isSel']);
			addAttr();
			break;
	}
	newCell.appendChild(ffield);
	newRow.appendChild(newCell);
}



function addAttr() {
	for ( var key in attr ) {
		// Workaround for IE-bug with on-Events
		if (key.substr(0,2) == 'on') {
			eval('ffield.'+ key + ' = new Function(\'F\', attr[key])');
		} else {
			ffield.setAttribute(key, attr[key]);
		}
	}
}



function updateRows(tableid) {
	var childs = d.getElementById(tableid).lastChild.childNodes.length - 1;
	// loop over all childs
	for (childCounter=1; childCounter<=childs; childCounter++) {
		var child = d.getElementById(tableid).lastChild.childNodes[childCounter];
		childCounter == '1' ? child.childNodes[8].firstChild.style.visibility = 'hidden' : child.childNodes[8].firstChild.style.visibility = 'visible';
		child.parentNode.childNodes.length-1 == childCounter ? child.childNodes[9].firstChild.style.visibility = 'hidden' : child.childNodes[9].firstChild.style.visibility = 'visible';
		child.parentNode.childNodes.length == '2' ? child.childNodes[10].firstChild.style.visibility = 'hidden' : child.childNodes[10].firstChild.style.visibility = 'visible';
		var sortField = child.childNodes[11].firstChild;
		sortField.value = childCounter;
		child = child.nextSibling;
	}
}



function updateRowFields(tableid, loc) {
	// Get the fields
	var currentRow = document.getElementById(tableid).lastChild.childNodes[loc];
	fname = currentRow.childNodes[0].firstChild;
	fttl = currentRow.childNodes[2].firstChild;
	ftype = currentRow.childNodes[4].firstChild;
	fprio = currentRow.childNodes[5].firstChild;
	fcontent = currentRow.childNodes[6].firstChild;

	if (ftype.value == '') {
		// Disable all fields and set value
		setFieldAttribute(fname, true, '', '', '');
		setFieldAttribute(fttl, true, '', '', '');
		setFieldAttribute(fprio, true, '', '', '');
		setFieldAttribute(fcontent, true, '', '', '');
	} else {
		switch (ftype.value) {
			case 'A':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'ip', '<?=$LANG->getLang('ipError') ?>', '');
				break;

			case 'AAAA':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('ipv6Error') ?>', regexIPv6);
				break;

			case 'MX':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, false, '<?=$TUPA_CONF_VARS['DNS']['defaultPrio'] ?>', 'number|0|0|65535', '<?=$LANG->getLang('prioError') ?>', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('domainError') ?>', regexDomain);
				break;

			case 'NS':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('domainError') ?>', regexDomain);
				break;

			case 'PTR':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('ptrError') ?>', regexPTR);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('domainError') ?>', regexDomain);
				break;

			case 'CNAME':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('domainError') ?>', regexDomain);
				break;

			case 'TXT':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, '', '', '');
				break;

			case 'HINFO':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('hinfoError') ?>', regexHINFO);
				break;

			case 'RP':
				setFieldAttribute(fname, false, fname.value, 'custom|bok', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('rpError') ?>', regexRP);
				break;

			case 'SRV':
				setFieldAttribute(fname, false, fname.value, 'custom', '<?=$LANG->getLang('srvNameError') ?>', regexSRVN);
				setFieldAttribute(fprio, false, '0', 'number|0|0|65535', '<?=$LANG->getLang('prioError') ?>', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('srvContentError') ?>', regexSRVC);
				break;

			case 'MBOXFW':
				setFieldAttribute(fname, false, fname.value, 'email|1', '<?=$LANG->getLang('emailError') ?>', '');
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'email|1', '<?=$LANG->getLang('emailError') ?>', '');
				break;

			case 'URL':
				setFieldAttribute(fname, false, fname.value, 'custom', '<?=$LANG->getLang('hostError') ?>', regexHost);
				setFieldAttribute(fprio, true, '', '', '', '');
				setFieldAttribute(fcontent, false, fcontent.value, 'custom', '<?=$LANG->getLang('urlError') ?>', regexUrl);
				break;
		}
		setFieldAttribute(fttl, false, '<?=$TUPA_CONF_VARS['DNS']['defaultTTL'] ?>', 'numeric|1', '<?=$LANG->getLang('ttlError') ?>', '');
	}
}



function setFieldAttribute(fname, disabled, value, alt, emsg, pattern) {
	fname.value = value;
	fname.setAttribute('alt', alt);
	fname.setAttribute('emsg', emsg);
	fname.setAttribute('pattern', pattern);
	disabled ? fname.disabled = true : fname.disabled = false;
}




function removeAllRows(tableid) {
	var childs = document.getElementById(tableid).lastChild.childNodes
	// loop over all childs
	for (i=childs.length-1; i>'0'; i--) {
		childs[i].parentNode.removeChild(childs[i]);
	}
}