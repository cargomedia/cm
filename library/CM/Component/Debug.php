<?php

class CM_Component_Debug extends CM_Component_Abstract {

    public function checkAccessible(CM_RenderEnvironment $environment) {
        if (!CM_Bootloader::getInstance()->isDebug()) {
            throw new CM_Exception_NotAllowed();
        }
    }

    public function prepare(CM_RenderEnvironment $environment, CM_ViewResponse $viewResponse) {
        $debug = CM_Debug::getInstance();
        $stats = $debug->getStats();
        ksort($stats);
        $viewResponse->set('stats', $stats);
        $cacheNames = array('CM_Cache_Storage_Memcache', 'CM_Cache_Storage_Apc', 'CM_Cache_Storage_File');
        $viewResponse->getJs()->setProperty('cacheNames', $cacheNames);
        $viewResponse->set('cacheNames', $cacheNames);
    }

    public function ajax_clearCache(CM_Params $params, CM_ViewFrontendHandler $handler, CM_Response_View_Ajax $response) {
        if (!CM_Bootloader::getInstance()->isDebug()) {
            throw new CM_Exception_NotAllowed();
        }
        $cachesCleared = array();
        if ($params->getBoolean('CM_Cache_Storage_Memcache', false)) {
            $cache = new CM_Cache_Storage_Memcache();
            $cache->flush();
            $cachesCleared[] = 'CM_Cache_Storage_Memcache';
        }
        if ($params->getBoolean('CM_Cache_Storage_Apc', false)) {
            $cache = new CM_Cache_Storage_Apc();
            $cache->flush();
            $cachesCleared[] = 'CM_Cache_Storage_Apc';
        }
        if ($params->getBoolean('CM_Cache_Storage_File', false)) {
            $cache = new CM_Cache_Storage_File();
            $cache->flush();
            $cachesCleared[] = 'CM_Cache_Storage_File';
        }
        $handler->message('Cleared: ' . implode(', ', $cachesCleared));
    }
}
