<?php
/**
 * Language help file (DE)
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

$this->HELP['de-ch'] = array(

// Domains
	'helpDomainsDomain'				=>	'Domainname im Format "domain.tld" (ohne Host!)',
	'helpDomainsOwner'				=>	'Setzen eines anderen Benutzers für die Domain.',
	'helpDomainsTemplate'			=>	'Der Vorlagenname welche sie für neue Domains verwenden.',
	'helpDomainsDomainList'			=>	'Liste der Domains welche mit dem gewählten Template erstelt werden sollen. Ein Domain pro Linie.',

// SOA
	'helpSoaPrimary'				=>	'Der Standart-Namensserver wird normalerweise in den Sever-, Gruppen- oder Benutzer-Einstellungen konfiguriert. Sie können hier aber wenn nötig einen Anderen angeben.',
	'helpSoaPrimaryG'				=>	'Geben sie den ersten Namensserver im Format "host.domain.tld" an, wenn dieser vom der Server-Einstellung abweicht.',
	'helpSoaRefresh'				=>	'Wert in Sekunden. Intervall in dem ein Slave-Server die Einträge aktualisiert. Dieser Wert muss zwischen %min% und %max% liegen.',
	'helpSoaRetry'					=>	'Wert in Sekunden. Intervall in dem ein Slave-Server einen wiederholten Verbindungs-Versuch macht wenn der Master-Server nicht erreichbar war. Sollte tiefer als die SOA Erneuerung sein, und muss zwischen %min% und %max% liegen.',
	'helpSoaExpire'				=>	'Nach dieser Zeit in Sekunden, in welcher der Slave-Server den Master-Server nicht erreichen konnte, werden keine Anfragen für diese Domain mehr beantwortet. Muss grosser sein als die SOA Erneuerung, oder die Daten werden ungültig bevor sie aktualisiert werden. Muss zwischen %min% und %max% liegen.',
	'helpSoaTTL'					=>	'"Time To Live" in Sekunden. Erlaubt anderen Namensservern die Einträge für diese Zeit zu speichern. Muss zwischen %min% und %max% liegen.',

// Templates
	'helpTemplatesName'				=>	'Name der Vorlage. Duplikate sind in der selben Gruppe nicht möglich.',
	'helpTemplatesOwner'				=>	'Setzen eines anderen Besitzers für die Vorlage.',

// Groups
	'helpGroupsName'				=>	'Gruppenname. Muss eindeutig sein.',
	'helpGroupsMaxUsers'			=>	'Maximal erlaubte Anzahl Benutzer in dieser Gruppe. "0" für unlimitiert Benutzer.',
	'helpGroupsMaxDomains'			=>	'Maximal erlaubte Anzahl Domains in dieser Gruppe. "0" für unlimitiert Domains.',
	'helpGroupsMaxTemplates'			=>	'Maimale erlaubte Anzahl Vorlagen in dieser Gruppe. "0" für unlimitiert Vorlagen.',
	
// Users
	'helpUsersPassword'				=>	'Das Passwort muss mindestens 7 Zeichen lang sein.',
	'helpUsersMaxDomains'			=>	'Maximale Anzahl Domains die ein Benutzer erstellen kann. "0" für unlimitiert Domains.',
	'helpUsersMaxTemplates'			=>	'Maximale Anzahl Vorlagen die ein Benutzer erstellen kann. "0" für unlimitiert Vorlagen.',
	'helpUsersPermAdmin'			=>	'Der Benutzer hat vollen Zugriff auf alles (Wie der Benutzer "admin").',
	'helpUsersPermUserAdmin'			=>	'Erlaubt dem Benutzer alle Gruppen und Benutzer zu administrieren.',
	'helpUsersPermDomainAdmin'		=>	'Erlaubt dem Benutzer alle Domains und Vorlagen zu administrieren.',
	'helpUsersPermGroupUsers'			=>	'Konfiguration was dieser Benutzer mit anderen Benutzern machen kann welche in der gleichen Gruppe sind.',
	'helpUsersPermDomains'			=>	'Konfiguration was dieser Benutzer mit seinen eigenen Domains machen kann.',
	'helpUsersPermGroupDomains'		=>	'Konfiguration was dieser Benutzer mit Domains machen kann deren Benutzern in der selben Gruppe sind.',
	'helpUsersPermTemplates'			=>	'Konfiguration was dieser Benutzer mit seinen eigenen Vorlagen machen kann.',
	'helpUsersPermGroupTemplates'		=>	'Konfiguration was dieser Benutzer mit Vorlagen machen kann deren Benutzern in der selben Gruppe sind.',

// Logging

// Backup
	'helpBackupEmail'				=>	'Geben sie die eMail Adresse ein auf welcher sie bei Fehlern informiert werden.',
	'helpBackupCompression'			=>	'Das benutzte Kompressions-Verfahren.',
	'helpBackupSaveBackup'			=>	'Deaktivieren der Sicherung oder Angabe wo die Sicherung gespeichert wird (Lokal oder entfernt)',
	'helpBackupPathLocal'				=>	'Absoluter Pfad wo die Sicherungen lokal gespeichert werden. Sollte aus Sicherheitsgründen ausserhalb der Webseite sein. Dieser Pfad wird auch verwendet um Sicherungen zu speichern bevor sie übertragen werden.',
	'helpBackupProtocol'				=>	'Protokoll welches zur Übertragung der Sicherungen verwendet wird.',
	'helpBackupPassiveMode'			=>	'Aktiviert den passiven Modus bei FTP Übertragungen.',
	'helpBackupHostPort'				=>	'Hostnamen or IP Adresse des entfernten Servers. Port muss nur angegeben werden wenn nicht der Standartport verwendet wird.',
	'helpBackupUsername'			=>	'Benutzername zur Anmeldung auf dem entfernten Server.',
	'helpBackupPassword'				=>	'Passwort zur Anmeldung auf dem entfernten Server.',
	'helpBackupPathRemote'			=>	'Absoluter Pfad wo die Sicherungen auf dem enfernten Server gespeichert werden. Achtung wenn ein gesicherter FTP server verwendet wird. Das Grundverzeichnis des absoluten Pfades ist normalerweise nicht das Grundverzeichnis des Systems.',
	'helpBackupUseConfig'			=>	'Wählen sie diese Option wenn sie die Konfiguration der "Crontab Konfiguration" verwenden wollen anstatt die Sicherung auf den PC herunterzuladen.',
	'helpBackupRestoreFile'			=>	'Die Datei auf dem lokalen Rechner wovon die Datenbank wiederhergestellt werden soll.',
	'helpBackupRestoreLocal'			=>	'Die Datei auf dem lokalen Server wovon die Datenbank wiederhergestellt werden soll.',
	'helpBackupRestoreRemote'			=>	'Die Datei auf dem entfernten Server wovon die Datenbank wiederhergestellt werden soll.',
	'helpBackupDumpOptionDbcreate'		=>	'"CREATE DATABASE /*!32312 IF NOT EXISTS*/ db_name;" wird in der Sicherungsdatei eingefügt. <strong>Nur</strong> wenn die Datenbank-Sicherung vor dem erstellen der Datenbank erstellt wird.',
	'helpBackupDumpOptionCompleteinsert'	=>	'Option ob "complete inserts" in mysqldump verwendet werden sollen anstatt der kürzeren Variante. Diese Option benötigt viel Speicher und die Sicherungsdatei wird etwa 50% grösser als "normal".',
	'helpBackupDumpOptionExtendedinsert'	=>	'Option ob der neuere, viel schnellere INSERT Syntax verwendet werden soll. Die Datei wird etwa halb so gross wie "normal".',
	'helpBackupSshFingerprint'			=>	'SSH Fingerabdruck (fingerprint) des SSH Server im HEX Format (47 Zeichen): Beispiel:<br />00:11:22:33:44:55:66:77:88:99:aa:bb:cc:dd:ee:ff',
	'helpBackupRemoteAmount'			=>	'Anzahl Sicherungen die auf dem entfernten Server behalten werden.',
	'helpBackupRemoteDays'			=>	'Belasse Sicherungen für x Tage auf dem entfernten Server.',
	'helpBackupRemoteSize'			=>	'Maximale Grösse der entfernten Sicherungs-Verzeichnises.',
	'helpBackupLocalAmount'			=>	'Anzahl Sicherungen die auf dem lokalen Server behalten werden.',
	'helpBackupLocalDays'			=>	'Belasse Sicherungen für x Tage auf dem lokalen Server.',
	'helpBackupLocalSize'				=>	'Maximale Grösse der lokalen Sicherungs-Verzeichnises.',
	'helpBackupSchedule'				=>	'Zeitpunkt zu welchem die Sicherung ausgefürt werden soll.<br />Wenn der 31. in der monatlichen Konfiguration gewählt wird und der aktuelle Monat weniger als 31 Tage hat, wird die Sicherung immer am letzten Tag des Monats durchgeführt.',

// Preferences
	'helpPrefsStartPage'				=>	'Seite welche nach dem Login angezeigt werden soll. Normalerweise diejenige welche sie am meisten verwenden.',
	'helpPrefsDisplayHelp'				=>	'Auswahl ob, oder wie Hilfen angezeigt werden.',
	'helpPrefsSkin'					=>	'Oberflächen verändern das Aussehen.',
	'helpPrefsDisableTabs'			=>	'Seiten mit viel Inhalt werden normalerweise in mehrere Register aufgeteilt. Mit dieser Option können sie dies deaktivieren um alles auf einer Seite anzuzeigen.',
	'helpPrefsLinesPerSite'			=>	'Maximale Anzahl Zeilen die in der Auflistung angezeigt werden.',
	'helpPrefsNaviShowPages'			=>	'Anzahl Seiten die in der Seiten-Navigation angezeigt werden.',
	'helpPrefsDefaultSoaPimary'			=>	'Der Standart Namensserver im Format "host.domain.tld". Dieser wird also Standart bei neuen Domains oder Vorlagen eingefügt.',
	'helpPrefsItemAmount'			=>	'Anzahl Log-Nachrichten welche standartmässig pro Seite angezeigt werden.',
)

?>