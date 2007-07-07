<?php
/**
 * Language file (EN)
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

/*
  * Mapping of versions:
  * 1 => all before RC1
  * 2 => RC1
  * 3 => RC2
  */

$this->LANG['en'] = array(
	'langName'					=>	'English',	// 1	Use language specific name. Not the english one.
	'langNameEn'					=>	'English',	// 3	English language name.
	'dateFormat'					=>	'Y m d',		// 1	Date format in strftime formating (USE " " (space) to split day, month, year!)
	'dateSplitter'					=>	'/',		// 1	Splitting of the day, month, year (replacement of the spaces)
	'timeFormat'					=>	'H M S',		// 1	Date format in strftime formating (USE " " (space) to split hour, minute, second!)
	'timeSplitter'					=>	':',		// 1	Splitting of the hour, minute, second (replacement of the spaces)

// Menu points
	'menuDomains'				=>	'Domains',	// 1
	'menuTemplates'				=>	'Templates',	// 1
	'menuGroups'					=>	'Groups',	// 1
	'menuUsers'					=>	'Users',	// 1
	'menuTools'					=>	'Tools',		// 3
	'menuLogging'				=>	'Log',		// 1
	'menuBackup'					=>	'Backup',	// 2
	'menuPreferences'				=>	'Preferences',	// 1
	'menuSystem'					=>	'System',	// 3
	'menuLogout'					=>	'Logout',	// 1

// Tab menues
	'tabPrefsGeneral'				=>	'General',	// 1
	'tabPrefsLogging'				=>	'Logging',	// 1
	'tabPrefsPersonal'				=>	'Personal',	// 1
	'tabPrefsPassword'				=>	'Password',	// 1
	'tabGroupGeneral'				=>	'General',	// 3
	'tabGroupLimits'				=>	'Limits',	// 3
	'tabUserPersonal'				=>	'Personal',	// 1
	'tabUserLimits'				=>	'Limits',	// 1
	'tabUserPermissions'				=>	'Permissions',	// 1
	'tabSysinfoSystem'				=>	'System',	// 2
	'tabSysinfoPdns'				=>	'PDNS',		// 2
	'tabBackupBackup'				=>	'Backup',	// 2
	'tabBackupRestore'				=>	'Restore',	// 2
	'tabBackupConfig'				=>	'Crontab config',	// 2
	'tabToolsIpChange'				=>	'Ip change',	// 3
	'tabSystemLangManager'			=>	'Languages',	// 3
	'tabSystemSkinManager'			=>	'Skins',		// 3
	'tabSystemConfig'				=>	'System configuration',	// 3


// Domains
	'showDomainsTitle'				=>	'Domains',									// 1
	'showDomainsButtonAdd'			=>	'Add new domain',								// 1
	'showDomainsButtonAddWithTemplate'	=>	'Add new with template',							// 1
	'showDomainsColId'				=>	'ID',										// 1
	'showDomainsColName'			=>	'Domain',									// 1
	'addDomainSingleTitle'			=>	'Add new domain',								// 1
	'addDomainWithTemplateTitle'		=>	'Add new domains with template',						// 1
	'addDomainButtonAdd'			=>	'Add domain',									// 1
	'editDomainTitle'				=>	'Modify domain',								// 1
	'editDomainButtonChange'			=>	'Save changes',								// 1
	'domainRecordsSubtitle'			=>	'Domain records',								// 1
	'addDomainSuccess'				=>	'The domain "%domainName%" created successfully.',			// 1
	'addDomainRecordsSuccess'			=>	'The records for the domain "%domainName%" created successfully.',	// 1
	'addDomainNoRecords'			=>	'No records enterd for this domain.',						// 1
	'editDomainSuccess'				=>	'The domain "%domainName%" updated successfully.',			// 1
	'editDomainRecordsSuccess'			=>	'The records for the domain "%domainName%" updated successfully.',	// 1
	'editDomainNoRecords'			=>	'No records enterd for this domain.',						// 1
	'selectTemplate'				=>	'< select template >',								// 1
	'deleteDomainJsConfirm'			=>	'Are you sure to delete the domain "%name%" and all records?',		// 1
	'deleteDomainsJsConfirm'			=>	'Are you sure to delete the selected domains (%count%) and all records?',	// 2
	'domainExampleRecords'			=>	'Example domain records',							// 1
	'moveDomainTitle'				=>	'Move domain "%domainName%"',						// 1
	'moveDomainsTitle'				=>	'Move %count% domains',							// 2
	'moveDomainButtonMove'			=>	'Move domain',								// 1

// Templates
	'showTemplatesTitle'				=>	'Templates',												// 1
	'showTemplatesButtonAdd'			=>	'Add new template',											// 1
	'showTemplatesColId'				=>	'ID',													// 1
	'showTemplatesColName'			=>	'Templatename',											// 1
	'addTemplateTitle'				=>	'Add new template',											// 1
	'addTemplateButtonAdd'			=>	'Add template',											// 1
	'editTemplateTitle'				=>	'Modify template',											// 1
	'editTemplateButtonChange'			=>	'Save changes',											// 1
	'templateRecordsSubtitle'			=>	'Template records (use %DOMAIN% as placeholder for the created domain in "Content" field)',	// 1
	'templateSoaSubtitle'				=>	'SOA record',												// 1
	'addTemplateSuccess'				=>	'The template "%templateName%" created successfully.',						// 1
	'addTemplateRecordsSuccess'		=>	'The records for the template "%templateName%" created successfully.',				// 1
	'addTemplateNoRecords'			=>	'No records enterd for this template.',								// 1
	'editTemplateSuccess'				=>	'The template "%templateName%" updated successfully.',						// 1
	'editTemplateRecordsSuccess'		=>	'The records for the template "%templateName%" updated successfully.',				// 1
	'editTemplateNoRecords'			=>	'No records enterd for this template.',								// 1
	'selectTemplateType'				=>	'< select type >',											// 1
	'deleteTemplateJsConfirm'			=>	'Are you sure to delete the template\n"%name%" and all records?',					// 1
	'deleteTemplatesJsConfirm'			=>	'Are you sure to delete the selected templates (%count%) and all records?',				// 2
	'moveTemplateTitle'				=>	'Move template "%templateName%"',									// 1
	'moveTemplatesTitle'				=>	'Move %count% templates',										// 2
	'moveTemplateButtonMove'			=>	'Move template',											// 1

// Groups
	'showGroupsTitle'				=>	'Groups',										// 1
	'showGroupsButtonAdd'			=>	'Add new group',									// 1
	'showGroupsColId'				=>	'ID',											// 1
	'showGroupsColName'			=>	'Groupname',										// 1
	'addGroupTitle'				=>	'Add new group',									// 1
	'addGroupButtonAdd'				=>	'Add group',										// 1
	'editGroupTitle'				=>	'Modify group',									// 1
	'editGroupButtonChange'			=>	'Save changes',									// 1
	'deleteGroupTitle'				=>	'Delete group "%groupName%"',							// 1	## CHANGED IN TUPA 0.1 RC1
	'deleteGroupsTitle'				=>	'Delete %count% groups',								// 2
	'deleteNoUTD'					=>	'No users, domains or templates in this group. So you can delete it directly.',	// 1
	'addGroupSuccess'				=>	'The group "%groupName%" created successfully.',					// 1
	'editGroupSuccess'				=>	'The group "%groupName%" updated successfully.',					// 1
	'groupLimitsSubtitle'				=>	'Group Limits ("0" => unlimited)',							// 1

// Users
	'showUsersTitle'				=>	'Users',										// 1
	'showUsersButtonAdd'			=>	'Add new user',									// 1
	'showUsersColId'				=>	'ID',											// 1
	'showUsersColUsername'			=>	'Username',										// 1
	'showUsersColName'				=>	'Name',											// 1
	'showUsersColFirstname'			=>	'Firstname',										// 1
	'addUserTitle'					=>	'Add new user',									// 1
	'addUserButtonAdd'				=>	'Add user',										// 1
	'addUserSuccess'				=>	'The user "%userName%" created successfully.',					// 1
	'editUserTitle'					=>	'Modify user "%userName%"',								// 1	## CHANGED IN TUPA 0.1 RC1
	'editUserButtonChange'			=>	'Save changes',									// 1
	'editUserSuccess'				=>	'The user "%userName%" updated successfully.',					// 1
	'deleteUserTitle'				=>	'Delete user "%userName%"',								// 1	## CHANGED IN TUPA 0.1 RC1
	'deleteUsersTitle'				=>	'Delete %count% users',								// 2
	'deleteNoTD'					=>	'No domains or templates owned by this user. So you can delete it directly.',	// 1
	'moveUserTitle'				=>	'Move user "%userName%"',								// 1
	'moveUsersTitle'				=>	'Move %count% users',									// 2
	'moveUserButtonMove'			=>	'Move user',										// 1
	'userPersonalSubtitle'				=>	'Users personal data',									// 1
	'userPermissionsAdminSubtitle'		=>	'Permissions - Administration',							// 1
	'userPermissionsUserSubtitle'		=>	'Permissions - Group users',								// 1
	'userPermissionsDomainSubtitle'		=>	'Permissions - Own domains',								// 1
	'userPermissionsDomainGroupSubtitle'	=>	'Permissions - Group domains',							// 1
	'userPermissionsTemplatesSubtitle'		=>	'Permissions - Own templates',							// 1
	'userPermissionsTemplatesGroupSubtitle'	=>	'Permissions - Group templates',							// 1
	'userLimitsSubtitle'				=>	'User Limits ("0" => unlimited)',							// 1

// Tools
	'toolsTitle'					=>	'Title',												// 3
	'toolsIpChangeSubtitle'			=>	'Global IP change',										// 3
	'toolsIpChangeButtonExec'			=>	'Execute IP change',										// 3
//	'toolsIpChangeOptGroupIpV4'		=>	'IPv4 addresses',										// 3
//	'toolsIpChangeOptGroupIpV6'		=>	'IPv6 addresses',										// 3
	'toolsIpChangeSuccess'			=>	'%recordsCount% record(s) successfully changed',						// 3
	'toolsIpChangeNothingChanged'		=>	'No records found which matchs your criteria.',						// 3

// System
	'systemLangInstalledSubtitle'			=>	'Installed languages',										// 3
	'systemLangAvailableSubtitle'			=>	'Available languages',										// 3
	'systemLangButtonGet'			=>	'Get list from server',										// 3
	'systemLangAvailableGetMsg'			=>	'Click on "%buttonName%" to get actual list of languages.',					// 3
	'systemDeleteSysLangJsConfirm'		=>	'Are you sure to delete the language "%name%" from server?',				// 3
	'systemLangDirNotExistsError'		=>	'The language directory (%dir%) does not exists.',						// 3
	'systemLangDirNotRemovableError'		=>	'The language directory (%dir%) can not be deleted. Normally a permission problem.',	// 3
	'systemLangDeleteFailed'			=>	'Failed to delete language (%item%) from server.',						// 3
	'systemLangDeleteSuccess'			=>	'Language (%item%) deleted successfully from server.',					// 3
	'systemLangDirAlreadyExistsError'		=>	'The directory (%dir%) of the language you want to install already exists. I try to remove it first.',	// 3
	'systemLangInstallFailedPrevious'		=>	'Installation of language (%item%) failed because of previous error(s)',			// 3
	'systemLangInstallCreateDirFailed'		=>	'Failed to create needed language directory (%dir%). Normally a permission problem.',	// 3
	'systemLangInstallSuccess'			=>	'Language (%item%) installed successfully.',							// 3
	'systemLangLanguageTitle'			=>	'Language',											// 3
	'systemLangLastChangeTitle'			=>	'Last change',											// 3
	'systemLangLastRemoteChangeTitle'		=>	'Last change on server',									// 3
	'systemLangCompVersionTitle'		=>	'Until version',											// 3
	'systemLangUpdatedNever'			=>	'never',												// 3
	'systemLangUpdateFailedPrevious'		=>	'Update of language (%item%) failed because of previous error(s)',				// 3
	'systemLangUpdateSuccess'			=>	'Language (%item%) updated successfully.',							// 3
	'systemSkinInstalledSubtitle'			=>	'Installed skins',										// 3
	'systemSkinAvailableSubtitle'			=>	'Available skins',										// 3
	'systemSkinButtonGet'				=>	'Get list from server',										// 3
	'systemSkinAvailableGetMsg'			=>	'Click on "%buttonName%" to get actual list of skins.',						// 3
	'systemDeleteSysSkinJsConfirm'		=>	'Are you sure to delete the skin "%name%" from server?',					// 3
	'systemSkinDirNotExistsError'		=>	'The skin directory (%dir%) does not exists.',							// 3
	'systemSkinDirNotRemovableError'		=>	'The skin directory (%dir%) can not be deleted. Normally a permission problem.',		// 3
	'systemSkinDeleteFailed'			=>	'Failed to delete skin (%item%) from server.',							// 3
	'systemSkinDeleteSuccess'			=>	'Skin (%item%) deleted successfully from server.',						// 3
	'systemSkinDirAlreadyExistsError'		=>	'The directory (%dir%) of the skin you want to install already exists. I try to remove it first.',	// 3
	'systemSkinInstallFailedPrevious'		=>	'Installation of skin (%item%) failed because of previous error(s)',				// 3
	'systemSkinInstallCreateDirFailed'		=>	'Failed to create needed skin directory (%dir%). Normally a permission problem.',		// 3
	'systemSkinInstallSuccess'			=>	'Skin (%item%) installed successfully.',								// 3
	'systemSkinSkinTitle'				=>	'Skin name',											// 3
	'systemSkinIdTitle'				=>	'Skin ID',											// 3
	'systemSkinLastChangeTitle'			=>	'Last change',											// 3
	'systemSkinLastRemoteChangeTitle'		=>	'Last change on server',									// 3
	'systemSkinUpdatedNever'			=>	'never',												// 3
	'systemSkinUpdateFailedPrevious'		=>	'Update of skin (%item%) failed because of previous error(s)',				// 3
	'systemSkinUpdateSuccess'			=>	'Skin (%item%) updated successfully.',								// 3
	'systemConfigButtonSave'			=>	'Save configuration.',										// 3


// Logging
	'logShowTitle'					=>	'Log messages',				// 1
	'logDate'					=>	'Date',						// 1
	'logFilterSubtitle'				=>	'Log filters',					// 1
	'logFilterSubmit'				=>	'Set filter',					// 1
	'selectMax'					=>	'&gt;&gt; select max items &lt;&lt;',		// 1
	'logRefreshNever'				=>	'&gt;&gt; never &lt;&lt;',			// 1
	// Columns
	'logColTime'					=>	'Time',						// 1
	'logColUser'					=>	'User',						// 1
	'logColPart'					=>	'Part',						// 1
	'logColAction'					=>	'Action',					// 1
	'logColType'					=>	'Type',						// 1
	'logColMessage'				=>	'Message',					// 1
	// Parts
	'selectPart'					=>	'&gt;&gt; select part &lt;&lt;',			// 1
	'logPartGeneral'				=>	'General',					// 1
	'logPartDomains'				=>	'Domains',					// 1
	'logPartTemplates'				=>	'Templates',					// 1
	'logPartUsers'					=>	'Users',					// 1
	'logPartGroups'				=>	'Groups',					// 1
	'logPartPrefs'					=>	'Preferences',					// 1
	'logPartAuth'					=>	'Authentication',				// 1
	'logPartBackup'				=>	'Backup',					// 2
	'logPartLangs'					=>	'Languages',					// 3
	'logPartSkins'					=>	'Skins',						// 3
	'logPartTools'					=>	'Tools',						// 3
	'logPartIpchange'				=>	'IP Change',					// 3
	'logPartSystemConfig'				=>	'System config',				// 3
	// Actions
	'selectAction'					=>	'&gt;&gt; select action &lt;&lt;',		// 1
	'logActionShow'				=>	'Show',						// 1
	'logActionAdd'				=>	'Add',						// 1
	'logActionEdit'					=>	'Edit',						// 1
	'logActionMove'				=>	'Move',						// 1
	'logActionDelete'				=>	'Delete',					// 1
	'logActionLogin'				=>	'Login',						// 1
	'logActionLogout'				=>	'Logout',					// 1
	'logActionGetConf'				=>	'Get configuration',				// 2
	'logActionDir'					=>	'Directory',					// 2
	'logActionFile'					=>	'File',						// 2
	'logActionFunction'				=>	'Function',					// 2
	'logActionExecute'				=>	'Execute',					// 2
	'logActionConnect'				=>	'Connect',					// 2
	'logActionStore'				=>	'Store',						// 2
	'logActionInstall'				=>	'Install',					// 3
	'logActionUpdate'				=>	'Update',					// 3
	//Types
	'selectType'					=>	'&gt;&gt; select type &lt;&lt;',			// 1
	'logTypeInfo'					=>	'Info',						// 1
	'logTypeWarning'				=>	'Warning',					// 2
	'logTypeError'					=>	'Error',						// 1
	'logTypeDb'					=>	'Database',					// 1
	'logTypePermission'				=>	'Permission',					// 1
	'logTypeSecurity'				=>	'Security',					// 1

	// Log messages
	'logMsgDbError'				=>	'Database error:<br />%mysqlError%',								// 1
	'logMsgNoPermission'				=>	'User tried to access page without permissions!',							// 1
	'logMsgGroupAdd'				=>	'Group "%groupName%" added.',									// 1
	'logMsgGroupEdit'				=>	'Group "%groupName%" updated.',									// 1
	'logMsgGroupDelete'				=>	'Group "%groupName%" deleted.',									// 1
	'logMsgUserAdd'				=>	'User "%userName%" added.',										// 1
	'logMsgUserEdit'				=>	'User "%userName%" updated.',									// 1
	'logMsgUserMove'				=>	'User %userName% moved to group "%groupName%".',						// 1
	'logMsgUserDelete'				=>	'User "%userName%" deleted.',										// 1
	'logMsgTemplateAdd'				=>	'Template "%templateName%" added.',									// 1
	'logMsgTemplateEdit'				=>	'Template "%templateName%" updated.',								// 1
	'logMsgTemplateMove'			=>	'Template "%templateName%" moved to "%userName%" (%groupName%).',				// 1
	'logMsgTemplateDelete'			=>	'Template "%templateName%" deleted.',								// 1
	'logMsgTemplateOwnerError'			=>	'User tried to add a template to user "%userName%".',						// 1
	'logMsgDomainAdd'				=>	'Domain "%domainName%" added.',									// 1
	'logMsgDomainEdit'				=>	'Domain "%domainName%" updated.',									// 1
	'logMsgDomainMove'				=>	'Domain %domainName% moved to user "%userName%" (%groupName%).',				// 1
	'logMsgDomainDelete'				=>	'Domain "%domainName%" deleted.',									// 1
	'logMsgDomainOwnerError'			=>	'User tried to add domain to user "%userName%".',							// 1
	'logMsgNoCookies'				=>	'User "%userName%" has cookies disabled.',								// 1
	'logMsgAuthNoHash'				=>	'An authentication hash could not be found on database for session of user "%userName%".',	// 1
	'logMsgHashTimeout'				=>	'The submitted authentication hash of user "%userName%" was to old.',				// 1
	'logMsgAuthIpNotMatch'			=>	'The IP of user "%userName%" does not match with the IP in database.',				// 1
	'logMsgWrongPassword'			=>	'User "%userName%" submitted a false password.',							// 1
	'logMsgWrongUserPassword'			=>	'The submitted username (%userName%) was no found in database.',				// 1
	'logMsgLoginSuccess'				=>	'User logged in succsessfully.',									// 1
	'logMsgLogoutSuccess'			=>	'User logged out succsessfully.',									// 1
	'logMsgSessionExpired'			=>	'User session has expired.',										// 1
	'logMsgNoBackupConfig'			=>	'No backup configuration found in database. Not configured yet?',					// 2
	'logMsgBackupConfigError'			=>	'More than one backup configuration found in database.',						// 2
	'logMsgBackupNoLocalDirConfError'		=>	'No local backup directory configured.',								// 2
	'logMsgBackupNoRemoteDirConfError'	=>	'No remote backup directory configured.',								// 2
	'logMsgBackupCreateLocalDirError'		=>	'The local backup directory (%dir%) could not be created.',						// 2
	'logMsgBackupCreateRemoteDirError'	=>	'The remote backup directory (%dir%) could not be created.',					// 2
	'logMsgBackupLocalDirNotWriteableError'	=>	'The local backup directory (%dir%) is not writeable.',							// 2
	'logMsgBackupDisabled'			=>	'Backup is disabled in configuration.',									// 2
	'logMsgBackupFtpConnectError'		=>	'Connection to FTP server failed. (%host%:%port%)',							// 2
	'logMsgBackupFtpLoginError'			=>	'Could connect to FTP server, but login failed.',							// 2
	'logMsgBackupWriteLocalError'		=>	'Could not write local backup file (%file%)',								// 2
	'logMsgBackupTransferRemoteError'		=>	'Could not transfer backup to remote server.',							// 2
	'logMsgBackupTransferLocalError'		=>	'Could not transfer backup to local server.',								// 2
	'logMsgBackupUploadAttackError'		=>	'Possible file upload attack. (%filename%)',								// 2
	'logMsgBackupFileNotExistsError'		=>	'The File to restore backup does not exists.',								// 2
	'logMsgBackupDecompressError'		=>	'Could not decompress backup file.',									// 2
	'logMsgBackupRestoreSuccess'		=>	'Database restored successfully.',									// 2
	'logMsgBackupRestoreFailedDefault'		=>	'Database restore returned an error: %errno%.',							// 2
	'logMsgBackupCalcNextExecError'		=>	'Calculation of next backup execution failed.',							// 2
	'logMsgBackupDone'				=>	'Backup successfully created. (crontab)',								// 2
	'logMsgFunctionNotFound'			=>	'The function "%function%" does not exists.',								// 2
	'logMsgConfigFileNotWritable'		=>	'The configuration file (%file%) has to be writable.',							// 3
	'logMsgConfigFileNotChanged'		=>	'The configuration file (%file%) has not changed.',							// 3
	'logMsgConfigFileNotChangedSimple'	=>	'The configuration file has not changed.',								// 3
	'logMsgConfigFileSaveSuccess'		=>	'Configuration saved sucessfully.',									// 3
	'logMsgSystemLangDirNotExistsError'	=>	'The language directory (%dir%) does not exists.',							// 3
	'logMsgSystemLangDirNotRemovableError'	=>	'The language directory (%dir%) can not be deleted.',							// 3
	'logMsgSystemLangDeleteFailed'		=>	'Failed to delete language (%item%) from server.',							// 3
	'logMsgSystemLangDeleteSuccess'		=>	'Language (%item%) deleted successfully from server.',						// 3
	'logMsgSystemLangInstallFailedPrevious'	=>	'Installation of language (%item%) failed because of previous error(s)',				// 3
	'logMsgSystemLangInstallCreateDirFailed'	=>	'Failed to create needed language directory (%dir%).',							// 3
	'logMsgSystemLangInstallSuccess'		=>	'Language (%item%) installed successfully.',								// 3
	'logMsgSystemLangUpdateFailedPrevious'	=>	'Update of language (%item%) failed because of previous error(s)',					// 3
	'logMsgSystemLangUpdateSuccess'		=>	'Language (%item%) updated successfully.',								// 3
	'logMsgSystemSkinDirNotExistsError'	=>	'The skin directory (%dir%) does not exists.',								// 3
	'logMsgSystemSkinDirNotRemovableError'	=>	'The skin directory (%dir%) can not be deleted.',							// 3
	'logMsgSystemSkinDeleteFailed'		=>	'Failed to delete skin (%item%) from server.',								// 3
	'logMsgSystemSkinDeleteSuccess'		=>	'Skin (%item%) deleted successfully from server.',							// 3
	'logMsgSystemSkinInstallFailedPrevious'	=>	'Installation of skin (%item%) failed because of previous error(s)',					// 3
	'logMsgSystemSkinInstallCreateDirFailed'	=>	'Failed to create needed skin directory (%dir%).',							// 3
	'logMsgSystemSkinInstallSuccess'		=>	'Skin (%item%) installed successfully.',									// 3
	'logMsgSystemSkinUpdateFailedPrevious'	=>	'Update of skin (%item%) failed because of previous error(s)',					// 3
	'logMsgSystemSkinUpdateSuccess'		=>	'Skin (%item%) updated successfully.',									// 3
	'logMsgToolsIpChangeSuccess'		=>	'Successfully changed %recordsCount% records from %srcIPs% to %dstIP%.',				// 3

// Preferences
	'prefsTitle'					=>	'Preferences of user %userName% (%firstName% %name%)',		// 1
	'prefsGeneralSubtitle'				=>	'General settings:',							// 1
	'prefsLoggingSubtitle'				=>	'Logging settings:',							// 1
	'prefsPersonalSubtitle'			=>	'Personal data:',							// 1
	'prefsPasswordSubtitle'			=>	'Change password:',							// 1
	'prefsButtonSave'				=>	'Save preferences',							// 1
	'prefsUpdateSuccess'				=>	'Your preferences were updated successfully.',			// 1
	'prefsUpdateReload'				=>	'You have to <a href="javascript:location.reload();">reload</a> the interface to load the new configuration.',	// 1
	'prefsLanguageSelect'				=>	'&gt;&gt; Select language &lt;&lt;',					// 1
	'prefsStartPageSelect'				=>	'&gt;&gt; select you start page &lt;&lt;',				// 1
	'prefsDisplayHelpDisabled'			=>	'Disabled',								// 2
	'prefsDisplayHelpLayer'			=>	'Layer (Mouseover)',							// 2
	'prefsDisplayHelpPopup'			=>	'Draggable layer (Click)',						// 2

// Backup
	'backupTitle'					=>	'Backup',									// 2
	'backupConfigSubtitle'			=>	'General backup configuration',						// 2
	'backupConfigLocalSubtitle'			=>	'Local backup configuration',							// 2
	'backupConfigRemoteSubtitle'		=>	'Remote backup configuration',						// 2
	'backupDumpOptionsSubtitle'		=>	'mysqldump options',								// 2
	'backupBackupSubtitle'			=>	'Backup database',								// 2
	'backupRestoreFileSubtitle'			=>	'Restore from file',								// 2
	'backupRestoreLocalSubtitle'			=>	'Restore from local backup',							// 2
	'backupRestoreRemoteSubtitle'		=>	'Restore from remote backup',						// 2
	'backupCompressionNone'			=>	'None',										// 2
	'backupCompressionGzip'			=>	'gzip',										// 2
	'backupCompressionBzip'			=>	'bzip2',										// 2
	'backupSaveBackupDisabled'			=>	'Disabled',									// 2
	'backupSaveBackupLocal'			=>	'Local',										// 2
	'backupSaveBackupRemote'			=>	'Remote',									// 2
	'backupSaveError'				=>	'Please select a correct value where you want to store your backups.',	// 2
	'backupExecuteNever'				=>	'never',										// 2
	'backupFrequencyError'			=>	'Please select a correct value how often you want to run the backup task.',	// 2
	'backupTimeError'				=>	'Please enter a correct time in the format hh:mm (24h).',			// 2
	'backupWeekdayError'				=>	'Please select a weekday from select box.',					// 2
	'backupDayError'				=>	'Please select a day from select box.',						// 2
	'backupButtonSave'				=>	'Save settings',									// 2
	'backupButtonBackup'				=>	'Run backup',									// 2
	'backupButtonRestore'			=>	'Run restore',									// 2
	'backupRestoreWarning'			=>	'<strong>Attention:</strong><br />A restore deletes ALL tables (also the ones from PDNS) before it restores the backup!',			// 2
	'backupConfigErrorTitle'			=>	'Configuration error!',								// 2
	'backupUpdateConfigSuccess'		=>	'Configuration saved successfully.',						// 2
	'backupDumpErrorTitle'			=>	'mysqldump error',								// 2
	'backupUnknownOptionError'		=>	'Unknown option in mysqldump command!',					// 2
	'backupFalseUsrPwdError'			=>	'False user or password while connecting to database!',			// 2
	'backupMysqldumpNotFoundError'		=>	'mysqldump command not found!',						// 2
	'backupStartDbDump1'			=>	'Starting dump of database "%DB%".<br /><br />(You can close this window AFTER downloading the dump file)',				// 2
	'backupStartDbDump2'			=>	'Starting dump of database "%DB%".',															// 2
	'backupNoBackupConfig'			=>	'No backup configuration found in database. Please configure the "Crontab config" first before start a backup/restore.',				// 2
	'backupBackupConfigError'			=>	'Found more than one backup configuration. This can (normally) not be. Please go to "Crontab config" and set your config again.',		// 2
	'backupDirectoryErrorTitle'			=>	'Directory error',																	// 2
	'backupNoLocalDirConfError'			=>	'No local backup directory configured. You have to configure a local directory to backup or restore .',						// 2
	'backupNoRemoteDirConfError'		=>	'No remote backup directory configured. You have to configure a directory if you want to store backups on a remote server.',			// 2
	'backupCreateLocalDirError'			=>	'The local backup directory (%dir%) could not be created. Normally a permission problem.',							// 2
	'backupCreateRemoteDirError'		=>	'The remote backup directory (%dir%) could not be created. Normally a permission problem.',							// 2
	'backupLocalDirNotWriteableError'		=>	'The local backup directory (%dir%) is not writeable by the webserver user.',										// 2
	'backupDisabledTitle'				=>	'Backup disabled',																	// 2
	'backupDisabled'				=>	'Backup is disabled in "Crontab config". Check the configuration if you want to use the "Crontab config" to backup the database.',		// 2
	'backupFunctionErrorTitle'			=>	'Function error',																	// 2
	'backupConnectErrorTitle'			=>	'Connection error',																	// 2
	'backupFtpConnectError'			=>	'Connection to FTP server (%host% port %port%) failed. Check your settings and server.',								// 2
	'backupSshConnectError'			=>	'Connection to SSH server (%host% port %port%) failed. Check your settings and server.',								// 2
	'backupSshFingerprintLoginError'		=>	'The given SSH fingerprint does not match the one in the configuration. This could be a "man in the middle" attack. Or the fingerprint just changed.',	// 2
	'backupSshLoginError'			=>	'Could connect to SSH server, but login failed.',													// 2
	'backupLoginErrorTitle'			=>	'Login error',																		// 2
	'backupFtpLoginError'			=>	'Could connect to FTP server, but login failed.',													// 2
	'backupWriteErrorTitle'			=>	'Write error',																		// 2
	'backupWriteLocalError'			=>	'Could not write local backup file (%file%) Normally a permission problem.',										// 2
	'backupTransferErrorTitle'			=>	'Transfer error',																	// 2
	'backupTransferRemoteError'			=>	'Could not transfer backup to remote server. Maybe a permission problem.',									// 2
	'backupTransferLocalError'			=>	'Error while transfering the backup file to local server.',												// 2
	'backupLocalAmountError'			=>	'Please enter a valid amount of backups you want to keep on your local server.',									// 2
	'backupLocalDaysError'			=>	'Please enter a valid number of days you want to keep backups on your local server.',								// 2
	'backupLocalSizeError'			=>	'Please enter a valid maximal size of your backup directory on your local server.',									// 2
	'backupRemoteAmountError'			=>	'Please enter a valid amount of backups you want to keep on your remote server.',									// 2
	'backupRemoteDaysError'			=>	'Please enter a valid number of days you want to keep backups on your remote server.',								// 2
	'backupRemoteSizeError'			=>	'Please enter a valid maximal size of your backup directory on your remote server.',								// 2
	'backupDoneTitle'				=>	'Backup Done',																		// 2
	'backupDone'					=>	'Backup sucessfully finished.<br />You can close this window now.',										// 2
	'backupSelectRestoreFile'			=>	'&gt;&gt; select the file to restore database from &lt;&lt;',												// 2
	'backupRestoreEitherorError'			=>	'One, and only one fileld has to be selected. Local, remote OR file.',											// 2
	'backupRestoreFileExtError'			=>	'The extension of the file has to be .sql, .sql.gz or .sql.bz2.',												// 2
	'backupUploadAttackError'			=>	'There was a problem with the file upload.',														// 2
	'backupFileNotExistsError'			=>	'The File to restore backup does not exists in backup directory.',											// 2
	'backupDecompressError'			=>	'Could not decompress backup file. Missing function or file permission problem.',									// 2
	'backupRestoreSuccess'			=>	'Database restored successfully.',															// 2
	'backupRestoreFailedDefault'			=>	'Database restore returned an error: %errno%<br />Database was not restored sucessfully!',							// 2
	'backupCronExecErrorSubject'		=>	'ERROR while execute backup task on "%hostname%"',												// 2
	'backupCronExecErrorMessage'		=>	'The following error(s) occured:%errors%',														// 2
	'backupCalcNextExecError'			=>	'Calculation of next backup execution failed. Backup was not executed.',										// 2

// System Information
	'sysPdnsRunning'				=>	'Running',								// 2
	'sysPdnsStopped'				=>	'!!! STOPPED !!!',						// 2
	'sysInfoNetInfoUnavailable'		=>	'No network information available',		// 3
	'sysInfoMemInfoUnavailable'		=>	'No memory information available',		// 3
	'sysInfoFsInfoUnavailable'		=>	'No filesystem information available',	// 3

// RRD Stuff (Titles / Lables / ...)
// ATTENTION!!
// RRD DOES NOT SUPPORT UTF-8 AT THE MOMENT. SO, ONLY USE LATIN CHARACTERS OR DELETE THIS PART TO USE THE ENGLISH TRANSLATIONS. NON-LATIN CHARS USED F.E. IN GREEK, JAPANESE, CHINESE OR RUSSIAN DON'T WORK!
	'rrdQueriesTitle'				=>	'udp/tcp queries',		// 2
	'rrdQueriesVertTitle'				=>	'queries / second',		// 2
	'rrdLatencyTitle'				=>	'Latency',			// 2
	'rrdLatencyVertTitle'				=>	'seconds',			// 2
	'rrdPeriodDay'					=>	'(last 24h)',			// 2
	'rrdPeriodWeek'				=>	'(1 week back)',		// 2
	'rrdPeriodMonth'				=>	'(1 month back)',		// 2
	'rrdPeriodYear'				=>	'(1 year back)',			// 2
	'rrdMin'					=>	'Min:',				// 2
	'rrdAverage'					=>	'Average:',			// 2
	'rrdMax'					=>	'Max:',				// 2
	'rrdGenError'					=>	'Could not generate chart!',	// 2

// Form labels
	'labelDeleteUser'				=>	'Delete user:',				// 2
	'labelDeleteUsers'				=>	'Delete users:',				// 1
	'labelMoveUsers'				=>	'Move users to:',			// 1
	'labelMoveUser'				=>	'Move user to:',			// 1
	'labelDeleteTemplate'				=>	'Delete template:',			// 2
	'labelDeleteTemplates'				=>	'Delete templates:',			// 1
	'labelMoveTemplates'				=>	'Move templates to:',			// 1
	'labelMoveTemplate'				=>	'Move template to:',			// 1
	'labelDeleteDomain'				=>	'Delete domain:',			// 2
	'labelDeleteDomains'				=>	'Delete domains:',			// 1
	'labelMoveDomains'				=>	'Move domains to:',			// 1
	'labelMoveDomain'				=>	'Move domain to:',			// 1
	'labelLanguage'				=>	'Language:',				// 1
	'labelSkin'					=>	'Skin:',					// 1
	'labelRowsPerSite'				=>	'Rows per site:',			// 1
	'labelNaviShowPages'				=>	'Number of pages in navigation:',	// 1
	'labelGroupname'				=>	'Groupname:',				// 1
	'labelGroup'					=>	'Group:',				// 1
	'labelTemplatename'				=>	'Templatename:',			// 1
	'labelUsername'				=>	'Username:',				// 1
	'labelUser'					=>	'User:',					// 1
	'labelName'					=>	'Last name:',				// 1
	'labelFirstname'				=>	'First name:',				// 1
	'labelHostname'				=>	'Hostname',				// 1
	'labelEmail'					=>	'e-Mail:',				// 1
	'labelNotice'					=>	'Notice:',				// 1
	'labelPassword'				=>	'Password:',				// 1
	'labelOldPassword'				=>	'Current password:',			// 1
	'labelNewPassword'				=>	'New password:',			// 1
	'labelConfirmPassword'			=>	'Confirm password:',			// 1
	'labelTTL'					=>	'TTL',					// 1
	'labelType'					=>	'Type',					// 1
	'labelPrio'					=>	'Prio',					// 1
	'labelContent'					=>	'Content',				// 1
	'labelDomain'					=>	'Domain:',				// 1
	'labelDomainOwner'				=>	'Domain owner:',			// 1
	'labelDomainTemplate'			=>	'Domain template:',			// 1
	'labelDomainList'				=>	'List of domains:',			// 1
	'labelTemplateOwner'				=>	'Template owner:',			// 1
	'labelSoaPrimary'				=>	'Default primary nameserver:',	// 1
	'labelSoaHostmaster'				=>	'Hostmaster eMail:',			// 1
	'labelSoaRefresh'				=>	'Refresh:',				// 1
	'labelSoaRetry'					=>	'Retry:',				// 1
	'labelSoaExpire'				=>	'Expire:',				// 1
	'labelSoaTtl'					=>	'TTL:',					// 1
	'labelPart'					=>	'Part:',					// 1
	'labelType'					=>	'Type:',					// 1
	'labelDate'					=>	'Date:',					// 1
	'labelLoggingMax'				=>	'Messages per site:',			// 1
	'labelLoggingMessage'			=>	'Search in message field:',		// 2
	'labelLoggingRefresh'				=>	'Refresh logs every:',			// 1
	'labelStartPage'				=>	'Prefered start page:',			// 1
	'labelHelpDisplay'				=>	'Display help:',				// 2
	'labelDisableTabs'				=>	'Disable Tabs (registers):',		// 1
	'labelPermAdmin'				=>	'Administrator:',			// 1
	'labelPermAdminUser'				=>	'User administrator:',			// 1
	'labelPermAdminDomain'			=>	'Domain administrator:',		// 1
	'labelPermAll'					=>	'All:',					// 1
	'labelPermShow'				=>	'Show:',				// 1
	'labelPermAdd'				=>	'Add:',					// 1
	'labelPermEdit'					=>	'Edit:',					// 1
	'labelPermDelete'				=>	'Delete:',				// 1
	'labelMaxUsers'				=>	'Maximal users:',			// 1
	'labelMaxDomains'				=>	'Maximal domains:',			// 1
	'labelMaxTemplates'				=>	'Maximal templates:',			// 1

	'labelSysHostname'				=>	'Hostname:',				// 2
	'labelSysIp'					=>	'IP:',					// 2
	'labelSysKernel'				=>	'Kernel:',				// 2
	'labelSysUptime'				=>	'Uptime:',				// 2
	'labelSysLoadavg'				=>	'Average load:',			// 2
	'labelSysNetwork'				=>	'Network:',				// 2
	'labelSysMemory'				=>	'Memory:',				// 2
	'labelSysFilesystem'				=>	'Filesystem:',				// 2
	'labelSysPdnsState'				=>	'PDNS-State:',				// 2
	'labelSysInterface'				=>	'Interface',				// 2
	'labelSysReceived'				=>	'Received',				// 2
	'labelSysSent'					=>	'Sent',					// 2
	'labelSysErrorDrop'				=>	'Errors / Dropped',			// 2
	'labelSysTotal'					=>	'Total',					// 2
	'labelSysUsed'					=>	'Used',					// 2
	'labelSysFree'					=>	'Free',					// 2
	'labelSysPercent'				=>	'Proportional use',			// 2
	'labelSysMount'				=>	'Mount point',				// 2
	'labelSysType'					=>	'Type',					// 2
	'labelSysPartition'				=>	'Partition',				// 2
	'labelNotificationEmail'			=>	'Notification eMail:',			// 2
	'labelCompression'				=>	'Compression:',			// 2
	'labelSavePlace'				=>	'Save backup:',				// 2
	'labelLocalPath'				=>	'Absolute path to store backup:',	// 2
	'labelProtocol'					=>	'Protocol:',				// 2
	'labelPassiveMode'				=>	'Use passive FTP mode:',		// 2
	'labelRemotePath'				=>	'Absolute path to store backup:',	// 2
	'labelHostPort'					=>	'Host/Port:',				// 2
	'labelUseBackupConfig'			=>	'Use crontab configuration:',		// 2
	'labelRestoreFile'				=>	'File to restore from: (Max. %maxSize%)',	// 2
	'labelRestoreLocal'				=>	'Local file to restore from:',		// 2
	'labelRestoreRemote'				=>	'Remote file to restore from:',		// 2
	'labelDumpOptionDbcreate'			=>	'Add create db in dump:',		// 2
	'labelDumpOptionCompleteinsert'		=>	'Use complete insert in dump:',	// 2
	'labelDumpOptionExtendedinsert'		=>	'Use extended insert in dump:',	// 2
	'labelBackupLocalAmount'			=>	'Hold x backups on local server:',	// 2
	'labelBackupLocalDays'			=>	'Delete backups older than x days:',	// 2
	'labelBackupLocalSize'				=>	'Maximal backup dir size:',		// 2
	'labelBackupRemoteAmount'			=>	'Hold x backups on remote server:',	// 2
	'labelBackupRemoteDays'			=>	'Delete backups older than x days:',	// 2
	'labelBackupRemoteSize'			=>	'Maximal backup dir size:',		// 2
	'labelBackupSchedule'				=>	'Execute backup task:',		// 2
	'labelSshFingerprint'				=>	'SSH fingerprint (HEX):',		// 2
	'labelChangeIpType'				=>	'Type of IP:',					// 3
	'labelChangeSrcIp'				=>	'Change the selected IP\'s:',			// 3
	'labelChangeDstIp'				=>	'To this IP:',					// 3
	'labelChangeOnlyFromGrpUsr'		=>	'But only from these group(s) and/or user(s):',	// 3

// Form fields errors
	'moveDeleteUsersError'			=>	'Please choose to delete users or move them to another group.',					// 1
	'moveDeleteTemplatesError'			=>	'Please choose to delete templates or move them to another user.',					// 1
	'moveDeleteDomainsError'			=>	'Please choose to delete domains or move them to another user.',					// 1
	'languageError'				=>	'Please select your prefered language.',								// 1
	'rowsPerSiteError'				=>	'Please enter the number of rows between %min% and %max% to be shown on one page.',		// 1
	'naviShowPagesError'				=>	'Please enter the number of pages between %min% and %max% to be shown on navigation.',	// 1
	'groupnameError'				=>	'Please enter a groupname.',										// 1
	'groupError'					=>	'Please select a group.',										// 1
	'groupnameDuplicateError'			=>	'There is already a group with the name "%groupName%"!',						// 1
	'usernameError'				=>	'Please enter an username.',										// 1
	'usernameDuplicateError'			=>	'There is already an user with the name "%userName%"!',						// 1
	'emailError'					=>	'Please enter a valid eMail address.',									// 1
	'urlError'					=>	'Please enter a valid URL.',										// 3
	'permissionProblemError'			=>	'There is an error with the sumitted permissions! User can not be created',				// 1
	'templatenameError'				=>	'Please enter a template name.',									// 1
	'templatenameDuplicateError'			=>	'There is already a template with the name "%templateName%" in your group!',			// 1
	'nameError'					=>	'Please enter a name.',											// 1
	'firstnameError'				=>	'Please enter a firstname.',										// 1
	'oldPasswordError'				=>	'Please enter you current password.',									// 1
	'passwordError'				=>	'The password must be at least 7 characters long.',							// 1
	'confirmPasswordError'			=>	'The new password an the confirmation are not identical.',						// 1
	'passwordFieldsError'				=>	'You have to enter all or none of the password fields.',						// 1
	'passwordCurrentError'			=>	'The current password is not correct.',								// 1
	'ipError'					=>	'Please enter a valid IP address.',									// 1
	'ipv6Error'					=>	'Please enter a valid IPv6 address.',									// 1
	'domainError'					=>	'Please enter a valid FQDN (Fully Qualified Domain Name).',						// 1
	'domainListError'				=>	'Please enter one valid FQDN (Fully Qualified Domain Name) each line and not more than %limit% domains. Make sure there are no empty lines!',		// 1	## CHANGED in TUPA 0.1RC1
	'hostError'					=>	'Please enter a valid hostname.',									// 1
	'hostIpError'					=>	'Please enter a valid hostname or IP address.',							// 2
	'portError'					=>	'Please enter a valid port number between 1 and 65535.',						// 2
	'ptrError'					=>	'Your PTR records is not correct',									// 1
	'ttlError'					=>	'Please enter a valid TTL.',										// 1
	'prioError'					=>	'Please enter a valid MX priority between 0 and 65535.',						// 1
	'prioEmptyError'				=>	'You set a priority where no priority is allowed.',							// 1
	'domainError'					=>	'Please enter a valid domain.',										// 1
	'domainDuplicateError'			=>	'The domain "%domainName%" already exists on this server!',					// 1
	'soaPrimaryError'				=>	'Please enter a valid nameserver.',									// 1
	'soaHostmasterError'				=>	'Please enter a valid hostmaster eMail address.',							// 1
	'soaRefreshError'				=>	'Please enter a SOA refresh time between %min% and %max% seconds.',				// 1
	'soaRetryError'				=>	'Please enter a SOA retry time between %min% and %max% seconds.',				// 1
	'soaExpireError'				=>	'Please enter a SOA expire time between %min% and %max% seconds.',				// 1
	'soaTtlError'					=>	'Please enter a SOA TTL time between %min% and %max% seconds.',					// 1
	'hinfoError'					=>	'Please enter a valid hardware information. (ex. "i386 Linux")',					// 2
	'rpError'					=>	'Please enter a valid responsible person. (ex. "my@email.com txt.record.com" or "." for info which is not present)',	// 2
	'srvNameError'				=>	'Please enter a valid SRV name. (ex. "_service._protocol.your.domain.com")',			// 2
	'srvContentError'				=>	'Please enter a valid SRV content. (ex. "123 456 your.domain.com")',				// 2
	'maxDomainsError'				=>	'Your entered domain limit exceeds the group limit (%groupLimit%) or is not a number.',		// 1
	'maxTemplatesError'				=>	'Your entered template limit exceeds the group limit (%groupLimit%) or is not a number.',		// 1
	'maxGroupUsersError'			=>	'Please enter a valid number for maximal users.',							// 1
	'maxGroupDomainsError'			=>	'Please enter a valid number for maximal domains.',							// 1
	'maxGroupTemplatesError'			=>	'Please enter a valid number for maximal templates.',						// 1
	'ownerSelectError'				=>	'Please select an owner of the record.',								// 1
	'domainTemplateError'			=>	'Please select a template to use.',									// 1
	'backupPathLocalError'			=>	'You have to enter a local backup path.',								// 2
	'backupPathRemoteError'			=>	'You have to enter a remote backup path.',								// 2
	'sshFingerprintError'				=>	'The hex SSH fingerprint has to be exactly 47 chars long.',						// 2
	'selectMin1IpError'				=>	'Please select at least one IP address.',								// 3
	'selectMin1AllGroupError'			=>	'Please select "all groups" or at least one group.',							// 3
	'selectMin1AllUserError'			=>	'Please select "all users" or at least one user.',							// 3
	'selectNoGrpUsrError'				=>	'Please select on which group(s) and/or user(s) you want to make the IP change.',			// 3
	'selectNoAllGrpError'				=>	'You can only select one option if "no groups" or "all groups" is selected.',				// 3
	'selectNoAllUsrError'				=>	'You can only select one option if "no users" or "all users" is selected.',				// 3
	'selectIpTypeError'				=>	'Please select the type of IP you want to change.',							// 3

// Delete/Move groups, users, templates, domains
	'deleteUsersSubtitle'				=>	'Users',										// 1
	'deleteTemplatesSubtitle'			=>	'Templates',										// 1
	'deleteDomainsSubtitle'			=>	'Domains',										// 1
	'selectGroup'					=>	'&gt;&gt; select group &lt;&lt;',							// 1
	'selectAllGroups'				=>	'&gt;&gt; all groups &lt;&lt;',								// 3
	'selectNoGroups'				=>	'&gt;&gt; no group(s) &lt;&lt;',								// 3
	'selectUser'					=>	'&gt;&gt; select user &lt;&lt;',								// 1
	'selectGroupFirst'				=>	'&gt;&gt; select group first &lt;&lt;',							// 1
	'selectNoUsers'				=>	'&gt;&gt; no users in group &lt;&lt;',							// 1
	'selectAllUsers'				=>	'&gt;&gt; all users &lt;&lt;',								// 3
	'selectNoUsers'				=>	'&gt;&gt; no user(s) &lt;&lt;',								// 3
	'deleteGroupButtonDelete'			=>	'Delete group',										// 1
	'deleteUserButtonDelete'			=>	'Delete user',										// 1
	'wrongDataError'				=>	'You submitted some nasty values!',							// 1
	'moveAndDeleteDomainsError'		=>	'It\'s not possible to move AND delete domains!',					// 1
	'moveAndDeleteTemplatesError'		=>	'It\'s not possible to move AND delete templates!',					// 1
	'moveAndDeleteUsersError'			=>	'It\'s not possible to move AND delete users!',					// 1
	'addDomainDbError'				=>	'Could not insert domain!',								// 1
	'addDomainRecordsDbError'			=>	'Could not insert domain records!',							// 1
	'addDomainRelationDbError'			=>	'Could not insert domain/owner relation!',						// 1
	'addDomainOwnerError'			=>	'Permission denied. Could not insert domain to selected owner. The record will be added under your own user.',		// 1
	'editDomainRelationDbError'			=>	'Could not change owner of record!',							// 1
	'deleteDomainRecordsDbError'		=>	'Could not delete domain records. Delete process aborted!',				// 1
	'deleteDomainsDbError'			=>	'Could not delete domains. Delete process aborted!',					// 1
	'deleteDomainRelationsDbError'		=>	'Could not delete domain/owner relations. Delete process aborted!',		// 1
	'deleteDomainsSuccess'			=>	'%count% domains deleted successfully.',						// 1
	'deleteDomainSuccess'			=>	'Domain "%name%" and all records deleted successfully.',				// 1
	'moveDomainsDbError'			=>	'Could not change domain owner. Delete process aborted!',				// 1
	'moveDomainDbError'				=>	'Could not change domain owner. Delete process aborted!',				// 1
	'moveDomainsSuccess'			=>	'%count% domains moved to user "%userName%" (%groupName%).',			// 1
	'moveDomainSuccess'				=>	'Domain %domainName% moved to user "%userName%" (%groupName%).',		// 1
	'deleteTemplateRecordsDbError'		=>	'Could not delete template records. Delete process aborted!',			// 1
	'addTemplateDbError'				=>	'Could not insert template!',								// 1
	'addTemplateRecordsDbError'		=>	'Could not insert template records!',							// 1
	'addTemplateOwnerError'			=>	'Permission denied. Could not insert template to selected owner. The rocord will be added under your own user.',	// 1
	'deleteTemplatesDbError'			=>	'Could not delete templates. Delete process aborted!',				// 1
	'deleteTemplatesSuccess'			=>	'%count% templates deleted successfully.',						// 1
	'deleteTemplateSuccess'			=>	'Template "%name%" and all records deleted successfully.',				// 1
	'moveTemplatesDbError'			=>	'Could not change template owner. Delete process aborted!',			// 1
	'moveTemplateDbError'			=>	'Could not change template owner. Delete process aborted!',			// 1
	'moveTemplatesSuccess'			=>	'%count% templates moved to user "%userName%" (%groupName%).',			// 1
	'moveTemplateSuccess'			=>	'Template %templateName% moved to user "%userName%" (%groupName%).',	// 1
	'deleteUserDbError'				=>	'Could not delete user. Delete process aborted!',					// 2
	'deleteUsersDbError'				=>	'Could not delete users. Delete process aborted!',					// 1
	'deleteUsersSuccess'				=>	'%count% users deleted successfully.',						// 1
	'deleteUserSuccess'				=>	'User "%userName%" deleted successfully.',						// 1
	'moveUsersDbError'				=>	'Could not change user group. Delete process aborted!',				// 1
	'moveUserDbError'				=>	'Could not move user. Delete process aborted!',					// 1
	'moveUsersSuccess'				=>	'%count% users moved to group "%groupName%".',					// 1
	'moveUserSuccess'				=>	'User "%userName%" moved to group "%groupName%".',				// 1
	'deleteGroupDbError'				=>	'Could not delete group!',								// 1
	'deleteGroupsDbError'			=>	'Could not delete groups!',								// 2
	'deleteGroupSuccess'				=>	'Group "%groupName%" deleted successfully.',					// 1
	'deleteGroupsSuccess'			=>	'%count% groups deleted successfully.',						// 2
	'deleteMissingValuesError'			=>	'There were to less values submitted. Delete process aborted!<br />Contact your administrator if this happens again.',	// 1
	'moveMissingValuesError'			=>	'There were to less values submitted. Moving aborted!<br />Contact your administrator if this happens again.',		// 1
	'markAllToDelete'				=>	'Delete all',										// 1
	'unMarkAllFromDelete'			=>	'Delete none',										// 1
	'usersLimitExceeded'				=>	'You exceeded your group limit. You can not add new users!',			// 1
	'domainsLimitExceeded'			=>	'You exceeded your group or user limit. You can not add new domains!',		// 1
	'templatesLimitExceeded'			=>	'You exceeded your group or user limit. You can not add new Templates!',		// 1

// Alt-Tags
	'altAddAfter'					=>	'Add new line after this',						// 1
	'altMoveUp'					=>	'Move line one up',							// 1
	'altMoveDown'					=>	'Move line one down',							// 1
	'altRemoveLine'				=>	'Remove line',								// 1
	'startBgLogo'					=>	'Background logo',							// 3
	'startLogoTop'					=>	'Top logo',								// 3
	'altInstall'					=>	'Install',								// 3
	'altUpdate'					=>	'Update',								// 3
	'altRemove'					=>	'Remove',								// 3

// Global buttons
	'buttonBackToList'				=>	'&lt;&lt; Back to list',							// 1
	'buttonSearch'					=>	'search',								// 1

// General errors
	'dbError'					=>	'A database error occoured:<br />%mysqlError%',			// 1
	'dbErrorExt'					=>	'A database error occoured: %message%<br />%mysqlError%',	// 1
	'noPermissionsTitle'				=>	'ACCESS DENIED!',							// 1
	'noPagePermissions'				=>	'You don\'t have permissions to access this page!',			// 1
	'noAccessPermissions'			=>	'You don\'t have permissions to access this function!',		// 1
	'functionNotFound'				=>	'The function "%function%" does not exists in you PHP installation. You may have to install/load the module.',	// 2
	'configFileNotWritable'			=>	'The configuration file (%file%) has to be writable by webserver user to perform this task.',				// 3
	'configFileNotChanged'			=>	'The configuration file (%file%) has not changed. This is maybe a problem.',						// 3
	'configFileNotChangedSimple'		=>	'The configuration file has not changed.',										// 3
	'configFileSaveSuccess'			=>	'Configuration saved sucessfully.',											// 3

// Login
	'loginErrorForm'				=>	'Please complete the form',						// 1
	'loginErrorUser'				=>	'Username or password wrong',					// 1
	'loginErrorCookie'				=>	'You have to enable cookies for this site!',				// 1
	'loginErrorIp'					=>	'Incorrect IP address',							// 1
	'loginErrorTimeout'				=>	'Timeout reached. Please enter again.',				// 1
	'loginErrorHashTimeout'			=>	'Security hash expired. Please reload page.',				// 1
	'loginUser'					=>	'Username:',								// 1
	'loginPass'					=>	'Password:',								// 1
	'loginButton'					=>	'Login',									// 1
	'loginCookies'					=>	'(Cookies have to be enabled!)',					// 1
	// Don't change / translate copyright notice please! Remove the line on other language files. It takes the english one automaticly.
	'loginCopyright'				=>	'Copyright &copy; 2005 <a href="http://www.whity.ch/" target="_blank">Urs Weiss</a> &amp; <a href="http://www.icrcom.com/" target="_blank">ICRCOM AG</a>. <a href="http://www.tupa-dns.org/" target="_blank">TUPA</a> comes with ABSOLUTELY NO WARRANTY; TUPA is <a href="http://www.gnu.org/licenses/gpl.html" target="_blank">free software</a>, and you are welcome to redistribute it under certain conditions; Obstructing the appearance of this notice is prohibited by law.',	// 1
	'sessionExpireWarning'			=>	'Attention:\nYou session expires in %seconds% seconds.\nLoad an other page to prevent this, or unsaved data will be lost!',	// 1
	'sessionExpired'				=>	'Your session has expired.\nYou are now redirected to the login page.',								// 1

// Browser check messages
	'broCheckWarning'				=>	'<strong>Warning: </strong>',								// 1
	'broCheckNotTested'				=>	'Your browser or browser version was never tested to work with this tool! You can test it and <a href="http://www.tupa-dns.org/Contact.9.0.html" target="_blank">tell us</a> the exact name and version of your browser and of course if it works or not.',		// 1	## CHANGED IN TUPA 0.1 RC1
	'broCheckMacIe'				=>	'IE on Mac does not work with this tool. Please use an actual browser',			// 1
	'broCheckKonq'				=>	'Konqueror browser is known to not work with this tool. Best you use <a href="http://www.mozilla.org" taget="_blank">Firefox</a>.',		// 1
	'broCheckOp7'				=>	'Opera version 7.x dont work with this tool. Please update to newest Opera version.',	// 1
	'broCheckSaf'					=>	'The Safari browser has problems when validating password fields.',			// 1

// Other stuff
	'sysinfoUpdateError'				=>	'Error: Can not update system informations on this server (%OS%)!',		// 2
	'pageLoaderLoading'				=>	'Loading...',									// 1
	'pageLoaderMsg'				=>	'Please wait till the page is completely loaded.',				// 1
	'maintenanceMsg'				=>	'Maintenance mode enabled. Only user "admin" can login.',			// 2
	'confirmLogout'				=>	'Are you sure to logout?',							// 1
	'error'						=>	'Error:',		// 2
	'success'					=>	'Successful:',		// 2
	'second'					=>	'second',		// 2
	'seconds'					=>	'seconds',		// 1
	'minute'					=>	'minute',		// 2
	'minutes'					=>	'minutes',		// 2
	'hour'						=>	'hour',			// 2
	'hours'						=>	'hours',		// 2
	'day'						=>	'day',			// 2
	'days'						=>	'days',			// 2
	'daily'						=>	'daily',			// 2
	'weekly'					=>	'weekly',		// 2
	'monthly'					=>	'monthly',		// 2
	'weekday0'					=>	'Sunday',		// 2
	'weekday1'					=>	'Monday',		// 2
	'weekday2'					=>	'Thuesday',		// 2
	'weekday3'					=>	'Wednesday',		// 2
	'weekday4'					=>	'Thursday',		// 2
	'weekday5'					=>	'Friday',		// 2
	'weekday6'					=>	'Saturday',		// 2
);

?>