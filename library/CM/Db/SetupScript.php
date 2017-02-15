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
        $this->_setInitialVersion();
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
        $this->_setInitialVersion();
    }

    public function getRunLevel() {
        return 1;
    }

    protected function _isLoaded() {
        $mysqlDbClient = $this->getServiceManager()->getDatabases()->getMaster();
        $mysqlClient = $mysqlDbClient->getClientWithoutDatabase();
        return (bool) $mysqlClient->createStatement('SHOW DATABASES LIKE ?')->execute(array($mysqlDbClient->getDatabaseName()))->fetch();
    }

    private function _setInitialVersion() {
        $app = CM_App::getInstance();
        foreach (CM_App::getInstance()->getUpdateScriptPaths() as $namespace => $path) {
            $updateFiles = CM_Util::rglob('*.php', $path);
            $version = array_reduce($updateFiles, function ($initial, $path) {
                $filename = basename($path);
                return max($initial, (int) $filename);
            }, 0);
            $app->setVersion($version, $namespace);
        }
    }

    private function _setInitialMigrationScripts() {
        $migrationPaths = CM_Util::getMigrationPaths();
        $loader = new CM_Migration_Loader($this->getServiceManager(), $migrationPaths);
        foreach ($loader->getRunnerList() as $runner) {
            $name = $runner->getName();
            $record = CM_Migration_Model::findByName($name);
            if (!$record) {
                $record = CM_Migration_Model::create($name);
            }
            $record->setExecutedAt(new DateTime());
        }
    }
}
