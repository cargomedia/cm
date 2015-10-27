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
     * @param string[]     $rootPaths
     * @param boolean|null $debug
     * @return string
     */
    protected function _browserify(array $mainPaths, array $rootPaths, $debug = null) {
        if (!count($mainPaths)) {
            return '';
        }

        $content = \Functional\reduce_left($rootPaths, function ($rootPath, $index, $collection, $carry) {
            return $carry . \Functional\reduce_left(CM_Util::rglob('*.js', $rootPath), function ($filePath, $index, $collection, $carry) {
                return $carry . (new CM_File($filePath))->read();
            }, '');
        }, '');

        $cacheKey = __METHOD__ . '_md5:' . md5($content) . '_debug:' . $debug;
        $cache = CM_Cache_Persistent::getInstance();
        return $cache->get($cacheKey, function () use ($mainPaths, $rootPaths, $debug) {
            $args = $mainPaths;
            if ($debug) {
                $args[] = '--debug';
            }
            return CM_Util::exec('NODE_PATH="' . implode(':', $rootPaths) . '" browserify', $args, null, null);
        });
    }
}
