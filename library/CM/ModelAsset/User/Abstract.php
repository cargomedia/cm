<?php

abstract class CM_ModelAsset_User_Abstract extends CM_ModelAsset_Abstract {

	/**
	 * @param CM_Model_User $user
	 */
	public function __construct(CM_Model_User $user) {
		parent::__construct($user);
	}

}
