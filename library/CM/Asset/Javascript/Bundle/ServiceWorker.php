<?php

class CM_Asset_Javascript_Bundle_ServiceWorker extends CM_Asset_Javascript_Bundle_Abstract {

    /**
     * @param CM_Site_Abstract $site
     * @param bool|null        $debug
     */
    public function __construct(CM_Site_Abstract $site, $debug = null) {
        parent::__construct($site, $debug);
        $this->_appendConfig();
        $this->_appendDirectoryBrowserify('client-vendor/serviceworker/');
    }

    public function get() {
        return $this->getCode(!$this->_isDebug());
    }

    protected function _getBundleName() {
        return 'worker.js';
    }

    protected function _appendConfig() {
        $config = [
            'site' => $this->_site,
        ];
        $this->_js->addInlineContent('worker/config', 'module.exports = ' . CM_Params::encode($config, true) . ';', false);
    }
}
