<?php

if (!CM_Db_Db::existsColumn('cm_splittestVariation_fixture', 'conversionWeight')) {
	CM_Db_Db::exec('ALTER TABLE `cm_splittestVariation_fixture` ADD `conversionWeight` DECIMAL(10,2) DEFAULT 1 NOT NULL');
}
