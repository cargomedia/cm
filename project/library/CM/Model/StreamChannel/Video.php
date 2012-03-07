<?php

class CM_Model_StreamChannel_Video extends CM_Model_StreamChannel_Abstract {
	const TYPE = 17;

	public function canPublish(CM_Model_User $user) {
		if (!$user->getRoles()->contains(SK_Role::PREMIUM)) {
			return false;
		}
		return true;
	}

	public function canSubscribe(CM_Model_User $user) {
		//check wallet
		return true;
	}

	public function onPublish(CM_Model_Stream_Publish $streamPublish, CM_Params $params = null) {
	}

	public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe, CM_Params $params = null) {

	}

	public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
	}

	public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
	}
}
