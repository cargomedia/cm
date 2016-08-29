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
        $cacheKey = $this->_getCacheKey([
            'class'      => get_class($this),
            'method'     => __FUNCTION__,
            'checksum'   => $this->getChecksum(),
            'compressed' => $compressed
        ]);
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
        $cacheKey = $this->_getCacheKey([
            'class'      => get_class($this),
            'method'     => __FUNCTION__,
            'checksum'   => $this->getChecksum(),
            'compressed' => $compressed
        ]);
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
     * @return string
     */
    public function getChecksum() {
        $storage = CM_Cache_Storage_Runtime::getInstance();
        $cacheKey = $this->_getCacheKey([
            'method' => __METHOD__,
        ]);
        $checksum = $storage->get($cacheKey);
        if (!$checksum) {
            $checksum = '';
            foreach (array_reverse($this->getSite()->getModules()) as $moduleName) {
                $path = $this->_getPathInModule($moduleName);
                $checksum .= \Functional\reduce_left(CM_Util::rglob('**/*.js', $path), function ($path, $index, $collection, $carry) {
                    return md5($carry . (new CM_File($path))->read());
                }, '');
            }
            $storage->set($cacheKey, md5($checksum));
        }
        return $checksum;
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return $this->_site;
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
     * @param string      $moduleName
     * @param string|null $path
     * @return string
     */
    protected function _getPathInModule($moduleName, $path = null) {
        $path = null === $path ? '' : $path;
        return DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . $path;
    }

    /**
     * @param $extra
     * @return string
     */
    protected function _getCacheKey(array $extra = null) {
        $extra = null === $extra ? [] : $extra;
        $modules = $this->getSite()->getModules();
        sort($modules);
        $cacheKey = array_merge($extra, [
            'modules' => join('_', $modules),
        ]);
        $cacheKey = \Functional\map($cacheKey, function ($value, $key) {
            return $key . ':' . $value;
        });
        return join('/', $cacheKey);
    }

    /**
     * @return bool
     */
    protected function _isDebug() {
        return $this->_debug;
    }
}
