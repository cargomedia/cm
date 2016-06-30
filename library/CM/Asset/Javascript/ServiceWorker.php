<?php

class CM_Asset_Javascript_ServiceWorker extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $debug
     */
    public function __construct(CM_Site_Abstract $site, $debug = null) {
        parent::__construct($site, $debug);

        $this->_appendConfig();
        $this->_appendDirectoryBrowserify('client-vendor/serviceworker/', false);
    }

    protected function _appendConfig() {
        $config = [
            'site' => $this->_site,
        ];
        $this->_js->append('var config = ' . CM_Params::encode($config, true) . ';');
    }
}
