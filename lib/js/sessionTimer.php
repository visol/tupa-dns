<?php
/**
 * Genrates JavaScript to detect session expiration.
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

$sessionTTL = $TUPA_CONF_VARS['SYS']['sessionTTL'];
$sessionTimeoutWarning = $TUPA_CONF_VARS['SYS']['sessionTimeoutWarning'];
?>


function sessTimer() {
	this.loginRefreshed = sessTimer_loginRefreshed;
	this.checkLoginTimeout = sessTimer_checkLoginTimeout;
	this.sessTimerUpdate=2000;
	this.sessTime=0;
	this.timerState=0;
}
function sessTimer_loginRefreshed() {
	var date = new Date();
	this.sessTime = Math.floor(date.getTime()/1000);
	this.timerState=0;
	sessTimer.sessTimerUpdate=2000;
}
function sessTimer_checkLoginTimeout() {
	var date = new Date();
	var theTime = Math.floor(date.getTime()/1000);
	if (<?= $sessionTimeoutWarning ?> > 0 && sessTimer.timerState < 1 && theTime > this.sessTime+<?= $sessionTTL ?>-<?= $sessionTimeoutWarning ?>) {
		sessTimer.timerState=1;
		return true;
	} else if (theTime > this.sessTime+<?= $sessionTTL ?>) {
		sessTimer.timerState=2;
		return true;
	}
}
function sessTimer_checkLoginTimeout_timer() {
	if (sessTimer.checkLoginTimeout()) {
		if (sessTimer.timerState == 1) {
			alert('<?= $LANG->getLang('sessionExpireWarning', array('seconds'=>$sessionTimeoutWarning)) ?>');
			sessTimer.sessTimerUpdate=500;
		} else if (sessTimer.timerState == 2) {
			alert('<?= $LANG->getLang('sessionExpired') ?>');
			location.href='<?= $USER->logout_page ?>';
		}
	}
	if (sessTimer.timerState < 2) {
		window.setTimeout("sessTimer_checkLoginTimeout_timer();", sessTimer.sessTimerUpdate);
	}
}

// Initialize session timer
var sessTimer = new sessTimer();
sessTimer.loginRefreshed();
sessTimer_checkLoginTimeout_timer();