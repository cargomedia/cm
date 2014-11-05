<?php

class CM_App {

    /**
     * @var CM_App
     */
    private static $_instance;

    /** @var CM_Service_Manager */
    private $_serviceManager;

    public function __construct() {
        $this->_serviceManager = CM_Service_Manager::getInstance();
    }

    /**
     * @return CM_App
     */
    public static function getInstance() {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * @param CM_OutputStream_Interface $output
     * @param bool                      $reload
     */
    public function setup(CM_OutputStream_Interface $output, $reload) {
        $loader = new CM_Provision_Loader($output);
        $loader->setServiceManager($this->_getServiceManager());
        $loader->registerScriptFromClassNames(CM_Config::get()->CM_App->setupScriptClasses);
        if ($reload) {
            $loader->unload();
        }

        if ($loader->isAnyScriptLoaded()) {
            return;
        }
        $loader->load();
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
     * @return string[]
     */
    public function getUpdateScriptPaths() {
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
     * @param Closure|null $callbackBefore fn($version)
     * @param Closure|null $callbackAfter  fn($version)
     * @return int Number of version bumps
     */
    public function runUpdateScripts(Closure $callbackBefore = null, Closure $callbackAfter = null) {
        CM_Cache_Shared::getInstance()->flush();
        CM_Cache_Local::getInstance()->flush();
        $versionBumps = 0;
        foreach ($this->getUpdateScriptPaths() as $namespace => $path) {
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
            $db = $this->_getServiceManager()->getDatabases()->getMaster()->getDb();
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
     * @return CM_Service_Manager
     */
    protected function _getServiceManager() {
        return $this->_serviceManager;
    }

    /**
     * @param int         $version
     * @param string|null $moduleName
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function _getUpdateScriptPath($version, $moduleName = null) {
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
}
