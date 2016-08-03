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
