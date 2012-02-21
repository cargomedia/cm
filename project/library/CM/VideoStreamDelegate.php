<?php

interface CM_VideoStreamDelegate {
	/**
	 * @param CM_VideoStream_Publish $streamPublish
     * @param array|null $data
	 * @return boolean
	 */
    public function onPublish(CM_VideoStream_Publish $streamPublish, array $data = null);

	/**
	 * @param CM_VideoStream_Subscribe $streamSubscribe
     * @param array|null $data
	 * @return boolean
	 */
	public function onSubscribe(CM_VideoStream_Subscribe $streamSubscribe, array $data = null);

	/**
	 * @param CM_VideoStream_Publish $streamPublish
	 */
	public function onUnpublish(CM_VideoStream_Publish $streamPublish);

	/**
	 * @param CM_VideoStream_Subscribe $streamSubscribe
	 */
	public function onUnsubscribe(CM_VideoStream_Subscribe $streamSubscribe);
}
