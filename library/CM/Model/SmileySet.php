<?php

class CM_Model_SmileySet extends CM_Model_Abstract {

	CONST TYPE = 15;

	protected function _loadData() {
		return CM_Db_Db::exec("SELECT * FROM TBL_CM_SMILEYSET WHERE id = ?", array($this->getId()))->fetch();
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
