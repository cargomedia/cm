<?php

abstract class CM_Paging_Language_Abstract extends CM_Paging_Abstract {

	protected function _processItem($item) {
		return new CM_Model_Language($item['id']);
	}

	/**
	 * @param string $abbreviation
	 * @return CM_Model_Language|null
	 */
	public function findByAbbreviation($abbreviation) {
		foreach ($this->getItemsRaw() as $language) {
			if ($abbreviation == $language['abbreviation']) {
				return new CM_Model_Language($language['id']);
			}
		}
		return null;
	}
}