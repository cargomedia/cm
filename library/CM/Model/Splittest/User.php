<?php
class CM_Model_Splittest_User extends CM_Model_Splittest {

	const TYPE = 27;

	/**
	 * @param CM_Model_User	$user
	 * @param  string|null	$variationName
	 * @return string
	 */
	public function getVariationFixture(CM_Model_User $user, $variationName = null) {
		return $this->_getVariationFixture($user->getId(), $variationName);
	}

	/**
	 * @param CM_Model_User $user
	 */
	public function setConversion($user) {
		$this->_setConversion($user->getId());
	}

}