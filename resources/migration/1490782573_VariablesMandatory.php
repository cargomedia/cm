<?php

class Migration_1490782573_VariablesMandatory implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function up(\CM_OutputStream_Interface $output) {
        CM_Db_Db::update('cm_model_languagekey', ['variables' => '[]'], ['variables' => null]);
        $columnDescription = CM_Db_Db::describeColumn('cm_model_languagekey', 'variables');
        if ($columnDescription->getAllowNull()) {
            CM_Db_Db::exec("alter table `cm_model_languagekey` change `variables` `variables` text CHARACTER SET utf8 COLLATE utf8_bin NOT NULL");
        }
    }
}
