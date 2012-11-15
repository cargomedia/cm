<?php
class CM_Model_Splittest_Dummy extends CM_Model_Splittest {

	const TYPE = 29;

	public function __construct() {
	}

	/**
	 * @param CM_Model_User    $user
	 * @param string          $variationName
	 * @param string|null             $forceVariationName
	 * @return bool
	 */
	public function isVariationFixture(CM_Model_User $user, $variationName, $forceVariationName = null) {
		return true;
	}

	/**
	 * @param CM_Model_User $user
	 */
	public function setConversion($user) {
	}

}