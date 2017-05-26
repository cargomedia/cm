<?php

class Migration_1495786897_AddVariationFrequency implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function up(\CM_OutputStream_Interface $output) {
        if (!CM_Db_Db::existsColumn('cm_splittestVariation', 'frequency')) {
            CM_Db_Db::exec("ALTER TABLE `cm_splittestVariation` ADD COLUMN `frequency` decimal(10,2) NOT NULL DEFAULT '1.00'");
        }
    }
}
