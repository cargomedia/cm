SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
/*!40101 SET NAMES utf8 */;
DROP TABLE IF EXISTS `cm_action`;
CREATE TABLE `cm_action` (
  `actorId` int(10) unsigned DEFAULT NULL,
  `ip` int(10) unsigned DEFAULT NULL,
  `verb` tinyint(3) DEFAULT NULL,
  `type` int(11) DEFAULT NULL,
  `actionLimitType` tinyint(3) unsigned DEFAULT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  `count` int(10) unsigned DEFAULT '1',
  `interval` int(10) unsigned NOT NULL DEFAULT '1',
  KEY `actorId` (`actorId`),
  KEY `ip` (`ip`),
  KEY `action` (`verb`),
  KEY `createStamp` (`createStamp`),
  KEY `modelType` (`type`),
  KEY `actionLimitType` (`actionLimitType`),
  KEY `interval` (`interval`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_actionLimit`;
CREATE TABLE `cm_actionLimit` (
  `actionType` tinyint(3) DEFAULT NULL,
  `actionVerb` tinyint(3) DEFAULT NULL,
  `type` int(10) unsigned NOT NULL,
  `role` tinyint(3) unsigned DEFAULT NULL,
  `limit` int(10) unsigned DEFAULT NULL,
  `period` int(10) unsigned NOT NULL,
  UNIQUE KEY `entityType` (`actionType`,`actionVerb`,`type`,`role`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_captcha`;
CREATE TABLE `cm_captcha` (
  `captcha_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `number` int(10) unsigned NOT NULL DEFAULT '0',
  `create_time` int(10) unsigned NOT NULL,
  PRIMARY KEY (`captcha_id`),
  KEY `create_time` (`create_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_ipBlocked`;
CREATE TABLE `cm_ipBlocked` (
  `ip` int(10) unsigned NOT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ip`),
  KEY `createStamp` (`createStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_language`;
CREATE TABLE `cm_language` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(30) NOT NULL,
  `abbreviation` varchar(5) NOT NULL,
  `enabled` tinyint(1) unsigned NOT NULL,
  `backupId` int(10) unsigned NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `abbreviation` (`abbreviation`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_languageKey`;
CREATE TABLE `cm_languageKey` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  `accessStamp` int(10) unsigned DEFAULT NULL,
  `updateCount` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `javascript` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `javascript` (`javascript`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_languageKey_variable`;
CREATE TABLE `cm_languageKey_variable` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `languageKeyId` int(10) unsigned NOT NULL,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`,`languageKeyId`),
  KEY `languageKeyId` (`languageKeyId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_languageValue`;
CREATE TABLE `cm_languageValue` (
  `languageKeyId` int(11) unsigned NOT NULL,
  `languageId` int(11) unsigned NOT NULL,
  `value` text NOT NULL,
  PRIMARY KEY (`languageKeyId`,`languageId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_locationCity`;
CREATE TABLE `cm_locationCity` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `stateId` int(10) unsigned DEFAULT NULL,
  `countryId` int(10) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  `_maxmind` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maxmind` (`_maxmind`),
  KEY `name` (`name`),
  KEY `stateId` (`stateId`),
  KEY `countryId` (`countryId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_locationCityIp`;
CREATE TABLE `cm_locationCityIp` (
  `cityId` int(10) unsigned NOT NULL,
  `ipStart` int(10) unsigned NOT NULL,
  `ipEnd` int(10) unsigned NOT NULL,
  KEY `cityId` (`cityId`),
  KEY `ipEnd` (`ipEnd`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_locationCountry`;
CREATE TABLE `cm_locationCountry` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `abbreviation` char(2) NOT NULL,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_locationCountryIp`;
CREATE TABLE `cm_locationCountryIp` (
  `countryId` int(10) unsigned NOT NULL,
  `ipStart` int(10) unsigned NOT NULL,
  `ipEnd` int(10) unsigned NOT NULL,
  KEY `ipEnd` (`ipEnd`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_locationState`;
CREATE TABLE `cm_locationState` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `countryId` int(10) unsigned NOT NULL,
  `name` varchar(120) NOT NULL,
  `_maxmind` char(5) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `maxmind` (`_maxmind`),
  KEY `name` (`name`),
  KEY `countryId` (`countryId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_locationZip`;
CREATE TABLE `cm_locationZip` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(10) NOT NULL,
  `cityId` int(10) unsigned NOT NULL,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `cityId` (`cityId`),
  KEY `name` (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_log`;
CREATE TABLE `cm_log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type` int(10) unsigned NOT NULL,
  `msg` varchar(5000) NOT NULL,
  `timeStamp` int(10) unsigned NOT NULL,
  `metaInfo` varchar(5000) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `type` (`type`,`timeStamp`),
  KEY `msg` (`msg`(333))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_mail`;
CREATE TABLE `cm_mail` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject` varchar(256) DEFAULT NULL,
  `text` text,
  `html` mediumtext,
  `createStamp` int(10) unsigned NOT NULL,
  `sender` text,
  `replyTo` text,
  `to` text,
  `cc` text,
  `bcc` text,
  PRIMARY KEY (`id`),
  KEY `createStamp` (`createStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_option`;
CREATE TABLE `cm_option` (
  `key` varchar(100) NOT NULL,
  `value` blob NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_role`;
CREATE TABLE `cm_role` (
  `userId` int(10) unsigned NOT NULL,
  `role` tinyint(3) unsigned NOT NULL,
  `startStamp` int(10) unsigned NOT NULL,
  `expirationStamp` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`userId`,`role`),
  KEY `expirationStamp` (`expirationStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_session`;
CREATE TABLE `cm_session` (
  `sessionId` char(32) NOT NULL,
  `data` text NOT NULL,
  `expires` int(10) unsigned NOT NULL,
  PRIMARY KEY (`sessionId`),
  KEY `expires` (`expires`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_smiley`;
CREATE TABLE `cm_smiley` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setId` int(10) NOT NULL,
  `code` varchar(50) NOT NULL,
  `file` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `section` (`setId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_smileySet`;
CREATE TABLE `cm_smileySet` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `label` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_splittest`;
CREATE TABLE `cm_splittest` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `createStamp` (`createStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_splittestVariation`;
CREATE TABLE `cm_splittestVariation` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `splittestId` int(10) unsigned NOT NULL,
  `name` varchar(100) NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `splittestId_name` (`splittestId`,`name`),
  KEY `splittestId` (`splittestId`),
  KEY `enabled` (`enabled`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_splittestVariation_user`;
CREATE TABLE `cm_splittestVariation_user` (
  `splittestId` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `variationId` int(10) unsigned NOT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  `conversionStamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`splittestId`,`userId`),
  KEY `splittestId` (`splittestId`),
  KEY `userId` (`userId`),
  KEY `conversionStamp` (`conversionStamp`),
  KEY `createStamp` (`createStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_stream`;
CREATE TABLE `cm_stream` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `createStamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `channel` varchar(32) NOT NULL,
  `data` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_streamChannel`;
CREATE TABLE `cm_streamChannel` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `key` varchar(64) NOT NULL,
  `type` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_streamChannelArchive_video`;
CREATE TABLE `cm_streamChannelArchive_video` (
  `id` int(10) unsigned NOT NULL,
  `userId` int(10) unsigned NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `duration` int(10) unsigned NOT NULL,
  `thumbnailCount` int(10) unsigned NOT NULL,
  `hash` char(32) NOT NULL,
  `streamChannelType` int(10) unsigned NOT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `createStamp` (`createStamp`),
  KEY `streamChannelType` (`streamChannelType`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_streamChannel_video`;
CREATE TABLE `cm_streamChannel_video` (
  `id` int(10) unsigned NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `thumbnailCount` int(10) unsigned NOT NULL DEFAULT '0',
  `serverId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_stream_publish`;
CREATE TABLE `cm_stream_publish` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned NOT NULL,
  `start` int(10) unsigned NOT NULL,
  `allowedUntil` int(10) unsigned NOT NULL,
  `key` varchar(32) NOT NULL,
  `channelId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `userId` (`userId`),
  KEY `channelId` (`channelId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_stream_subscribe`;
CREATE TABLE `cm_stream_subscribe` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(10) unsigned,
  `start` int(10) unsigned NOT NULL,
  `allowedUntil` int(10) unsigned NOT NULL,
  `key` varchar(32) NOT NULL,
  `channelId` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`),
  KEY `userId` (`userId`),
  KEY `channelId` (`channelId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_string`;
CREATE TABLE `cm_string` (
  `type` int(10) unsigned NOT NULL,
  `string` varchar(100) NOT NULL,
  PRIMARY KEY (`type`,`string`),
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_svm`;
CREATE TABLE `cm_svm` (
  `id` int(11) NOT NULL,
  `trainingChanges` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `trainingChanges` (`trainingChanges`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_svmtraining`;
CREATE TABLE `cm_svmtraining` (
  `svmId` int(11) NOT NULL,
  `class` int(11) NOT NULL,
  `values` blob NOT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  KEY `svmId` (`svmId`),
  KEY `createStamp` (`createStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_tmp_location`;
CREATE TABLE `cm_tmp_location` (
  `level` tinyint(4) NOT NULL,
  `id` int(10) unsigned NOT NULL,
  `1Id` int(10) unsigned DEFAULT NULL,
  `2Id` int(10) unsigned DEFAULT NULL,
  `3Id` int(10) unsigned DEFAULT NULL,
  `4Id` int(10) unsigned DEFAULT NULL,
  `name` varchar(120) DEFAULT NULL,
  `abbreviation` char(2) DEFAULT NULL,
  `lat` float DEFAULT NULL,
  `lon` float DEFAULT NULL,
  UNIQUE KEY `levelId` (`level`,`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_tmp_userfile`;
CREATE TABLE `cm_tmp_userfile` (
  `uniqid` varchar(32) NOT NULL DEFAULT '',
  `filename` varchar(100) NOT NULL DEFAULT '',
  `createStamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`uniqid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_user`;
CREATE TABLE `cm_user` (
  `userId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `activityStamp` int(10) unsigned NOT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  `site` tinyint(3) unsigned DEFAULT NULL,
  `languageId` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`userId`),
  KEY `activityStamp` (`activityStamp`),
  KEY `createStamp` (`createStamp`),
  KEY `languageId` (`languageId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_useragent`;
CREATE TABLE `cm_useragent` (
  `userId` int(10) unsigned NOT NULL,
  `createStamp` int(10) unsigned NOT NULL,
  `useragent` varchar(200) NOT NULL,
  PRIMARY KEY (`userId`,`useragent`),
  KEY `createStamp` (`createStamp`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_user_online`;
CREATE TABLE `cm_user_online` (
  `userId` int(10) unsigned NOT NULL,
  `visible` tinyint(3) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`userId`),
  KEY `visible` (`visible`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_user_preference`;
CREATE TABLE `cm_user_preference` (
  `userId` int(10) unsigned NOT NULL,
  `preferenceId` int(10) unsigned NOT NULL,
  `value` tinyint(1) NOT NULL,
  PRIMARY KEY (`userId`,`preferenceId`),
  KEY `preferenceId` (`preferenceId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `cm_user_preferenceDefault`;
CREATE TABLE `cm_user_preferenceDefault` (
  `preferenceId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `section` varchar(128) NOT NULL,
  `key` varchar(128) NOT NULL,
  `defaultValue` tinyint(1) NOT NULL,
  `configurable` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`preferenceId`),
  UNIQUE KEY `section` (`section`,`key`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;