<?php

class CM_Db_SetupScript extends CM_Provision_Script_Abstract {

    public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $mysqlClient = $manager->getDatabases()->getMaster();
        $db = $mysqlClient->getDb();

        $mysqlClient->setDb(null);
        $databaseExists = (bool) $mysqlClient->createStatement('SHOW DATABASES LIKE ?')->execute(array($db))->fetch();
        if (!$databaseExists) {
            $mysqlClient->createStatement('CREATE DATABASE ' . $mysqlClient->quoteIdentifier($db))->execute();
        }
        $mysqlClient->setDb($db);

        $tables = $mysqlClient->createStatement('SHOW TABLES')->execute()->fetchAll();
        $hasTables = count($tables) > 0;
        if (!$hasTables) {
            foreach (CM_Util::getResourceFiles('db/structure.sql') as $dump) {
                CM_Db_Db::runDump($db, $dump);
            }
        }
    }

    /**
     * @param CM_Service_Manager        $manager
     * @param CM_OutputStream_Interface $output
     */
    public function unload(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $mysqlClient = $manager->getDatabases()->getMaster();
        $db = $mysqlClient->getDb();
        $mysqlClient->setDb(null);
        $mysqlClient->createStatement('DROP DATABASE IF EXISTS ' . $mysqlClient->quoteIdentifier($db))->execute();
        $mysqlClient->setDb($db);
    }

    public function isLoaded(CM_Service_Manager $manager) {
        $mysqlClient = $manager->getDatabases()->getMaster();
        $db = $mysqlClient->getDb();

        $mysqlClient->setDb(null);
        $databaseExists = (bool) $mysqlClient->createStatement('SHOW DATABASES LIKE ?')->execute(array($db))->fetch();
        $mysqlClient->setDb($db);
        if (!$databaseExists) {
            return false;
        }

        $tables = $mysqlClient->createStatement('SHOW TABLES')->execute()->fetchAll();
        $hasTables = count($tables) > 0;
        if (!$hasTables) {
            return false;
        }

        return true;
    }
}
