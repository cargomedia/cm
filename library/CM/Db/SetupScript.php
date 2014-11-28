<?php

class CM_Db_SetupScript extends CM_Provision_Script_Abstract implements CM_Provision_Script_UnloadableInterface {

    use CM_Provision_Script_IsLoadedTrait;

    public function load(CM_OutputStream_Interface $output) {
        $mysqlDbClient = $this->getServiceManager()->getDatabases()->getMaster();
        $databaseName = $mysqlDbClient->getDatabaseName();
        $mysqlClient = $mysqlDbClient->getClientWithoutDatabase();

        $databaseExists = (bool) $mysqlClient->createStatement('SHOW DATABASES LIKE ?')->execute(array($databaseName))->fetch();
        if (!$databaseExists) {
            $mysqlClient->createStatement('CREATE DATABASE ' . $mysqlClient->quoteIdentifier($databaseName))->execute();
        }

        $tables = $mysqlDbClient->createStatement('SHOW TABLES')->execute()->fetchAll();
        $hasTables = count($tables) > 0;
        if (!$hasTables) {
            foreach (CM_Util::getResourceFiles('db/structure.sql') as $dump) {
                CM_Db_Db::runDump($databaseName, $dump);
            }
        }
        $this->_setInitialVersion();
    }

    public function unload(CM_OutputStream_Interface $output) {
        $mysqlDbClient = $this->getServiceManager()->getDatabases()->getMaster();
        $mysqlClient = $mysqlDbClient->getClientWithoutDatabase();
        $db = $mysqlDbClient->getDatabaseName();
        $mysqlClient->createStatement('DROP DATABASE IF EXISTS ' . $mysqlDbClient->quoteIdentifier($db))->execute();
    }

    public function reload(CM_OutputStream_Interface $output) {
        $tableNames = CM_Db_Db::exec('SHOW TABLES')->fetchAllColumn();
        CM_Db_Db::exec('SET foreign_key_checks = 0;');
        foreach ($tableNames as $table) {
            CM_Db_Db::delete($table);
        }
        CM_Db_Db::exec('SET foreign_key_checks = 1;');
    }

    public function getRunLevel() {
        return 1;
    }

    protected function _isLoaded() {
        $mysqlDbClient = $this->getServiceManager()->getDatabases()->getMaster();
        $mysqlClient = $mysqlDbClient->getClientWithoutDatabase();

        $databaseExists = (bool) $mysqlClient->createStatement('SHOW DATABASES LIKE ?')->execute(array($mysqlDbClient->getDatabaseName()))->fetch();
        if (!$databaseExists) {
            return false;
        }

        $tables = $mysqlDbClient->createStatement('SHOW TABLES')->execute()->fetchAll();
        $hasTables = count($tables) > 0;
        if (!$hasTables) {
            return false;
        }

        return true;
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
}
