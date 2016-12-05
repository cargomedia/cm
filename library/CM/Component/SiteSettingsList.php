<?php

class CM_Component_SiteSettingsList extends CM_Component_Abstract {

    public function checkAccessible(CM_Frontend_Environment $environment) {
        if (!CM_Bootloader::getInstance()->isDebug()) {
            throw new CM_Exception_NotAllowed();
        }
    }

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $viewResponse->set('siteSettingsList', (new CM_Paging_SiteSettings_All())->getItems());
    }
}
