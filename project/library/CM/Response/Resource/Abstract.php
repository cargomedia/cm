<?php

abstract class CM_Response_Resource_Abstract extends CM_Response_Abstract {

	/**
	 * @var string[]
	 */
	private $_pathParts;

	/**
	 * @param CM_Request_Abstract $request
	 * @param int                 $siteId OPTIONAL
	 */
	public function __construct(CM_Request_Abstract $request, $siteId = null) {
		parent::__construct($request, $siteId);
		$path = explode('/', $request->getPath());
		$this->_pathParts = array_filter(array_splice($path, 4), function ($dir) {
			return '..' != $dir;
		});
	}

	/**
	 * @param int|null $partNumber
	 * @return string
	 */
	protected function _getPath($partNumber = null) {
		if (null === $partNumber) {
			return implode('/', $this->_pathParts);
		}
		if (isset($this->_pathParts[$partNumber])) {
			return $this->_pathParts[$partNumber];
		}
		return null;
	}
}
