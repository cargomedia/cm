<?php

interface CM_VideoStreamDelegate {
	/**
	 * @param CM_VideoStream_Publish $stream
	 * @param array $data
	 */
	public function onPublish(CM_VideoStream_Publish $stream, array $data);

	/**
	 * @param CM_VideoStream_Subscribe $stream
	 * @param array $data
	 */
	public function onSubscribe(CM_VideoStream_Subscribe $stream, array $data);

	/**
	 * @param CM_VideoStream_Publish $stream
	 * @param array $data
	 */
	public function onUnpublish(CM_VideoStream_Publish $stream, array $data);

	/**
	 * @param CM_VideoStream_Subscribe $stream
	 * @param array $data
	 */
	public function onUnsubscribe(CM_VideoStream_Subscribe $stream, array $data);

	/**
	 * @param CM_VideoStream_Publish $stream
	 * @param array $data
	 */
	public function onPublishAllowed(CM_VideoStream_Publish $stream, array $data);

	/**
	 * @param CM_VideoStream_Subscribe $stream
	 * @param array $data
	 */
	public function onSubscribeAllowed(CM_VideoStream_Subscribe $stream, array $data);

}
