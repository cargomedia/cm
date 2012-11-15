<?php
class CM_Model_Splittest_RequestClient extends CM_Model_Splittest {

	const TYPE = 26;

	/**
	 * @param CM_Request_Abstract $request
	 * @param string              $variationName
	 * @param string|null         $forceVariationName
	 * @return bool
	 */
	public function isVariationFixture(CM_Request_Abstract $request, $variationName, $forceVariationName = null) {
		return $this->_isVariationFixture($request->getClientId(), $variationName, $forceVariationName);
	}

	/**
	 * @param CM_Request_Abstract $request
	 * @param string|null         $forceVariationName
	 * @return string
	 */
	public function getVariationFixture(CM_Request_Abstract $request, $forceVariationName = null) {
		return $this->_getVariationFixture($request->getClientId(), $forceVariationName);
	}

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function setConversion(CM_Request_Abstract $request) {
		$this->_setConversion($request->getClientId());
	}

}