<?php

abstract class CM_Paging_FileUserContent_Abstract extends CM_Paging_Abstract {

	/**
	 * @param mixed $item
	 * @return string
	 */
	abstract protected function _getFilename($item);

	/**
	 * @param mixed $item
	 * @return string
	 */
	abstract protected function _getFileNamespace($item);

	/**
	 * @param mixed $item
	 * @return int
	 */
	abstract protected function _getSequence($item);

	protected function _processItem($item) {
		return new CM_File_UserContent($this->_getFileNamespace($item), $this->_getFilename($item), $this->_getSequence($item));
	}

}
