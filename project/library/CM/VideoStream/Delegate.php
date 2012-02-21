<?php

interface CM_VideoStream_Delegate {
	/**
	 * @param CM_VideoStream_Publish $stream
	 * @return boolean
	 */
    public function onPublish(CM_VideoStream_Publish $stream);

	/**
	 * @param CM_VideoStream_Subscribe $stream
	 * @return boolean
	 */
	public function onSubscribe(CM_VideoStream_Subscribe $stream);

	/**
	 * @param CM_VideoStream_Publish $stream
	 */
	public function onUnpublish(CM_VideoStream_Publish $stream);

	/**
	 * @param CM_VideoStream_Subscribe $stream
	 */
	public function onUnsubscribe(CM_VideoStream_Subscribe $stream);

    /**
     * @param CM_VideoStream_Publish $stream
     */
    public function inPublishAllowed(CM_VideoStream_Publish $stream);

    /**
     * @param CM_VideoStream_Subscribe $stream
     */
    public function onSubscribeAlloewd(CM_VideoStream_Subscribe $stream);

}
