<?php
/**
 * JavaScript which handeles the remote php calls.
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

// *****************************
// Include JPSpan library
// *****************************
require_once(PATH_lib.'JPSpan.php');

// *****************************
// Additional JPSpan libraries
// *****************************
require_once(JPSPAN . 'Server/PostOffice.php');

// *******************************
// Include the classes for JPSpan
// *******************************
require_once(PATH_lib.'class.tupa_general.php');
require_once(PATH_lib.'class.tupa_groups.php');
require_once(PATH_lib.'class.tupa_users.php');
require_once(PATH_lib.'class.tupa_preferences.php');
require_once(PATH_lib.'class.tupa_templates.php');
require_once(PATH_lib.'class.tupa_domains.php');
require_once(PATH_lib.'class.tupa_tools.php');
require_once(PATH_lib.'class.tupa_logging.php');
require_once(PATH_lib.'class.tupa_sysinfo.php');
require_once(PATH_lib.'class.tupa_system.php');
require_once(PATH_lib.'class.tupa_backup.php');
// obsolete require_once(PATH_lib.'class.form.php');




// Create the PostOffice server
$S = & new JPSpan_Server_PostOffice();

// Register your class with it...
$S->addHandler(new tupa_general());
$S->addHandler(new tupa_groups());
$S->addHandler(new tupa_users());
$S->addHandler(new tupa_templates());
$S->addHandler(new tupa_domains());
$S->addHandler(new tupa_tools());
$S->addHandler(new tupa_logging());
$S->addHandler(new tupa_sysinfo());
$S->addHandler(new tupa_system());
$S->addHandler(new tupa_backup());
//$S->addHandler(new lib_form());

// This allows the JavaScript to be seen by
// just adding ?client to the end of the
// server's URL

if (isset($_SERVER['QUERY_STRING']) &&
	strcasecmp($_SERVER['QUERY_STRING'], 'client')==0) {

	// Compress the output Javascript (e.g. strip whitespace)
	define('JPSPAN_INCLUDE_COMPRESS',TRUE);

	// Display the Javascript client
	$S->displayClient();
} else {
	// This is where the real serving happens...
	// Include error handler
	// PHP errors, warnings and notices serialized to JS
	require_once JPSPAN . 'ErrorHandler.php';

	// Start serving requests...
	$S->serve();
}
?>