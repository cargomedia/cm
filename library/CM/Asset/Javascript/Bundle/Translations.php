<?php

class CM_Asset_Javascript_Bundle_Translations extends CM_Asset_Javascript_Bundle_Abstract {

    /** @var CM_Model_Language */
    protected $_language;

    /**
     * CM_Asset_Javascript_Bundle_Translations constructor.
     * @param CM_Model_Language $language
     * @param CM_Site_Abstract  $site
     * @param bool|null         $sourceMapsOnly
     */
    public function __construct(CM_Model_Language $language, CM_Site_Abstract $site, $sourceMapsOnly = null) {
        $this->_language = $language;
        parent::__construct($site, $sourceMapsOnly);
        $this->_js->addInlineContent($this->_getModuleName(), $this->_getTranslations());
        $this->_js->setIgnoreMissing(true);
        $this->_js->addSourceMapping([
            '/App/' => '(^|.*/)App/'
        ]);
    }

    /**
     * @return string
     */
    protected function _getTranslations() {
        $translations = array();
        foreach ($this->_language->getTranslations(true) as $translation) {
            $translations[$translation['key']] = $this->_language->getTranslation($translation['key']);
        }
        return join("\n", [
            'var cm = require("/App/init");',
            'cm.language.setAll(' . CM_Util::jsonEncode($translations, true) . ');'
        ]);
    }

    /**
     * @return string
     */
    protected function _getModuleName() {
        return '/App/translations/' . $this->_language->getAbbreviation();
    }

    protected function _getBundleName() {
        return 'translations.' . $this->_language->getAbbreviation() . '.js';
    }
}
