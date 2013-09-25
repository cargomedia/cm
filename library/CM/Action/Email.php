<?php

class CM_Action_Email extends CM_Action_Abstract {

	const TYPE = 6;

	/**
	 * @param CM_Model_User $user
	 * @param int           $mailType
	 */
	public function notify(CM_Model_User $user, $mailType) {
		$this->_notify($user, $mailType);
	}

	protected function _prepare() {
	}
}
