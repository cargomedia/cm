<?php

class CM_Cache_Cli extends CM_Cli_Runnable_Abstract {

    public function clear() {
        $this->_getStreamError()->writeln('Clearing cache...');

        $classes = CM_Util::getClassChildren('CM_Cache_Storage_Abstract', false);
        foreach ($classes as $className) {
            $this->_getStreamError()->writeln('  ' . $className);
            /** @var CM_Cache_Storage_Abstract $cache */
            $cache = new $className;
            $cache->flush();
        }

        $this->_getStreamError()->writeln('Cache cleared.');
    }

    public static function getPackageName() {
        return 'cache';
    }
}
