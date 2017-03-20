<?php

class CM_Db_SetupScript extends CM_Provision_Script_Abstract implements CM_Provision_Script_UnloadableInterface {

    use CM_Provision_Script_IsLoadedTrait;

    public function load(CM_OutputStream_Interface $output) {
        $mysqlDbClient = $this->getServiceManager()->getDatabases()->getMaster();

        $databaseName = $mysqlDbClient->getDatabaseName();
        $mysqlClient = $mysqlDbClient->getClientWithoutDatabase();
        $mysqlClient->createStatement('CREATE DATABASE ' . $mysqlClient->quoteIdentifier($databaseName))->execute();

        foreach (CM_Util::getResourceFiles('db/structure.sql') as $dump) {
            CM_Db_Db::runDump($databaseName, $dump);
        }
        $this->_setInitialMigrationScripts();
    }

    public function unload(CM_OutputStream_Interface $output) {
        $mysqlDbClient = $this->getServiceManager()->getDatabases()->getMaster();
        $mysqlClient = $mysqlDbClient->getClientWithoutDatabase();
        $databaseName = $mysqlDbClient->getDatabaseName();
        $mysqlClient->createStatement('DROP DATABASE ' . $mysqlDbClient->quoteIdentifier($databaseName))->execute();
    }

    public function reload(CM_OutputStream_Interface $output) {
        $tableNames = CM_Db_Db::exec('SHOW TABLES')->fetchAllColumn();
        CM_Db_Db::exec('SET foreign_key_checks = 0;');
        foreach ($tableNames as $table) {
            CM_Db_Db::delete($table);
        }
        CM_Db_Db::exec('SET foreign_key_checks = 1;');
        $this->_setInitialMigrationScripts();
    }

    public function getRunLevel() {
        return 1;
    }

    protected function _isLoaded() {
        $mysqlDbClient = $this->getServiceManager()->getDatabases()->getMaster();
        $mysqlClient = $mysqlDbClient->getClientWithoutDatabase();
        return (bool) $mysqlClient->createStatement('SHOW DATABASES LIKE ?')->execute(array($mysqlDbClient->getDatabaseName()))->fetch();
    }

    /**
     * @return CM_Migration_Loader
     */
    private function _getMigrationLoader() {
        static $loader = null;
        if (null === $loader) {
            $loader = new CM_Migration_Loader($this->getServiceManager(), CM_Util::getMigrationPaths());
        }
        return $loader;
    }

    private function _setInitialMigrationScripts() {
        foreach ($this->_getMigrationLoader()->getRunnerList() as $runner) {
            $name = $runner->getName();
            if (0 === CM_Db_Db::count(CM_Migration_Model::getTableName(), ['name' => $name])) {
                CM_Db_Db::insert(CM_Migration_Model::getTableName(), ['name' => $name, 'executedAt' => time()]);
            }
        }
    }
}
