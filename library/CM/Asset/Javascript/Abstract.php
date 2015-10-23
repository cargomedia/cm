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
     * @param string       $mainPath
     * @param boolean|null $debug
     * @return string
     */
    protected function _browserify($mainPath, $debug = null) {
        $directoryPath = dirname($mainPath);
        $content = array_reduce(CM_Util::rglob('*.js', $directoryPath), function ($carry, $item) {
            return $carry . $item;
        }, '');
        $cacheKey = __METHOD__ . '_dir:' . $directoryPath . '_md5:' . md5($content) . '_debug:' . $debug;
        $cache = CM_Cache_Persistent::getInstance();
        return $cache->get($cacheKey, function () use ($mainPath, $directoryPath, $debug) {
            $args = [];
            if ($debug) {
                $args[] = '--debug';
            }
            $args[] = $mainPath;
            return CM_Util::exec('browserify', $args, null, null, ['NODE_PATH' => $directoryPath]);
        });
    }
}
