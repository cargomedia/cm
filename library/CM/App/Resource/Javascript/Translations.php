<?php

class CM_App_Resource_Javascript_Translations extends CM_App_Resource_Javascript_Abstract {

	/**
	 * @param CM_Model_Language $language
	 */
	public function __construct(CM_Model_Language $language) {
		$translations = array();
		foreach (new CM_Paging_Translation_Language($language, null, null, null, true) as $translation) {
			$translations[$translation['key']] = $language->getTranslation($translation['key']);
		}
		$this->_content = 'cm.language.setAll(' . CM_Params::encode($translations, true) . ');';
	}
}
