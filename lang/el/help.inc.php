<?php
/**
 * Language help file (EL)
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

$this->HELP['el'] = array(

// Ζώνες
	'helpDomainsDomain'				=>	'Όνομα ζώνης με τη μορφή "domain.tld". (χωρίς το hostname)',
	'helpDomainsOwner'				=>	'Εδώ μπορείτε να ορίσετε έναν διαφορετικό ιδιοκτήτη για τη ζώνη.',
	'helpDomainsTemplate'			=>	'Το όνομα προτύπου που επιθυμείτε να χρησιμοποιηθεί για τις νέες ζώνες.',
	'helpDomainsDomainList'			=>	'Λίστα ζωνών που θα δημιουργηθούν βάση του επιλεγμένου προτύπου. Δηλώστε ένα όνομα ζώνης σε κάθε σειρά.',

// SOA
	'helpSoaPrimary'				=>	'Ο αρμόδιος διακομιστής ονομάτων ορίζεται ως προκαθορισμένος στις ρυθμίσεις του διακομιστή, της ομάδας ή του χρήστη. Ωστόσο μπορεί να οριστεί και συγκεκριμένα εδώ, εάν διαφέρει.',
	'helpSoaPrimaryG'				=>	'Ο προκαθορισμένος αρμόδιος διακομιστής ονομάτων για τις ζώνες αυτής της ομάδας, με τη μορφή "host.domain.tld" (εάν διαφέρει από αυτόν που έχει δηλωθεί στις ρυθμίσεις διακομιστή).',
	'helpSoaRefresh'				=>	'Τιμή σε δευτερόλεπτα. Διάστημα ανά το οποίο ένας δευτερεύον διακομιστής συγχρονίζει τις εγγραφές του. Η τιμή θα πρέπει να είναι από %min% εώς %max%.',
	'helpSoaRetry'					=>	'Τιμή σε δευτερόλεπτα. Διάστημα ανά το οποίο ένας δευτερεύον διακομιστής θα προσπαθεί να συνδεθεί στον κύριο, εάν ο κύριος δεν είναι προσπελάσιμος την ώρα Συγχρονισμού. Θα πρέπει να είναι μικρότερη από αυτήν του Συγχρονισμού και ανάμεσα σε %min% και %max%.',
	'helpSoaExpire'				=>	'Μετά από τόσα δευτερόλεπτα αδυναμίας ενός Δευτερεύοντος διακομιστή προσπέλασης του Κύριου για συγχρονισμό των εγγραφών, ο Δευτερεύων θα πάψει να εξυπηρετεί αιτήσεις για αυτή τη ζώνη. Το διάστημα θα πρέπει να είναι μεγαλύτερο από αυτό του Συγχρονισμού, διαφορετικά ο Δευτερεύον διακομιστής δε θα εξυπηρετεί αιτήσεις για διάστημα ίσο με τη διαφορά των δύο τιμών. Οι επιτρεπόμενες τιμές είναι από %min% εώς %max%.',
	'helpSoaTTL'					=>	'"Χρόνος Ζωής" σε δευτερόλεπτα. Υποδεικνύει στους μη αρμόδιους διακομιστές γενικής εξυπηρέτησης να κρατήσουν τοπικά αντίγραφο την απάντησης ενός ερωτήματος για τόσο διάστημα. Οι επιτρεπόμενες τιμές είναι από %min% έως %max%.',

// Πρότυπα
	'helpTemplatesName'				=>	'Το όνομα του προτύπου. Συνωνυμίας δεν επιτρέπονται εντός της ίδιας ομάδας.',
	'helpTemplatesOwner'				=>	'Εδώ μπορείτε να ορίσετε έναν διαφορετικό ιδιοκτήτη για τη ζώνη.',

// Ομάδες
	'helpGroupsName'				=>	'Το όνομα της ομάδας. Θα πρέπει να είναι μοναδικό.',
	'helpGroupsMaxUsers'			=>	'Μέγιστο πλήθος χρηστών που επιτρέπεται να είναι μέλη αυτής της ομάδας. Εάν οριστεί "0" τότε δεν υπάρχει περιορισμός.',
	'helpGroupsMaxDomains'			=>	'Μέγιστο πλήθος ζωνών που επιτρέπεται να ανήκουν σε αυτή την ομάδα. Εάν οριστεί "0" τότε δεν υπάρχει περιορισμός.',
	'helpGroupsMaxTemplates'			=>	'Μέγιστο πλήθος προτύπων που επιτρέπεται να ανήκουν σε αυτή την ομάδα. Εάν οριστεί "0" τότε δεν υπάρχει περιορισμός.',
	
// Χρήστες
	'helpUsersPassword'				=>	'Ο κωδικός θα πρέπει να έχει μήκος τουλάχιστον 7 χαρακτήρων.',
	'helpUsersMaxDomains'			=>	'Μέγιστο πλήθος ζωνών που επιτρέπεται να δημιουργήσει ένας χρήστης. Εάν οριστεί "0" τότε δεν υπάρχει περιορισμός.',
	'helpUsersMaxTemplates'			=>	'Μέγιστο πλήθος προτύπων που επιτρέπεται να δημιουργήσει ένας χρήστης. Εάν οριστεί "0" τότε δεν υπάρχει περιορισμός.',
	'helpUsersPermAdmin'			=>	'Ο χρήστης θα έχει πλήρη πρόσβαση στα πάντα, όπως και ο χρήστης "admin".',
	'helpUsersPermUserAdmin'			=>	'Ο χρήστης θα έχει πλήρη πρόσβαση σε όλους τους λογαριασμούς χρηστών και τις ομάδες.',
	'helpUsersPermDomainAdmin'		=>	'Ο χρήστης θα έχει πλήρη πρόσβαση σε όλες τις ζώνες και τα πρότυπα.',
	'helpUsersPermGroupUsers'			=>	'Δικαιώματα πρόσβασης του χρήστη σε λογαριασμούς χρηστών της ίδιας ομάδας.',
	'helpUsersPermDomains'			=>	'Δικαιώματα πρόσβασης του χρήστη σε ζώνες που του ανήκουν.',
	'helpUsersPermGroupDomains'		=>	'Δικαιώματα πρόσβασης του χρήστη σε ζώνες που ανήκουν σε χρήστες της ίδιας ομάδας.',
	'helpUsersPermTemplates'			=>	'Δικαιώματα πρόσβασης του χρήστη σε πρότυπα που του ανήκουν.',
	'helpUsersPermGroupTemplates'		=>	'Δικαιώματα πρόσβασης του χρήστη σε πρότυπα που ανήκουν σε χρήστες της ίδιας ομάδας.',

// Αρχείο Καταγραφής

// Αντίγραφα Ασφαλείας
	'helpBackupEmail'				=>	'Δηλώστε την διεύθυνση E-Mail όπου θέλετε να ενημερώνεστε για σφάλματα.',
	'helpBackupCompression'			=>	'Επιλέξτε την συμπίεση του αντίγραφου ασφαλείας.',
	'helpBackupSaveBackup'			=>	'Απενεργοποιήστε τη λήψη του αντίγραφου ασφαλείας ή επιλέξτε που θέλετε να αποθηκεύεται (Τοπικά ή Απομακρυσμένα)',
	'helpBackupPathLocal'				=>	'Απόλυτη διαδρομή για την τοπική αποθήκευση αντίγραφου ασφαλείας. Θα πρέπει να μην είναι προσπελάσιμη μέσω HTTP. Εδώ επίσης θα αποθηκεύονται προσωρινά τα αντίγραφα ασφαλείας πριν μεταφερθούν σε απομακρυσμένη τοποθεσία.',
	'helpBackupProtocol'				=>	'Επιλέξετε το πρωτόκολλο που θα χρησιμοποιηθεί για τη μεταφορά των αντιγράφων ασφαλείας σε απομακρυσμένη τοποθεσία.',
	'helpBackupPassiveMode'			=>	'Επιλέξτε εάν θέλετε να χρησιμοποιηθεί FTP (με παθητική σύνδεση) για την μεταφορά των αντιγράφων ασφαλείας.',
	'helpBackupHostPort'				=>	'Διεύθυνση DNS ή IP του απομακρυσμένου διακομιστή. Η θύρα είναι απαραίτητη μόνο εάν δεν είναι η προκαθορισμένη του συγκεκριμένου προτοκόλλου.',
	'helpBackupUsername'			=>	'Ψευδώνυμο για σύνδεση στον απομακρυσμένο διακομιστή.',
	'helpBackupPassword'				=>	'Κωδικός για σύνδεση στον απομακρυσμένο διακομιστή.',
	'helpBackupPathRemote'			=>	'Απόλυτη διαδρομή αποθήκευσης αντιγράφων ασφαλείας στον απομακρυσμένο διακομιστή. Προσοχή στην περίπτωση χρήσης FTP με ενεργοποιημένο chroot, καθώς η ρίζα μέσω FTP δεν συμπίπτει με αυτήν του συστήματος αρχείον στο δίσκο.',
	'helpBackupUseConfig'			=>	'Επιλέξτε εάν θέλετε να χρησιμοποιηθούν οι ρυθμίσεις Crontab αντί να σταλεί το αντίγραφο ασφαλείας στο πρόγραμμα πελάτη σας.',
	'helpBackupRestoreFile'			=>	'Το αρχείο στο σύστημά-πελάτη σας το οποίο περιέχει το αντίγραφο ασφαλείας που θέλετε να επαναφέρετε.',
	'helpBackupRestoreLocal'			=>	'Το αρχείο στον τοπικό διακομιστή το οποίο περιέχει το αντίγραφο ασφαλείας που θέλετε να επαναφέρετε.',
	'helpBackupRestoreRemote'			=>	'Το αρχείο στον απομακρυσμένο διακομιστή το οποίο περιέχει το αντίγραφο ασφαλείας που θέλετε να επαναφέρετε.',
	'helpBackupDumpOptionDbcreate'		=>	'"CREATE DATABASE /*!32312 IF NOT EXISTS*/ db_name;" θα τοποθετηθεί στο αντίγραφο ασφαλείας. <strong>Μόνο</strong> αν θέλετε να επαναφέρετε το αντίγραφο ασφαλείας πριν δημιουργήσετε τη βάση.',
	'helpBackupDumpOptionCompleteinsert'	=>	'Επιλέξτε εάν θέλετε να χρησιμοποιηθούν δηλώσεις εισαγωγής πλήρους μήκους στο αντίγραφο ασφαλείας. Αυξάνει το κόστος μνήμης κατά τη λήψη του αντιγράφου και παράγει αρχείο ~50% μεγαλύτερου μεγέθους.',
	'helpBackupDumpOptionExtendedinsert'	=>	'Επιτρέπει τη χρήση της νέας, ταχύτερης σύνταξης Εισαγωγής. Το παραγόμενο αρχείο θα είναι ~50% μικρότερο.',
	'helpBackupSshFingerprint'			=>	'Η "σφραγίδα" SSH στον διακομιστή SSH, σε εξαδική μορφή (47 χαρακτήρες): Για παράδειγμα:<br />00:11:22:33:44:55:66:77:88:99:aa:bb:cc:dd:ee:ff',
	'helpBackupRemoteAmount'			=>	'Πόσα παλαιά αντίγραφα ασφαλείας θα παραμένουν στον απομακρυσμένο διακομιστή.',
	'helpBackupRemoteDays'			=>	'Αποθήκευση αντιγράφων για x ημέρες στον απομακρυσμένο διακομιστή.',
	'helpBackupRemoteSize'			=>	'Μέγιστο μέγεθος του απομακρυσμένου καταλόγου αποθήκευσης των αντιγράφων.',
	'helpBackupLocalAmount'			=>	'Πόσα παλαιά αντίγραφα ασφαλείας θα παραμένουν στον τοπικό διακομιστή.',
	'helpBackupLocalDays'			=>	'Αποθήκευση αντιγράφων για x ημέρες στον τοπικό διακομιστή.',
	'helpBackupLocalSize'				=>	'Μέγιστο μέγεθος του τοπικού καταλόγου αποθήκευσης των αντιγράφων.',
	'helpBackupSchedule'				=>	'Ορίστε κάθε πότε θα εκτελείτε η διεργασία λήψης αντίγραφου ασφαλείας μέσω του crontab.<br />Αν επιλέξετε την 31η ημέρα σε μηνιαία βάση και ο μήνας έχει λιγότερες από 31 ημέρες, θα εκτελείται πάντα την τελευταία ημέρα του μήνα.',

