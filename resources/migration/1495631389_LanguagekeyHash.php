<?php

class Migration_1495631389_LanguagekeyHash implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function up(\CM_OutputStream_Interface $output) {
        if (!CM_Db_Db::existsColumn('cm_model_languagekey', 'nameHash')) {
            CM_Db_Db::exec("ALTER TABLE cm_model_languagekey ADD nameHash VARCHAR(40) NOT NULL");
        }
        CM_Db_Db::exec('UPDATE cm_model_languagekey SET nameHash = SHA1(`name`) WHERE nameHash=""');
        if (!CM_Db_Db::existsIndex('cm_model_languagekey', 'nameHash')) {
            CM_Db_Db::exec('ALTER TABLE cm_model_languagekey ADD UNIQUE KEY nameHash (nameHash)');
        }
    }
}
