<?php

abstract class CM_Paging_ModelAbstract extends CM_Paging_Abstract {

	/** @var array */
	protected $_modelList;

	/**
	 * @return int
	 * @throws CM_Exception_NotImplemented
	 */
	protected function _getModelType() {
		throw new CM_Exception_NotImplemented();
	}

	protected function _onLoadItemsRaw(array $itemsRaw) {
		$modelList = CM_Model_Abstract::factoryGenericMultiple($itemsRaw, $this->_getModelType());
		$this->_modelList = array();
		foreach ($itemsRaw as $index => $itemRaw) {
			$this->_modelList[serialize($itemRaw)] = $modelList[$index];
		}
	}

	protected function _processItem($itemRaw) {
		$index = serialize($itemRaw);
		if (null === ($model = $this->_modelList[$index])) {
			throw new CM_Exception_Nonexistent('Model type:`' . $this->_getModelType() . '` id: `' . CM_Util::var_line($itemRaw) . '` has no data.');
		}
		return $model;
	}

}
