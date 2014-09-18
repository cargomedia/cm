<?php

class CM_App {

    /**
     * @var CM_App
     */
    private static $_instance;

    /**
     * @return CM_App
     */
    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function setupFilesystem() {
        $serviceManager = CM_Service_Manager::getInstance();
        $serviceManager->getFilesystems()->getData()->getAdapter()->setup();
        $serviceManager->getFilesystems()->getTmp()->getAdapter()->setup();
        $serviceManager->getFilesystems()->getTmp()->deleteByPrefix('/');
        foreach ($serviceManager->getUserContent()->getFilesystemList() as $filesystem) {
            $filesystem->getAdapter()->setup();
        }
    }

    /**
     * @param boolean|null $forceReload
     */
    public function setupDatabase($forceReload = null) {
        $this->_setupDbSql($forceReload);
        $this->_setupDbMongo($forceReload);
        $app = CM_App::getInstance();
        foreach ($this->_getUpdateScriptPaths() as $namespace => $path) {
            $updateFiles = CM_Util::rglob('*.php', $path);
            $version = array_reduce($updateFiles, function ($initial, $path) {
                $filename = basename($path);
                return max($initial, (int) $filename);
            }, $app->getVersion());
            $app->setVersion($version, $namespace);
        }
        foreach (CM_Util::getResourceFiles('db/setup.php') as $setupScript) {
            require $setupScript->getPath();
        }
    }

    public function setupTranslations() {
        /** @var CM_Model_Language $language */
        foreach (new CM_Paging_Language_All() as $language) {
            $path = 'translations/' . $language->getAbbreviation() . '.php';
            foreach (CM_Util::getResourceFiles($path) as $translationsFile) {
                $translationsSetter = require $translationsFile->getPath();
                if (!$translationsSetter instanceof Closure) {
                    throw new CM_Exception_Invalid('Invalid translation file. `' . $translationsFile->getPath() . '` must return callable');
                }
                $translationsSetter($language);
            }
        }
    }

    public function fillCaches() {
        /** @var CM_Asset_Javascript_Abstract[] $assetList */
        $assetList = array();
        $languageList = new CM_Paging_Language_Enabled();
        foreach (CM_Site_Abstract::getAll() as $site) {
            $assetList[] = new CM_Asset_Javascript_Internal($site);
            $assetList[] = new CM_Asset_Javascript_Library($site);
            $assetList[] = new CM_Asset_Javascript_VendorAfterBody($site);
            $assetList[] = new CM_Asset_Javascript_VendorBeforeBody($site);
            foreach ($languageList as $language) {
                $render = new CM_Frontend_Render(new CM_Frontend_Environment($site, null, $language));
                $assetList[] = new CM_Asset_Css_Vendor($render);
                $assetList[] = new CM_Asset_Css_Library($render);
            }
        }
        foreach ($languageList as $language) {
            $assetList[] = new CM_Asset_Javascript_Translations($language);
        }
        foreach ($assetList as $asset) {
            $asset->get(true);
        }
        CM_Bootloader::getInstance()->getModules();
    }

    /**
     * @param string|null $namespace
     * @return int
     */
    public function getVersion($namespace = null) {
        $namespace = (string) $namespace;
        if ($namespace) {
            $namespace = '.' . $namespace;
        }
        return (int) CM_Option::getInstance()->get('app.version' . $namespace);
    }

    /**
     * @param int         $version
     * @param string|null $namespace
     */
    public function setVersion($version, $namespace = null) {
        $version = (int) $version;
        $namespace = (string) $namespace;
        if ($namespace) {
            $namespace = '.' . $namespace;
        }
        CM_Option::getInstance()->set('app.version' . $namespace, $version);
    }

    /**
     * @return int
     */
    public function getDeployVersion() {
        return (int) CM_Config::get()->deployVersion;
    }

