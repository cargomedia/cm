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
        CM_Util::mkDir(CM_Bootloader::getInstance()->getDirData());
        CM_Util::mkDir(CM_Bootloader::getInstance()->getDirUserfiles());
        $dirTmp = CM_Bootloader::getInstance()->getDirTmp();
        CM_Util::rmDirContents($dirTmp);
        CM_Util::mkdir($dirTmp);
    }

    /**
     * @param boolean|null $forceReload
     * @throws CM_Exception_Invalid
     */
    public function setupDatabase($forceReload = null) {
        $configDb = CM_Config::get()->CM_Db_Db;
        if (!$configDb->db) {
            throw new CM_Exception_Invalid('No database name configured');
        }
        $client = new CM_Db_Client($configDb->server['host'], $configDb->server['port'], $configDb->username, $configDb->password);

        if ($forceReload) {
            $client->createStatement('DROP DATABASE IF EXISTS ' . $client->quoteIdentifier($configDb->db))->execute();
        }

        $databaseExists = (bool) $client->createStatement('SHOW DATABASES LIKE ?')->execute(array($configDb->db))->fetch();
        if (!$databaseExists) {
            $client->createStatement('CREATE DATABASE ' . $client->quoteIdentifier($configDb->db))->execute();
        }

        $client->setDb($configDb->db);
        $tables = $client->createStatement('SHOW TABLES')->execute()->fetchAll();
        if (0 === count($tables)) {
            foreach (CM_Util::getResourceFiles('db/structure.sql') as $dump) {
                CM_Db_Db::runDump($configDb->db, $dump);
            }
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
                $render = new CM_Render($site, null, $language);
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
        CM_Bootloader::getInstance()->getNamespaces();
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
            $db = CM_Config::get()->CM_Db_Db->db;
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
        foreach (CM_Bootloader::getInstance()->getNamespaces() as $namespace) {
            $paths[$namespace] = CM_Util::getNamespacePath($namespace) . 'resources/db/update/';
        }

        $rootPath = DIR_ROOT . 'resources/db/update/';
        if (!in_array($rootPath, $paths)) {
            $paths[null] = $rootPath;
        }

        return $paths;
    }

    /**
     * @param int         $version
     * @param string|null $namespace
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _getUpdateScriptPath($version, $namespace = null) {
        $path = DIR_ROOT;
        if ($namespace) {
            $path = CM_Util::getNamespacePath($namespace);
        }
        $updateScript = $path . 'resources/db/update/' . $version . '.php';
        if (!CM_File::exists($updateScript)) {
            throw new CM_Exception_Invalid('Update script `' . $version . '` does not exist for `' . $namespace . '` namespace.');
        }
        return $updateScript;
    }
}
