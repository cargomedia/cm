<?php

class CM_Asset_Javascript_Vendor extends CM_Asset_Javascript_Abstract {

    /** @var CM_Site_Abstract */
    protected $_site;

    /** @var CM_Frontend_JavascriptContainer */
    protected $_jsContainer;

    /**
     * @param CM_Site_Abstract $site
     */
    public function __construct(CM_Site_Abstract $site) {
        $this->_site = $site;
        $this->_jsContainer = new CM_Frontend_JavascriptContainer();
    }

    public function get($compress = null) {
        $this->_content = $this->_jsContainer->compile();
        return parent::get($compress);
    }

    /**
     * @param {string} $path
     */
    public function compileJsForAllModules($path) {
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $initPath = $this->_buildPath($moduleName, $path);
            foreach (CM_Util::rglob('*.js', $initPath) as $filePath) {
                $content = (new CM_File($filePath))->read();
                $this->_jsContainer->append($content);
            }
        }
    }

    /**
     * @param {string} $path
     */
    public function browserifyJsForAllModules($path) {
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $this->browserifyJs($moduleName, $path, false);
        }
    }

    /**
     * @param {string} $path
     */
    public function browserifyJs($moduleName, $path, $generateSourceMap = null) {
        $generateSourceMap = (bool) $generateSourceMap;
        $sourcePath = $this->_buildPath($moduleName, $path);
        $sourceMainPaths = glob($sourcePath . '*/main.js');
        if (count($sourceMainPaths)) {
            $content = $this->_browserify($sourceMainPaths, $sourcePath, $generateSourceMap);
            $this->_jsContainer->append($content);
        }
    }

    /**
     * @param {string} $moduleName
     * @param {string} $path
     * @return string
     */
    protected function _buildPath($moduleName, $path) {
        return DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . $path;
    }
}
