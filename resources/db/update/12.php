<?php

if (CM_Db_Db::existsIndex('cm_splittestVariation_fixture', 'fixtureId')) {
	CM_Db_Db::exec('
		ALTER TABLE cm_splittestVariation_fixture
			DROP INDEX fixtureId,
			DROP PRIMARY KEY');
}

if (CM_Db_Db::existsColumn('cm_splittestVariation_fixture', 'fixtureId')) {
	CM_Db_Db::exec('
		ALTER TABLE `cm_splittestVariation_fixture`
			CHANGE `fixtureId` `userId` INT(10) UNSIGNED NULL,
			ADD COLUMN `clientId` INT(10) UNSIGNED NULL AFTER `splittestId`');
	CM_Db_Db::exec(
		'UPDATE cm_splittestVariation_fixture
			SET clientId = userId, userId = NULL
			WHERE splittestId = 41');
}

if (!CM_Db_Db::existsIndex('cm_splittestVariation_fixture', 'user')) {
	CM_Db_Db::exec('
		ALTER TABLE cm_splittestVariation_fixture
			ADD UNIQUE `user` (`userId`, `splittestId`),
			ADD UNIQUE `client` (`clientId`, `splittestId`)');
}
