<?php

class CM_StreamDelegate_Dummy implements CM_VideoStreamDelegate {

	public function onPublish(CM_VideoStream_Publish $streamPublish, CM_Params $params = null) {
	}

	public function onSubscribe(CM_VideoStream_Subscribe $streamSubscribe, CM_Params $params = null) {
	}

	public function onUnpublish(CM_VideoStream_Publish $streamPublish) {
	}

	public function onUnsubscribe(CM_VideoStream_Subscribe $streamSubscribe) {
	}

}
