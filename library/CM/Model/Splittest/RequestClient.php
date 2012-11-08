<?php
class CM_Model_Splittest_RequestClient extends CM_Model_Splittest {

	/**
	 * @param CM_Request_Abstract $requestClientId
	 * @param string|null         $variationName
	 * @return string
	 */
	public function getVariationFixture(CM_Request_Abstract $requestClient, $variationName = null) {
		return $this->_getVariationFixture($requestClient->getClientId(), $variationName);
	}

	/**
	 * @param CM_Request_Abstract $requestClientId
	 */
	public function setConversion(CM_Request_Abstract $requestClient) {
		$this->_setConversion($requestClient->getClientId());
	}

}