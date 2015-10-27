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
    public function mergeJs($path) {
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $initPath = $this->_buildPath($moduleName, $path);
            foreach (CM_Util::rglob('*.js', $initPath) as $filePath) {
                $content = (new CM_File($filePath))->read();
                $this->_jsContainer->append($content);
            }
        }
    }

    /**
     * @param {string}  $path
     * @param {bool}    $generateSourceMap
     */
    public function browserifyJs($path, $generateSourceMap = null) {
        $generateSourceMap = (bool) $generateSourceMap;
        $sourceMainPaths = [];
        $sourcePaths = [];

        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $sourcePath = $this->_buildPath($moduleName, $path);
            $sourcePaths[] = $sourcePath;
            $sourceMainPaths = array_merge($sourceMainPaths, glob($sourcePath . '*/main.js'));
        }

        $content = $this->_browserify($sourceMainPaths, $sourcePaths, $generateSourceMap);
        $this->_jsContainer->append($content);
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
