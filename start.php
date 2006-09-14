<?php
/**
 * Start page
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

// IE transparency fix - Part 1
ob_start();

// Include needed files and start session
require('init.php');

// Check login
$USER->loggedin();

$hiddenFields = '';

// Set the language cookie for one year. Used for next login.
setcookie('lang', $TUPA_CONF_VARS['SYS']['language'] , time()+60*60*24*30);

// Get the path and sizes of the used icons
$icons = array();
$icons['clock']['path']		= lib_div::getSkinFilePath(PATH_images .'pageloader_clock.gif');
$icons['clock']['size']		= @getimagesize(PATH_site . $icons['clock']['path']);
$icons['bglogo']['path']	= lib_div::getSkinFilePath(PATH_images .'bg-logo.png');
$icons['bglogo']['size']	= @getimagesize(PATH_site . $icons['bglogo']['path']);
$icons['logotop']['path']	= lib_div::getSkinFilePath(PATH_images .'logo-top.png');
$icons['logotop']['size']	= @getimagesize(PATH_site . $icons['logotop']['path']);

// Get the template
$templateFileContent = $TBE_TEMPLATE->fileContent(PATH_templates .'start.html');
// Get template subpart
$subpart = $TBE_TEMPLATE->getSubpart($templateFileContent, '###TUPA_START###');

$markerArray['meta_generator'] = $TBE_TEMPLATE->generator();
$markerArray['page_title'] = 'TUPA: The Ultimate PowerDNS Admin';
$markerArray['style_sheet'] = '<link rel="stylesheet" type="text/css" href="'. lib_div::getSkinFilePath(PATH_skin_root .'styles.css') .'" />';

$markerArray['javascript'] = '
	<!-- Load the page loader... // -->
	<script type="text/javascript" src="lib/includeFile.php?ifc=pLoader"></script>
	<!-- Load the generated client side code... // -->
	<script type="text/javascript" src="lib/js/requestHandler.php?client"></script>
	<!-- Include the fValidate files // -->
	<script type="text/javascript" src="lib/includeFile.php?ifc=fVconfig"></script>
	<script type="text/javascript" src="lib/includeFile.php?ifc=fVcore"></script>
	<script type="text/javascript" src="lib/includeFile.php?ifc=fVlang"></script>
	<script type="text/javascript" src="lib/includeFile.php?ifc=fVtupa"></script>
	<!-- Include main file // -->
	<script type="text/javascript" src="lib/includeFile.php?ifc=mainFunc"></script>
	<script type="text/javascript" src="lib/includeFile.php?ifc=md5"></script>
	<script type="text/javascript" src="lib/js/tableEdit.php"></script>
	<script type="text/javascript" src="lib/js/sessionTimer.php"></script>
	';
// When user is admin, include the calendar JavaScript (because it's only used for logging at the moment)
if ($USER->hasPerm('admin')) {
	$markerArray['javascript'] .= $jsCalendar->load_files();
}

$pageLoaderMessage = '
	<table cellpadding="0" cellspacing="0" border="0">
		<tr>
			<td valign="middle" align="left" width="40"><img src="'. $icons['clock']['path'] .'" '. $icons['clock']['size'][3] .' align="middle" border="0" alt="clock" /></td>
			<td valign="middle"><strong>'. $LANG->getLang('pageLoaderLoading') .'</strong><br />'. $LANG->getLang('pageLoaderMsg') .'</td>
		</tr>
	</table>
	';

$markerArray['div_pageloader_trans'] = $TBE_TEMPLATE->wrapInDiv('pageloader-trans');
$markerArray['div_pageloader_msg'] = $TBE_TEMPLATE->wrapInDiv('pageloader-msg', $pageLoaderMessage);

$markerArray['div_help_layer'] = $TBE_TEMPLATE->wrapInDiv('helpLayer', '', 'style="top:'. $TUPA_CONF_VARS['HELP']['draggableXPos'] .'px; left:'. $TUPA_CONF_VARS['HELP']['draggableYPos'] .'px;"');
$markerArray['div_bg_top'] = $TBE_TEMPLATE->wrapInDiv('bg-top');
$markerArray['div_bg_left'] = $TBE_TEMPLATE->wrapInDiv('bg-left');
$markerArray['div_bg_logo'] = $TBE_TEMPLATE->wrapInDiv('bg-logo', '<img src="'. $icons['bglogo']['path'] .'" '. $icons['bglogo']['size'][3] .' alt="'. $LANG->getLang('startBgLogo') .'" />');
$markerArray['div_logo'] = $TBE_TEMPLATE->wrapInDiv('logo', '<img src="'. $icons['logotop']['path'] .'" '. $icons['logotop']['size'][3] .' alt="'. $LANG->getLang('startLogoTop') .'" />');

// Add or remove Sysinfo DIV
if ($USER->hasPerm('admin')) {
	$markerArray['div_sysinfo'] = $TBE_TEMPLATE->wrapInDiv('sysinfo', '');
} else {
	$markerArray['div_sysinfo'] = '';
}

$markerArray['div_menu'] = $TBE_TEMPLATE->wrapInDiv('menu', $MENU->mainMenu() .'<input type="hidden" name="csite" />');
$markerArray['div_content'] = $TBE_TEMPLATE->wrapInDiv('content-container', $TBE_TEMPLATE->wrapInDiv('content', ''));


// Substitute markers
echo $subpart = $TBE_TEMPLATE->substituteMarkerArray($subpart, $markerArray, '###|###', '1');

// IE transparency fix - Part 2
echo $TBE_TEMPLATE->replacePngTags(ob_get_clean(), 'images/');
?>