    /**
     * @param Closure|null $callbackBefore fn($version)
     * @param Closure|null $callbackAfter  fn($version)
     * @return int Number of version bumps
     */
    public function runUpdateScripts(Closure $callbackBefore = null, Closure $callbackAfter = null) {
        CM_Cache_Shared::getInstance()->flush();
        CM_Cache_Local::getInstance()->flush();
        $versionBumps = 0;
        foreach ($this->_getUpdateScriptPaths() as $namespace => $path) {
            $version = $versionStart = $this->getVersion($namespace);
            while (true) {
                $version++;
                if (!$this->runUpdateScript($namespace, $version, $callbackBefore, $callbackAfter)) {
                    $version--;
                    break;
                }
                $this->setVersion($version, $namespace);
            }
            $versionBumps += ($version - $versionStart);
        }
        if ($versionBumps > 0) {
            $db = CM_Service_Manager::getInstance()->getDatabases()->getMaster()->getDb();
            CM_Db_Db::exec('DROP DATABASE IF EXISTS `' . $db . '_test`');
        }
        return $versionBumps;
    }

    /**
     * @param string       $namespace
     * @param int          $version
     * @param Closure|null $callbackBefore
     * @param Closure|null $callbackAfter
     * @return int
     */
    public function runUpdateScript($namespace, $version, Closure $callbackBefore = null, Closure $callbackAfter = null) {
        try {
            $updateScript = $this->_getUpdateScriptPath($version, $namespace);
        } catch (CM_Exception_Invalid $e) {
            return 0;
        }
        if ($callbackBefore) {
            $callbackBefore($version);
        }
        require $updateScript;
        if ($callbackAfter) {
            $callbackAfter($version);
        }
        return 1;
    }

    /**
     * @return string[]
     */
    private function _getUpdateScriptPaths() {
        $paths = array();
        foreach (CM_Bootloader::getInstance()->getModules() as $moduleName) {
            $paths[$moduleName] = CM_Util::getModulePath($moduleName) . 'resources/db/update/';
        }

        $rootPath = DIR_ROOT . 'resources/db/update/';
        if (!in_array($rootPath, $paths)) {
            $paths[null] = $rootPath;
        }

        return $paths;
    }

    /**
     * @param int         $version
     * @param string|null $moduleName
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _getUpdateScriptPath($version, $moduleName = null) {
        $path = DIR_ROOT;
        if ($moduleName) {
            $path = CM_Util::getModulePath($moduleName);
        }
        $file = new CM_File($path . 'resources/db/update/' . $version . '.php');
        if (!$file->getExists()) {
            throw new CM_Exception_Invalid('Update script `' . $version . '` does not exist for `' . $moduleName . '` namespace.');
        }
        return $file->getPath();
    }

    /**
     * @param boolean $forceReload
     * @throws CM_Exception_Invalid
     */
    private function _setupDbMongo($forceReload) {
        $mongoClient = CM_Service_Manager::getInstance()->getMongoDb();
        if ($forceReload) {
            $mongoClient->dropDatabase();
        }
        $collections = $mongoClient->listCollectionNames();
        if (0 === count($collections)) {
            foreach (CM_Util::getResourceFiles('mongo/collections.json') as $dump) {
                $collectionInfo = CM_Params::jsonDecode($dump->read());
                foreach ($collectionInfo as $collection => $indexes) {
                    $mongoClient->createCollection($collection);
                    foreach ($indexes as $indexInfo) {
                        $mongoClient->createIndex($collection, $indexInfo['key'], $indexInfo['options']);
                    }
                }
            }
        }
    }

    /**
     * @param boolean $forceReload
     * @throws CM_Db_Exception
     */
    private function _setupDbSql($forceReload) {
        $mysqlClient = CM_Service_Manager::getInstance()->getDatabases()->getMaster();
        $db = $mysqlClient->getDb();
        $mysqlClient->setDb(null);
        if ($forceReload) {
            $mysqlClient->createStatement('DROP DATABASE IF EXISTS ' . $mysqlClient->quoteIdentifier($db))->execute();
        }
        $databaseExists = (bool) $mysqlClient->createStatement('SHOW DATABASES LIKE ?')->execute(array($db))->fetch();
        if (!$databaseExists) {
            $mysqlClient->createStatement('CREATE DATABASE ' . $mysqlClient->quoteIdentifier($db))->execute();
        }
        $mysqlClient->setDb($db);
        $tables = $mysqlClient->createStatement('SHOW TABLES')->execute()->fetchAll();
        if (0 === count($tables)) {
            foreach (CM_Util::getResourceFiles('db/structure.sql') as $dump) {
                CM_Db_Db::runDump($db, $dump);
            }
        }
    }
}
