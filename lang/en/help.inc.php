<?php
/**
 * Language help file (EN)
 *
 * @package 	TUPA
 * @author 	Urs Weiss <urs@tupa-dns.org>
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

/**
 *  NOTICE FOR TRANSLATORS
 * The pages are all UTF-8 encoded. So, you can enter language specific characters directly without encode them when translating.
 * But make sure your editor supports saving as utf-8!!
**/

$this->HELP['en'] = array(

// Domains
	'helpDomainsDomain'				=>	'Domain name in the format "domain.tld". (Without host!)',					// 2
	'helpDomainsOwner'				=>	'Here you can set an other owner for the domain.',						// 2
	'helpDomainsTemplate'			=>	'The template name you want to use for the new domain(s).',				// 2
	'helpDomainsDomainList'			=>	'List of domains to create with the given template. Write one domain on each line.',		// 2

// SOA
	'helpSoaPrimary'				=>	'The default nameserver is normaly set in server, group or user preferences. But you can set it to an other here.',					// 2
	'helpSoaPrimaryG'				=>	'Insert the primary namserver in the format "host.domain.tld" for this group if it is different from the server setting.',					// 2
	'helpSoaRefresh'				=>	'Value in seconds. Interval in which a slave server updates the records. This value must be between %min% and %max%.',					// 2
	'helpSoaRetry'					=>	'Value in seconds. Interval in which a slave server retries to connect to the master if the master could no be reached after the SOA refresh. Should be lower than the SOA refresh time and has to be between %min% and %max%.',								// 2
	'helpSoaExpire'				=>	'After this time seconds, in which a slave server could not reach the master server, it does not answer queries for ths domain anymore. Must be greater than the SOA refresh, or your data on the slave expieres before they are updated. Has to be between %min% and %max%.',	// 2
	'helpSoaTTL'					=>	'"Time To Live" in seconds. Allows other nameserver to cache the records for this time. Has to be between %min% and %max%.',				// 2

// Templates
	'helpTemplatesName'				=>	'Name of your template. Duplicates are not possible in same group.',			// 2
	'helpTemplatesOwner'				=>	'Here you can set an other owner for the template.',						// 2

// Groups
	'helpGroupsName'				=>	'Name of group. Has to be unique.',									// 2
	'helpGroupsMaxUsers'			=>	'Maximum number of users allowd in this group. Set this to "0" for unlimitted users.',		// 2
	'helpGroupsMaxDomains'			=>	'Maximum number of domains allowd in this group. Set this to "0" for unlimitted domains.',	// 2
	'helpGroupsMaxTemplates'			=>	'Maximum number of templates allowd in this group. Set this to "0" for unlimitted templates.',	// 2

// Users
	'helpUsersPassword'				=>	'The password has to be at least 7 characters long.',									// 2
	'helpUsersMaxDomains'			=>	'Maximum number of domains the user can create. Set this to "0" for unlimitted domains.',				// 2
	'helpUsersMaxTemplates'			=>	'Maximum number of templates the user can create. Set this to "0" for unlimitted templates.',			// 2
	'helpUsersPermAdmin'			=>	'The user will have full access to everything. Same right as user "admin". It has also rights to see the statistics.',	// 2
	'helpUsersPermUserAdmin'			=>	'Allows to administrate all groups and users on server.',								// 2
	'helpUsersPermDomainAdmin'		=>	'Allows to administrate all domains and templates on server.',							// 2
	'helpUsersPermGroupUsers'			=>	'What this user is able to do with the users which are in his own group.',						// 2
	'helpUsersPermDomains'			=>	'What this user is able to do with domains he is owner of.',								// 2
	'helpUsersPermGroupDomains'		=>	'What this user is able to do with domains of other users in same group.',						// 2
	'helpUsersPermTemplates'			=>	'What this user is able to do with templates he is owner of.',								// 2
	'helpUsersPermGroupTemplates'		=>	'What this user is able to do with templates of other users in same group.',						// 2

// Logging

// Backup
	'helpBackupEmail'				=>	'Enter the eMail address where you want to be notified on errors.',					// 2
	'helpBackupCompression'			=>	'Choose the compression of your backup.',								// 2
	'helpBackupSaveBackup'			=>	'Disable backup or choose where to store it (Local or Remote)',					// 2
	'helpBackupPathLocal'				=>	'Absolute path to store local backups. Should be somwhere outside the webroot. This is also needed to store remote backups in before transfering them.',		// 2
	'helpBackupProtocol'				=>	'Choose over which protocol you want to transfer your backups.',					// 2
	'helpBackupPassiveMode'			=>	'Check it if you want to use passive FTP mode to transfer your backups.',				// 2
	'helpBackupHostPort'				=>	'Hostname or IP address of remote server. Port is only needed if not default protocol port.',	// 2
	'helpBackupUsername'			=>	'Username to login on remote server.',								// 2
	'helpBackupPassword'				=>	'Password to login on remote server.',								// 2
	'helpBackupPathRemote'			=>	'Absolute path to store backup on remote server. Take care if you have a secured ftp server. The root of the absolute path is normally not the system root.',		// 2
	'helpBackupUseConfig'			=>	'Check this option if you want to use the configuration from "Crontab config" instead of download the backup to your client.',						// 2
	'helpBackupRestoreFile'			=>	'The file on your client machine to restore the database from.',					// 2
	'helpBackupRestoreLocal'			=>	'The file on your local server backup directory to restore the database from.',			// 2
	'helpBackupRestoreRemote'			=>	'The file on your remote server backup directory to restore the database from.',			// 2
	'helpBackupDumpOptionDbcreate'		=>	'"CREATE DATABASE /*!32312 IF NOT EXISTS*/ db_name;" will be put in the dump. <strong>Only</strong> if you want to restore the dump before creating the databse istself.',					// 2
	'helpBackupDumpOptionCompleteinsert'	=>	'If you want to use complete insert statements in your dump file instead of the shorter one. A lot of memory is used and the generated dump file is around 50% bigger than "normal" if you check this option.',	// 2
	'helpBackupDumpOptionExtendedinsert'	=>	'Allows utilization of the new, much faster INSERT syntax. Generates files which are half in size than "normal".',					// 2
	'helpBackupSshFingerprint'			=>	'SSH fingerprint of the SSH server in HEX format (47 chars): Example:<br />00:11:22:33:44:55:66:77:88:99:aa:bb:cc:dd:ee:ff',			// 2
	'helpBackupRemoteAmount'			=>	'How many backups to leave on remote server.',							// 2
	'helpBackupRemoteDays'			=>	'Hold backups for x days on remote server.',								// 2
	'helpBackupRemoteSize'			=>	'Maximum size of remote backup directory.',								// 2
	'helpBackupLocalAmount'			=>	'How many backups to leave on local server.',							// 2
	'helpBackupLocalDays'			=>	'Hold backups for x days on local server.',								// 2
	'helpBackupLocalSize'				=>	'Maximum size of local backup directory.',								// 2
	'helpBackupSchedule'				=>	'Select when the backup task should be executed from crontab.<br />If you choose the 31th in monthly configuration and the month has less than 31 days, it is always executed at the last day of month.',	// 2

// Preferences
	'helpPrefsStartPage'				=>	'Choose which page to show after you logged in. Normaly the one you need the most.',	// 2
	'helpPrefsDisplayHelp'				=>	'Choose if and how help messages are displayed.',						// 2
	'helpPrefsSkin'					=>	'Skins changes the look of your adinistration frontend.',					// 2
	'helpPrefsLinesPerSite'			=>	'Maximal number of lines shown on a page in list view.',					// 2
	'helpPrefsNaviShowPages'			=>	'Number of pages shown on the navigation in list view.',					// 2
	'helpPrefsDefaultSoaPimary'			=>	'Enter your prefered primary nameserver in the format "host.domain.tld" here. This is inserted as default when creating a new domain or template.',			// 2
	'helpPrefsItemAmount'			=>	'Amount of log messages are shown per default.',						// 2

// Tools
	'helpToolsIpChangeSrcIp'			=>	'Select all source IP\'s you want to change into an other single IP address (for multiple selection hold "Ctrl"-Key).',																// 3
	'helpToolsIpChangeDstIp'			=>	'Enter the IP address to which the above selected IP\'s should change to.',																							// 3
	'helpToolsIpChangeGrpUsr'			=>	'Select the group(s) and/or user(s) the IP-change should affect to.',																							// 3

// System
	'helpSystemLangUntilVersionInstall'		=>	'Shows the version which is fully compatible with this language (all needed is translated). You can install a language which has a lower version, but missing translations of the language are shown in the default language (English).',	// 3
	'helpSystemLangUntilVersion'			=>	'Shows the version which is fully compatible with this language (all needed is translated). If the version is lower than your running version, missing translations are shown in default language (English).',					// 3
)

?>