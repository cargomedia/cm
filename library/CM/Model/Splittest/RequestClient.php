<?php
class CM_Model_Splittest_RequestClient extends CM_Model_Splittest {

	const TYPE = 26;

	/**
	 * @param CM_Request_Abstract $request
	 * @param string|null         $variationName
	 * @return string
	 */
	public function getVariationFixture(CM_Request_Abstract $request, $variationName = null) {
		return $this->_getVariationFixture($request->getClientId(), $variationName);
	}

	/**
	 * @param CM_Request_Abstract $request
	 */
	public function setConversion(CM_Request_Abstract $request) {
		$this->_setConversion($request->getClientId());
	}

}