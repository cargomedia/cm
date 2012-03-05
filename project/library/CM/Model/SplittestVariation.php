<?php

class CM_Model_SplittestVariation extends CM_Model_Abstract {
	CONST TYPE = 17;

	/**
	 * @return string
	 */
	public function getName() {
		return $this->_get('name');
	}

	/**
	 * @return bool
	 */
	public function getEnabled() {
		return (bool) $this->_get('enabled');
	}

	/**
	 * @param bool $state
	 */
	public function setEnabled($state) {
		$state = (bool) $state;
		CM_Mysql::update(TBL_CM_SPLITTESTVARIATION, array('enabled' => $state), array('id' => $this->getId()));
		$this->_change();
	}

	/**
	 * @return CM_Model_Splittest
	 */
	public function getSplittest() {
		$splittestId = (int) $this->_get('splittestId');
		return new CM_Model_Splittest($splittestId);
	}

	protected function _loadData() {
		return CM_Mysql::select(TBL_CM_SPLITTESTVARIATION, '*', array('id' => $this->getId()))->fetchAssoc();
	}
}
