<?php

interface CM_VideoStreamDelegate {
	/**
	 * @param CM_VideoStream_Publish $streamPublish
     * @param CM_Params|null $params
	 * @return boolean
	 */
    public function onPublish(CM_VideoStream_Publish $streamPublish, CM_Params $params = null);

	/**
	 * @param CM_VideoStream_Subscribe $streamSubscribe
     * @param CM_Params|null $params
	 * @return boolean
	 */
	public function onSubscribe(CM_VideoStream_Subscribe $streamSubscribe, CM_Params $params = null);

	/**
	 * @param CM_VideoStream_Publish $streamPublish
	 */
	public function onUnpublish(CM_VideoStream_Publish $streamPublish);

	/**
	 * @param CM_VideoStream_Subscribe $streamSubscribe
	 */
	public function onUnsubscribe(CM_VideoStream_Subscribe $streamSubscribe);
}
