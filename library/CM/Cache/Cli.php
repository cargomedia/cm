<?php

class CM_Cache_Cli extends CM_Cli_Runnable_Abstract {

    public function clear() {
        echo 'Clearing cache...' . PHP_EOL;
        $cache = new CM_Cache_Storage_Memcache();
        $cache->flush();

        $cache = new CM_Cache_Storage_Apc();
        $cache->flush();

        $cache = new CM_Cache_Storage_File();
        $cache->flush();
        echo 'Cache cleared.' . PHP_EOL;
    }

    public static function getPackageName() {
        return 'cache';
    }
}
