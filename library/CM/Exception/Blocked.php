<?php

class CM_Exception_Blocked extends CM_Exception {
	
	public function __construct(CM_Model_User $user) {
		parent::__construct($user->getDisplayName() . ' has blocked you.', true);
	}
}