// Προτιμήσεις
	'helpPrefsStartPage'				=>	'Επιλέξτε ποια σελίδα θα εμφανίζεται μετά την σύνδεση -λογικά αυτή που χρησιμοποιείται συχνότερα.',
	'helpPrefsDisplayHelp'				=>	'Επιλέξετε εάν και πως θα εμφανίζονται μηνύματα βοηθείας.',
	'helpPrefsSkin'					=>	'Τα Στυλ καθορίζουν την εμφάνιση της εφαρμογής.',
	'helpPrefsDisableTabs'			=>	'Οι σελίδες με πολλά αντικείμενα συνήθως χωρίζονται σε καρτέλες. Με αυτή την επιλογή μπορείτε να απενεργοποιήσετε την επιλογή ώστε νε εμφανίζονται όλα σε μία σελίδα.',
	'helpPrefsLinesPerSite'			=>	'Αριθμός αντικειμένων που θα εμφανίζονται σε κάθε σελίδα στις λίστες.',
	'helpPrefsNaviShowPages'			=>	'Αριθμός σελίδων που θα εμφανίζονται για πλοήγηση στις λίστες.',
	'helpPrefsDefaultSoaPimary'			=>	'Ορίστε τον Κύριο διακομιστή DNS με τη μορφή "host.domain.tld". Αυτό εισάγεται ως προεπιλεγμένος όταν δημιουργείται μία νέα ζώνη ή πρότυπο.',
	'helpPrefsItemAmount'			=>	'Το πλήθος των καταγεγραμμένων μηνυμάτων που εμφανίζονται εκ προεπιλογής.',
)

?>