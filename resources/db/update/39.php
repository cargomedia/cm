<?php

if ('1.00' === CM_Db_Db::describeColumn('cm_splittestVariation_fixture', 'conversionWeight')->getDefaultValue()) {
    CM_Db_Db::exec('ALTER TABLE cm_splittestVariation_fixture
      MODIFY COLUMN conversionWeight decimal(10,2) NOT NULL DEFAULT 0');
    CM_Db_Db::update('cm_splittestVariation_fixture', array('conversionWeight' => 0), array('conversionStamp' => null));
}
