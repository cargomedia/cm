<?php

class CM_Asset_Javascript_Bundle_Library extends CM_Asset_Javascript_Bundle_Abstract {

    public function __construct(CM_Site_Abstract $site, $sourceMapsOnly = null) {
        parent::__construct($site, $sourceMapsOnly);

        $this->_js->addInlineContent('/App/init', $this->_getAppInit($this->_getAppClassName($site)), false, true);
        $this->_js->addInlineContent('/App/internals', $this->_getInternals());

        foreach (self::getIncludedPaths($site) as $path) {
            $this->_js->addRawPath($path);
        }

        $watch = [];
        $mapping = [
            '/App/' => '(^|.*/)App/'
        ];
        foreach (array_reverse($this->_site->getModules()) as $moduleName) {
            $mapping['/' . $moduleName . '/library/'] = '(^|.*/)' . $moduleName . '/library/' . $moduleName;
            $watch[] = CM_Util::getModulePath($moduleName) . '/library/**/*.js';
        }

        $this->_js->addSourceMapping($mapping);
        $this->_js->addWatchPaths($watch);
    }

    protected function _getBundleName() {
        return 'library.js';
    }

    /**
     * @return string
     */
    protected function _getInternals() {
        return join("\n", [
            'var cm = require("/App/init");',
            (new CM_File(DIR_ROOT . 'resources/config/js/internal.js'))->read()
        ]);
    }

    /**
     * @param string $appClassName
     * @return string
     */
    protected function _getAppInit($appClassName) {
        return 'module.exports = window.cm = new ' . $appClassName . '();';
    }

    /**
     * @param CM_Site_Abstract $site
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _getAppClassName(CM_Site_Abstract $site) {
        foreach ($site->getModules() as $moduleName) {
            $file = new CM_File(DIR_ROOT . CM_Bootloader::getInstance()->getModulePath($moduleName) . 'library/' . $moduleName . '/App.js');
            if ($file->exists()) {
                return $moduleName . '_App';
            }
        }
        throw new CM_Exception_Invalid('No App class found');
    }

    /**
     * @param CM_Site_Abstract $site
     * @return string[]
     */
    public static function getIncludedPaths(CM_Site_Abstract $site) {
        $pathsUnsorted = CM_Util::rglobLibraries('*.js', $site);
        return array_keys(CM_Util::getClasses($pathsUnsorted));
    }
}
