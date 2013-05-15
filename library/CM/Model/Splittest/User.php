<?php

class CM_Model_Splittest_User extends CM_Model_Splittest {

	const TYPE = 27;

	/**
	 * @param CM_Model_User $user
	 * @param string        $variationName
	 * @return bool
	 */
	public function isVariationFixture(CM_Model_User $user, $variationName) {
		return $this->_isVariationFixture(new CM_Splittest_Fixture($user), $variationName);
	}

	/**
	 * @param CM_Model_User $user
	 * @param float|null    $weight
	 */
	public function setConversion($user, $weight = null) {
		$this->_setConversion(new CM_Splittest_Fixture($user), $weight);
	}
}
