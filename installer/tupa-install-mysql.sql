-- MySQL dump 9.11
--
-- Host: localhost    Database: pdns
-- ------------------------------------------------------
-- Server version	4.0.23_Debian-3ubuntu2.1-log

--
-- Table structure for table `authentication`
--

CREATE TABLE `authentication` (
  `tstamp` int(11) NOT NULL default '0',
  `sessionid` varchar(32) NOT NULL default '0',
  `ip` varchar(46) NOT NULL default '',
  `hash` varchar(32) NOT NULL default '0',
  PRIMARY KEY  (`sessionid`)
) ENGINE=MyISAM;

--
-- Table structure for table `domain_owners`
--

CREATE TABLE `domain_owners` (
  `dom_id` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  KEY `dom_id` (`dom_id`,`usr_id`)
) ENGINE=MyISAM;

--
-- Table structure for table `domains`
--

CREATE TABLE `domains` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `master` varchar(20) default NULL,
  `last_check` int(11) default NULL,
  `type` varchar(6) NOT NULL default '',
  `notified_serial` int(11) default NULL,
  `account` varchar(40) default NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name_index` (`name`)
) ENGINE=InnoDB;

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(30) NOT NULL default '',
  `notice` text,
  `max_users` int(11) NOT NULL default '0',
  `max_domains` int(11) NOT NULL default '0',
  `max_templates` int(11) NOT NULL default '0',
  `preferences` text,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `logging`
--

CREATE TABLE `logging` (
  `id` mediumint(11) unsigned NOT NULL auto_increment,
  `tstamp` int(11) NOT NULL default '0',
  `usr_id` int(11) NOT NULL default '0',
  `part` varchar(255) NOT NULL default '',
  `action` varchar(255) NOT NULL default '',
  `type` varchar(255) NOT NULL default '',
  `message` varchar(255) NOT NULL default '',
  `message_repl` text,
  `ip` varchar(46) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `history`
--

CREATE TABLE `history` (
  `id` mediumint(11) unsigned NOT NULL auto_increment,
  `log_id` int(11) NOT NULL default '0',
  `data` mediumtext,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `records`
--

CREATE TABLE `records` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `domain_id` int(11) default NULL,
  `name` varchar(255) default NULL,
  `type` varchar(6) default NULL,
  `content` varchar(255) default NULL,
  `ttl` int(11) default NULL,
  `prio` int(11) default NULL,
  `change_date` int(11) default NULL,
  `tupasorting` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `rec_name_index` (`name`),
  KEY `nametype_index` (`name`,`type`),
  KEY `domain_id` (`domain_id`)
) ENGINE=InnoDB;

--
-- Table structure for table `supermasters`
--

CREATE TABLE `supermasters` (
  `ip` varchar(25) NOT NULL default '',
  `nameserver` varchar(255) NOT NULL default '',
  `account` varchar(40) default NULL
) ENGINE=InnoDB;

--
-- Table structure for table `template_records`
--

CREATE TABLE `template_records` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `tmpl_id` int(11) NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `type` varchar(6) NOT NULL default '',
  `content` varchar(255) NOT NULL default '',
  `ttl` int(11) default NULL,
  `prio` int(11) default NULL,
  `tupasorting` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `templates`
--

CREATE TABLE `templates` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `usr_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM;

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `username` varchar(30) NOT NULL default '',
  `password` varchar(32) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `firstname` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `grp_id` int(11) NOT NULL default '0',
  `notice` text,
  `max_domains` int(11) NOT NULL default '0',
  `max_templates` int(11) NOT NULL default '0',
  `permissions` text,
  `preferences` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM;

--
-- Table structure for table `backup_config`
--

CREATE TABLE `backup_config` (
  `email` varchar(255) NOT NULL default '',
  `compression` int(2) NOT NULL default '0',
  `save` int(2) NOT NULL default '0',
  `dumpOptions` text,
  `path_local` varchar(255) NOT NULL default '',
  `path_remote` varchar(255) NOT NULL default '',
  `protocol` int(2) NOT NULL default '0',
  `passive` int(2) NOT NULL default '0',
  `ssh_fingerprint` varchar(47) NOT NULL default '',
  `host` varchar(255) NOT NULL default '',
  `port` int(5) NOT NULL default '0',
  `username` varchar(255) NOT NULL default '',
  `password` varchar(255) NOT NULL default '',
  `maintenance` text,
  `time` text,
  `next_exec` int(11) NOT NULL default '0'
) ENGINE=MyISAM;
