<?php

if (!CM_Db_Db::existsColumn('cm_locationState', 'abbreviation')) {
	CM_Db_Db::exec('ALTER TABLE `cm_locationState` ADD `abbreviation` char(2) AFTER `name`');
}

CM_Model_Location::createUSStatesAbbreviation();
