<?php

class CM_Response_EmailTracking extends CM_Response_Abstract {

	public function process() {
		$params = CM_Params::factory($this->_request->getQuery());
		try {
			$user = $params->getUser('user');
			$mailType = $params->getInt('mailType');

			$action = new SK_Action_Email(SK_Action_Abstract::VIEW, $user);
			$action->prepare();
			$action->notify($user, $mailType);
		} catch (CM_Exception_Nonexistent $e) {

		}

		$this->setHeader('Content-Type', 'image/gif');
		return base64_decode('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==');
	}
}