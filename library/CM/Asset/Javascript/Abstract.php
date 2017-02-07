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
     * @param string[] $sourcePaths
     * @param bool     $generateSourceMaps
     * @return string
     */
    protected function _browserify(array $mainPaths, array $sourcePaths, $generateSourceMaps) {
        if (!count($mainPaths)) {
            return '';
        }

        $involvedFiles = [];
        foreach ($sourcePaths as $sourcePath) {
            $involvedFiles = array_merge($involvedFiles, CM_Util::rglob('*.js', $sourcePath));
        }
        foreach ($mainPaths as $mainPath) {
            $involvedFiles[] = $mainPath;
        }
        $cacheKeyContent = \Functional\reduce_left(array_unique($involvedFiles), function ($path, $index, $collection, $carry) {
            return md5($carry . (new CM_File($path))->read());
        }, '');

        $cacheKeyMainPaths = \Functional\reduce_left($mainPaths, function ($path, $index, $collection, $carry) {
            return md5($carry . $path);
        }, '');

        $cache = CM_Cache_Persistent::getInstance();
        $cacheKey = $cache->key(__METHOD__, $cacheKeyContent, $cacheKeyMainPaths, $generateSourceMaps);
        return $cache->get($cacheKey, function () use ($mainPaths, $sourcePaths, $generateSourceMaps) {
            $args = $mainPaths;
            if ($generateSourceMaps) {
                $args[] = '--debug';
            }
            return CM_Util::exec('NODE_PATH="' . implode(':', $sourcePaths) . '" browserify', $args);
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
        $sourceMainPaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $sourceMainPaths = array_merge($sourceMainPaths, glob($this->_getPathInModule($moduleName, $mainPath) . '*.js'));
        }
        $this->_appendSourceMainBrowserify($sourceMainPaths, $generateSourceMaps);
    }

    /**
     * @param string[] $sourceMainPaths
     * @param bool     $generateSourceMaps
     * @throws CM_Exception
     */
    protected function _appendSourceMainBrowserify($sourceMainPaths, $generateSourceMaps) {
        if ($generateSourceMaps && !$this->_js->isEmpty()) {
            throw new CM_Exception('Cannot generate source maps when output container already contains code.');
        }

        $sourcePaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $sourcePaths[] = $this->_getPathInModule($moduleName, 'client-vendor/source');
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
