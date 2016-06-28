<?php

class CM_Asset_Javascript_Abstract extends CM_Asset_Abstract {

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
        $this->_js = new CM_Frontend_JavascriptContainer();
    }

    public function get() {
        $content = $this->_js->compile();
        if (!$this->_isDebug()) {
            $content = $this->_minify($content);
        }
        return $content;
    }

    /**
     * @param string $content
     * @return string
     */
    protected function _minify($content) {
        $md5 = md5($content);
        $cacheKey = CM_CacheConst::App_Resource . '_md5:' . $md5;
        $cache = CM_Cache_Persistent::getInstance();
        if (false === ($contentMinified = $cache->get($cacheKey))) {
            $uglifyCommand = 'uglifyjs --no-copyright';
            /**
             * Quote keys in literal objects, otherwise some browsers break.
             * E.g. "select2.js" on "Android 4.0.4"
             */
            $uglifyCommand .= ' --beautify beautify=false,quote-keys=true';
            $contentMinified = CM_Util::exec($uglifyCommand, null, $content);
            $cache->set($cacheKey, $contentMinified);
        }
        return $contentMinified;
    }

    /**
     * @param string[] $mainPaths
     * @param string[] $rootPaths
     * @param bool     $generateSourceMaps
     * @return string
     */
    protected function _browserify(array $mainPaths, array $rootPaths, $generateSourceMaps) {
        if (!count($mainPaths)) {
            return '';
        }

        $content = \Functional\reduce_left($rootPaths, function ($rootPath, $index, $collection, $carry) {
            return $carry . \Functional\reduce_left(CM_Util::rglob('*.js', $rootPath), function ($filePath, $index, $collection, $carry) {
                return $carry . md5((new CM_File($filePath))->read());
            }, '');
        }, '');

        $cacheKey = __METHOD__ . '_md5:' . md5($content) . '_generateSourceMaps:' . $generateSourceMaps;
        $cache = CM_Cache_Persistent::getInstance();
        return $cache->get($cacheKey, function () use ($mainPaths, $rootPaths, $generateSourceMaps) {
            $args = $mainPaths;
            if ($generateSourceMaps) {
                $args[] = '--debug';
            }
            return CM_Util::exec('NODE_PATH="' . implode(':', $rootPaths) . '" browserify', $args);
        });
    }

    /**
     * @param string $path
     */
    protected function _appendDirectoryGlob($path) {
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $initPath = $this->_getPathInModule($moduleName, $path);
            foreach (CM_Util::rglob('*.js', $initPath) as $filePath) {
                $content = (new CM_File($filePath))->read();
                $this->_js->append($content);
            }
        }
    }

    /**
     * @param string $mainPath
     * @param bool   $generateSourceMaps
     * @throws CM_Exception
     */
    protected function _appendDirectoryBrowserify($mainPath, $generateSourceMaps) {
        if ($generateSourceMaps && !$this->_js->isEmpty()) {
            throw new CM_Exception('Cannot generate source maps when output container already contains code.');
        }

        $sourceMainPaths = [];
        $sourcePaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $sourcePaths[] = $this->_getPathInModule($moduleName, 'client-vendor/source');
            $sourceMainPaths = array_merge($sourceMainPaths, glob($this->_getPathInModule($moduleName, $mainPath) . '*.js'));
        }

        $content = $this->_browserify($sourceMainPaths, $sourcePaths, $generateSourceMaps);
        $this->_js->append($content);
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
