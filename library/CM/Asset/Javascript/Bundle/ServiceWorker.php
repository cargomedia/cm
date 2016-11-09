<?php

class CM_Asset_Javascript_Bundle_ServiceWorker extends CM_Asset_Javascript_Bundle_Abstract {

    public function __construct(CM_Site_Abstract $site, $sourceMapsOnly = null) {
        parent::__construct($site, $sourceMapsOnly);
        $workerConfig = $this->_getWorkerConfig();
        $this->_js->addInlineContent('worker/config', $workerConfig, true, true);
        $this->_appendDirectoryBrowserify('client-vendor/serviceworker/');
    }

    protected function _getBundleName() {
        return 'worker.js';
    }

    /**
     * @return string
     */
    protected function _getWorkerConfig() {
        $config = [
            'site' => $this->_site,
        ];
        return 'module.exports = ' . CM_Params::encode($config, true) . ';';
    }
}
