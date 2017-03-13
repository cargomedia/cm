<?php

class CM_Frontend_Bundler_Factory implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param string    $socketUrl
     * @param bool|null $cacheEnabled
     */
    public function createBundler($socketUrl, $cacheEnabled = null) {
        $cacheEnabled = (bool) $cacheEnabled;
        $baseUrl = CM_App::getInstance()->getDirRoot();
        return new CM_Frontend_Bundler_Client($socketUrl, $baseUrl, $cacheEnabled);
    }
}
