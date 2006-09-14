<?
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
require('../init.php');

// Check login
$USER->loggedin();

if (!array_key_exists('ifc', $_GET)) die();

switch($_GET['ifc']) {
	case 'fVcore':
		echo lib_div::optimizeJsCode(lib_div::getFileContent(PATH_lib . 'fValidate/fValidate.core.js'));
		break;
	case 'fVconfig':
		echo lib_div::optimizeJsCode(lib_div::getFileContent(PATH_lib . 'fValidate/fValidate.config.js'));
		break;
	case 'fVtupa':
		echo lib_div::optimizeJsCode(lib_div::getFileContent(PATH_lib . 'fValidate/fValidate.tupa-optimized.js'));
		break;
	case 'fVlang':
		echo lib_div::optimizeJsCode(lib_div::getFileContent(PATH_lib . 'fValidate/fValidate.lang-enUS.js'));
		break;

	case 'pLoader':
		echo lib_div::optimizeJsCode(lib_div::getFileContent(PATH_lib . 'js/pageLoader.js'));
		break;

	case 'mainFunc':
		echo lib_div::optimizeJsCode(lib_div::getFileContent(PATH_lib . 'js/mainFunctions.js'));
		break;
	case 'md5':
		echo lib_div::optimizeJsCode(lib_div::getFileContent(PATH_lib . 'js/md5.js'));
		break;
}