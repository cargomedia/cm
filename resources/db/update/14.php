<?php

if (CM_Db_Db::existsColumn('cm_languageKey', 'accessStamp')) {
	CM_Db_Db::exec('ALTER TABLE  `cm_languageKey` CHANGE  `accessStamp`  `updateCountResetVersion` int(10) unsigned DEFAULT NULL');
}
