<?php

class CM_Asset_Javascript_Abstract extends CM_Asset_Abstract {

    protected $_content;

    public function get($compress = null) {
        $content = (string) $this->_content;
        if ($compress) {
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
     * @param string[]     $mainPaths
     * @param string       $rootPath
     * @param boolean|null $debug
     * @return string
     */
    protected function _browserify(array $mainPaths, $rootPath, $debug = null) {
        $content = array_reduce(CM_Util::rglob('*.js', $rootPath), function ($carry, $item) {
            return $carry . (new CM_File($item))->read();
        }, '');

        $cacheKey = __METHOD__ . '_dir:' . $rootPath . '_md5:' . md5($content) . '_debug:' . $debug;
        $cache = CM_Cache_Persistent::getInstance();
        return $cache->get($cacheKey, function () use ($mainPaths, $rootPath, $debug) {
            $args = $mainPaths;
            if ($debug) {
                $args[] = '--debug';
            }
            return CM_Util::exec('NODE_PATH=' . $rootPath . ' browserify', $args, null, null);
        });
    }
}
