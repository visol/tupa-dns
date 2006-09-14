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
 * Functions to generate menu.
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */
class lib_menu {
	var $startPage = '';			// Users configured start page
	var $startPageArr = array();		// array of allowed start pages used in preferences later

	/**
	 * Get the configured startpage if any
	 *
	 */
	function lib_menu() {
		global $TUPA_CONF_VARS;

		$this->startPage = $TUPA_CONF_VARS['PREFS']['startPage'];
	}


	/**
	 * Adds menu point when permissions are set.
	 *
	 * @return	string
	 * @todo 	Maybe change to templates (skins). Possibility to only get the startPageArr (for prefs selection)
	 */
	function mainMenu() {
		global $TUPA_CONF_VARS, $TBE_TEMPLATE, $USER, $LANG;

		// Get the path and sizes of the used icons
		$icons = array();
		$icons['dom']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/domains.png');
		$icons['dom']['size']	= @getimagesize(PATH_site . $icons['dom']['path']);
		$icons['tmpl']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/templates.png');
		$icons['tmpl']['size']	= @getimagesize(PATH_site . $icons['tmpl']['path']);
		$icons['grp']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/groups.png');
		$icons['grp']['size']	= @getimagesize(PATH_site . $icons['grp']['path']);
		$icons['usr']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/users.png');
		$icons['usr']['size']	= @getimagesize(PATH_site . $icons['usr']['path']);
		$icons['tool']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/tools.png');
		$icons['tool']['size']	= @getimagesize(PATH_site . $icons['log']['path']);
		$icons['log']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/logging.png');
		$icons['log']['size']	= @getimagesize(PATH_site . $icons['log']['path']);
		$icons['bkp']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/backup.png');
		$icons['bkp']['size']	= @getimagesize(PATH_site . $icons['bkp']['path']);
		$icons['pref']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/prefs.png');
		$icons['pref']['size']	= @getimagesize(PATH_site . $icons['pref']['path']);
		$icons['sys']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/system.png');
		$icons['sys']['size']	= @getimagesize(PATH_site . $icons['pref']['path']);
		$icons['lout']['path']	= lib_div::getSkinFilePath(PATH_images .'icons/logout.png');
		$icons['lout']['size']	= @getimagesize(PATH_site . $icons['lout']['path']);

		$content = '';

		if ($USER->hasPerm('menu_domains')) {
			$this->startPageArr['domains'] = 'setConf(\'show=>char\', \'ALL\', true, true); showData(\'domains\'); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['domains'] .'"><img src="'. $icons['dom']['path'] .'" '. $icons['dom']['size'][3] .' border="0" alt="'. $LANG->getLang('menuDomains') .'" /> '. $LANG->getLang('menuDomains') .'</a></p>';
		}

		if ($USER->hasPerm('menu_templates')) {
			$this->startPageArr['templates'] = 'setConf(\'show=>char\', \'ALL\', true, true); showData(\'templates\'); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['templates'] .'"><img src="'. $icons['tmpl']['path'] .'" '. $icons['tmpl']['size'][3] .' border="0" alt="'. $LANG->getLang('menuTemplates') .'" /> '. $LANG->getLang('menuTemplates') .'</a></p>';
		}

		if ($USER->hasPerm('menu_groups')) {
			$this->startPageArr['groups'] = 'setConf(\'show=>char\', \'ALL\', true, true); showData(\'groups\'); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['groups'] .'"><img src="'. $icons['grp']['path'] .'" '. $icons['grp']['size'][3] .' border="0" alt="'. $LANG->getLang('menuGroups') .'" /> '. $LANG->getLang('menuGroups') .'</a></p>';
		}

		if ($USER->hasPerm('menu_users')) {
			$this->startPageArr['users'] = 'setConf(\'show=>char\', \'ALL\', true, true); showData(\'users\'); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['users'] .'"><img src="'. $icons['usr']['path'] .'" '. $icons['usr']['size'][3] .' border="0" alt="'. $LANG->getLang('menuUsers') .'" /> '. $LANG->getLang('menuUsers') .'</a></p>';
		}

		if ($USER->hasPerm('menu_tools')) {
			$this->startPageArr['tool'] = 'showTools(); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['tool'] .'"><img src="'. $icons['tool']['path'] .'" '. $icons['tool']['size'][3] .' border="0" alt="'. $LANG->getLang('menuTools') .'" /> '. $LANG->getLang('menuTools') .'</a></p>';
		}

		if ($USER->hasPerm('menu_logging')) {
			$this->startPageArr['logging'] = 'setConf(\'show=>page\', \'1\', true, true); showLogMessages(); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['logging'] .'"><img src="'. $icons['log']['path'] .'" '. $icons['log']['size'][3] .' border="0" alt="'. $LANG->getLang('menuLogging') .'" /> '. $LANG->getLang('menuLogging') .'</a></p>';
		}

		if ($USER->hasPerm('menu_backup')) {
			$this->startPageArr['backup'] = 'showBackup(); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['backup'] .'"><img src="'. $icons['bkp']['path'] .'" '. $icons['bkp']['size'][3] .' border="0" alt="'. $LANG->getLang('menuBackup') .'" /> '. $LANG->getLang('menuBackup') .'</a></p>';
		}

		$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="editUserPrefs(\'userPrefs\'); this.blur(this);"><img src="'. $icons['pref']['path'] .'" '. $icons['pref']['size'][3] .' border="0" alt="'. $LANG->getLang('menuPreferences') .'" /> '. $LANG->getLang('menuPreferences') .'</a></p>';

		if ($USER->hasPerm('menu_system')) {
			$this->startPageArr['system'] = 'showSystemConfig(); this.blur(this);';
			$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="'. $this->startPageArr['system'] .'"><img src="'. $icons['sys']['path'] .'" '. $icons['sys']['size'][3] .' border="0" alt="'. $LANG->getLang('menuSystem') .'" /> '. $LANG->getLang('menuSystem') .'</a></p>';
		}

		$content .= '<p class="menu1"><a href="javascript:void(0);" onclick="openConfirm(\''. lib_div::slashJS($LANG->getLang('confirmLogout')) .'\', \''. lib_div::slashJS("location.href='logout.php';") .'\')"><img src="'. $icons['lout']['path'] .'" '. $icons['lout']['size'][3] .' border="0" alt="'. $LANG->getLang('menuLogout') .'" /> '. $LANG->getLang('menuLogout') .'</a></p>';

		// Check start page an load it if exists
		if ($this->startPage != '' && key_exists($this->startPage, $this->startPageArr)) {
			$content .= $TBE_TEMPLATE->wrapScriptTags(
				'document.onload = '. $this->startPageArr[$this->startPage]
			);
		}

		// get systeminformation when admin logged in
		if($USER->hasPerm('admin')) {
			$content .= $TBE_TEMPLATE->wrapScriptTags(
				'updateShortSysinfo();
				sysrefresh = '. $TUPA_CONF_VARS['SYSINFO']['shortSysinfoRefresh'] .' * 1000;
				sysinfoRefresh = window.setInterval("updateShortSysinfo();", sysrefresh);'
			);
		}

		return $content;
	}
}