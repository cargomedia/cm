<?php

abstract class CM_Asset_Javascript_Bundle_Abstract extends CM_Asset_Javascript_Abstract {

    /** @var CM_Frontend_JavascriptContainer_Bundle */
    protected $_js;

    /** @var bool */
    protected $_sourceMapsOnly;

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $sourceMapsOnly
     */
    public function __construct(CM_Site_Abstract $site, $sourceMapsOnly = null) {
        parent::__construct($site);
        $this->_sourceMapsOnly = (bool) $sourceMapsOnly;
        $this->_js = new CM_Frontend_JavascriptContainer_Bundle($this->_getName());
    }

    public function get() {
        if ($this->_sourceMapsOnly) {
            return $this->_js->getSourceMaps();
        } else {
            return $this->_js->getCode();
        }
    }

    /**
     * @return string
     */
    abstract protected function _getBundleName();

    /**
     * @return string
     */
    protected function _getName() {
        return $this->getSite()->getName() . '/' . $this->_getBundleName() . ($this->_sourceMapsOnly ? '.map' : '');
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
        $initPaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $initPaths[] = $this->_getPathInModule($moduleName, $path) . '**/*.js';
            $mapping['/' . $moduleName . '/' . $mapPath . '/'] = '^.*/?' . $moduleName . '/' . $path;
        }
        $this->_js->addRawPaths($initPaths);
        $this->_js->addWatchPaths($initPaths);
        $this->_js->addSourceMapping($mapping);
    }

    /**
     * @param $mainPath
     */
    protected function _appendDirectoryBrowserify($mainPath) {
        $mapping = [];
        $watchPaths = [];
        $sourceMainPaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $path = $this->_getPathInModule($moduleName, $mainPath) . '*.js';
            $watchPaths[] = $path;
            $sourceMainPaths = array_merge($sourceMainPaths, glob($path));
            $mapping['/' . $moduleName . '/main/'] = '^.*/?' . $moduleName . '/' . $mainPath;
        }
        $this->_js->addEntryPaths($sourceMainPaths);
        $this->_js->addWatchPaths($watchPaths);
        $this->_js->addSourceMapping($mapping);
        $this->_appendSourceBrowserify();
    }

    protected function _appendSourceBrowserify() {
        $mapping = [];
        $watchPaths = [];
        $sourcePaths = [];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $path = $this->_getPathInModule($moduleName, 'client-vendor/source');
            $sourcePaths[] = $path;
            $watchPaths[] = $path . '/**/*.js';
            $mapping['/' . $moduleName . '/source/'] = '^.*/?' . $moduleName . '/client-vendor/source/';
        }
        $this->_js->addSourcePaths($sourcePaths);
        $this->_js->addWatchPaths($watchPaths);
        $this->_js->addSourceMapping($mapping);
    }
}
