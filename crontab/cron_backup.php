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
 * Script to dump mysql database
 *
 * @package 	TUPA
 * @author	Urs Weiss <urs@tupa-dns.org>
 */

if ($_SERVER['REMOTE_ADDR']) {
	die('It\'s not allowed to call this script from a remote host!');
}

// Include needed files and start session
require('../init.php');
// Include backup class
require_once('../lib/class.backup.php');


$BACKUP = lib_div::makeInstance('backup');
$BACKUP->cronExec = true;
$BACKUP->runBackup();

?>