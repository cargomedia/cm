<?php

abstract class CM_Asset_Javascript_Bundle_Abstract extends CM_Asset_Javascript_Abstract {

    /** @var CM_Frontend_JavascriptContainer */
    protected $_js;

    /** @var CM_Site_Abstract */
    protected $_site;

    /** @var bool */
    protected $_debug;

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $debug
     */
    public function __construct(CM_Site_Abstract $site, $debug = null) {
        $this->_site = $site;
        $this->_debug = (bool) $debug;
        $this->_js = new CM_Frontend_JavascriptContainer_Bundle();
    }

    /**
     * @return string
     */
    abstract protected function _getBundleName();

    /**
     * @param $compressed
     * @return string
     */
    public function getCode($compressed) {
        $checksum = $this->_js->compile('checksum');
        $cacheKey = __METHOD__ . '_md5:' . $checksum . '_compressed:' . $compressed;
        return CM_Cache_Persistent::getInstance()->get($cacheKey, function () use ($compressed) {
            return $this->_js->compile('code', [
                'uglify' => $compressed
            ]);
        });
    }

    /**
     * @param $compressed
     * @return string
     */
    public function getSourceMaps($compressed) {
        $bundleName = $this->_getBundleName();
        $mapping = $this->_js->getSourceMapping();
        $checksum = $this->_js->compile('checksum');
        $cacheKey = __METHOD__ . '_md5:' . $checksum . '_compressed:' . $compressed;
        return CM_Cache_Persistent::getInstance()->get($cacheKey, function () use ($compressed, $mapping, $bundleName) {
            return $this->_js->compile('sourcemaps', [
                'bundleName' => $bundleName,
                'uglify'     => $compressed,
                'sourceMaps' => [
                    'enabled' => true,
                    'replace' => $mapping
                ]
            ]);
        });
    }

    /**
     * @param string                       $name
     * @param CM_Asset_Javascript_Abstract $asset
     */
    protected function _appendAsset($name, CM_Asset_Javascript_Abstract $asset) {
        $this->_js->addInlineContent($name, $asset->get());
    }

    /**
     * @param string $path
     * @param string $mapPath
     */
    protected function _appendDirectoryGlob($path, $mapPath) {
        $mapping = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $initPath = $this->_getPathInModule($moduleName, $path);
            $this->_js->addRawPath($initPath . '/**/*.js');
            $mapping['/' . $moduleName . '/' . $mapPath . '/'] = '.*/' . $moduleName . '/' . $path;
        }
        $this->_js->addSourceMapping($mapping);
    }

    /**
     * @param string $mainPath
     * @throws CM_Exception
     */
    protected function _appendDirectoryBrowserify($mainPath) {
        $mapping = [];
        $sourceMainPaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $sourceMainPaths = array_merge($sourceMainPaths, glob($this->_getPathInModule($moduleName, $mainPath) . '*.js'));
            $mapping['/' . $moduleName . '/main/'] = '.*/' . $moduleName . '/' . $mainPath;
        }
        $this->_appendSourceMainBrowserify($sourceMainPaths);
        $this->_js->addSourceMapping($mapping);
    }

    /**
     * @param string[] $sourceMainPaths
     * @param string   $mapPath
     * @throws CM_Exception
     */
    protected function _appendSourceMainBrowserify($sourceMainPaths) {
        $mapping = [];
        $sourcePaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $path = $this->_getPathInModule($moduleName, 'client-vendor/source');
            $sourcePaths[] = $path;
            $mapping['/' . $moduleName . '/source/'] = '.*/' . $moduleName . '/client-vendor/source/';
        }
        $this->_js->addEntryPaths($sourceMainPaths);
        $this->_js->addSourcePaths($sourcePaths);
        $this->_js->addSourceMapping($mapping);
    }

    /**
     * @param string $moduleName
     * @param string $path
     * @return string
     */
    protected function _getPathInModule($moduleName, $path) {
        return DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . $path;
    }

    /**
     * @return bool
     */
    protected function _isDebug() {
        return $this->_debug;
    }
}
