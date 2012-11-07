<?php

class CM_Response_EmailTracking extends CM_Response_Abstract {

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'emailtracking';
	}

	protected function _process() {
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
		$this->_setContent(base64_decode('R0lGODlhAQABAIAAAP///////yH5BAEKAAEALAAAAAABAAEAAAICTAEAOw=='));
	}
}