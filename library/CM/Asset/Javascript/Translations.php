<?php

class CM_Asset_Javascript_Translations extends CM_Asset_Javascript_Abstract {

    /**
     * @param CM_Site_Abstract  $site
     * @param bool|null         $debug
     * @param CM_Model_Language $language
     */
    public function __construct(CM_Site_Abstract $site, $debug = null, CM_Model_Language $language) {
        parent::__construct($site, $debug);

        $translations = array();
        foreach ($language->getTranslations(true) as $translation) {
            $translations[$translation['key']] = $language->getTranslation($translation['key']);
        }
        $this->_js->append('cm.language.setAll(' . CM_Params::encode($translations, true) . ');');
    }
}
