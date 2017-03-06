<?php

class Migration_1488794963_RemoveDbUpdate implements \CM_Migration_UpgradableInterface, \CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    public function up(\CM_OutputStream_Interface $output) {
        if (0 !== CM_Db_Db::count('cm_option', '`key` lIKE "app.version%"')) {
            $output->writeln('- remove cm_option app.version* entries');
            CM_Db_Db::delete('cm_option', '`key` lIKE "app.version%"');
        }
    }
}
