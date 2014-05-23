<?php

class CM_Cache_Cli extends CM_Cli_Runnable_Abstract {

    public function clear() {
        $this->_getOutput()->writeln('Clearing cache...');

        $classes = CM_Util::getClassChildren('CM_Cache_Storage_Abstract', false);
        foreach ($classes as $className) {
            $this->_getOutput()->writeln('  ' . $className);
            $cache = new $className;
            $cache->flush();
        }

        $this->_getOutput()->writeln('Cache cleared.');
    }

    public static function getPackageName() {
        return 'cache';
    }
}
