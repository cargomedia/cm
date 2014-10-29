<?php

class CM_Db_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string $namespace
     */
    public function dbToFile($namespace) {
        $namespace = (string) $namespace;
        $this->_dbToFileSql($namespace);
        $this->_dbToFileMongo($namespace);
    }

    public function fileToDb() {
        CM_App::getInstance()->setupDatabase($this->_getStreamOutput(), true);
    }

    public function runUpdates() {
        $app = CM_App::getInstance();
        $output = $this->_getStreamOutput();
        $output->writeln('Running database updates…');
        $app->runUpdateScripts(function ($version) use ($output) {
            $output->writeln('  Running update ' . $version . '…');
        });
    }

    /**
     * @param integer     $version
     * @param string|null $namespace
     */
    public function runUpdate($version, $namespace = null) {
        $versionBumps = CM_App::getInstance()->runUpdateScript($namespace, $version);
        if ($versionBumps > 0) {
            $db = CM_Db_Db::getClient()->getDb();
            CM_Db_Db::exec('DROP DATABASE IF EXISTS `' . $db . '_test`');
        }
    }

    /**
     * @param string $namespace
     */
    private function _dbToFileMongo($namespace) {
        $mongo = CM_Service_Manager::getInstance()->getMongoDb();
        $collectionList = \Functional\select($mongo->listCollectionNames(), function($collection) use ($namespace) {
            return preg_match('/^' . strtolower($namespace) . '_/', $collection);
        });
        sort($collectionList);
        $optionsValid = ['unique', 'name', 'dropDups', 'sparse', 'expireAfterSeconds'];
        $indexes = [];
        foreach ($collectionList as $collection) {
            $indexList = $mongo->getIndexInfo($collection);
            foreach ($indexList as $indexInfo) {
                $key = $indexInfo['key'];
                $options = array_intersect_key($indexInfo, array_flip($optionsValid));
                $indexes[$collection][] = ['key' => $key, 'options' => $options];
            }
        }
        $dump = CM_Params::jsonEncode($indexes, true);
        $dirPath = CM_Util::getModulePath($namespace) . '/resources/mongo/';
        CM_File::getFilesystemDefault()->ensureDirectory($dirPath);
        CM_File::create($dirPath  . 'collections.json', $dump);
    }

    /**
     * @param string $namespace
     */
    private function _dbToFileSql($namespace) {
        $namespace = (string) $namespace;
        $tables = CM_Db_Db::exec("SHOW TABLES LIKE ?", array(strtolower($namespace) . '_%'))->fetchAllColumn();
        sort($tables);
        $dump = CM_Db_Db::getDump($tables, true);
        CM_File::create(CM_Util::getModulePath($namespace) . '/resources/db/structure.sql', $dump);
    }

    public static function getPackageName() {
        return 'db';
    }
}
