<?php

class CM_Model_SmileySet extends CM_Model_Abstract {

	protected function _loadData() {
		return CM_Mysql::exec("SELECT * FROM TBL_CM_SMILEYSET WHERE id=?", $this->getId())->fetchAssoc();
	}

	/**
	 * @return string
	 */
	public function getLabel() {
		return $this->_get('label');
	}

	/**
	 * @return CM_Paging_Smiley_Set
	 */
	public function getSmileys() {
		return new CM_Paging_Smiley_Set($this->getId());
	}
}
