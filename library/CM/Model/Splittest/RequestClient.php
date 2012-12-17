<?php
class CM_Model_Splittest_RequestClient extends CM_Model_Splittest {

	const TYPE = 26;

	/**
	 * @param CM_Request_Abstract $request
	 * @param string              $variationName
	 * @return bool
	 */
	public function isVariationFixture(CM_Request_Abstract $request, $variationName) {
		return $this->_isVariationFixture($request->getClientId(), $variationName);
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @return string
	 */
	public function getVariationFixture(CM_Request_Abstract $request) {
		return $this->_getVariationFixture($request->getClientId());
	}

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function setConversion(CM_Request_Abstract $request) {
		$this->_setConversion($request->getClientId());
	}

}