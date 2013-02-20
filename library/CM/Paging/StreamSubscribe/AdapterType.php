<?php

class CM_Paging_StreamSubscribe_AdapterType extends CM_Paging_StreamSubscribe_Abstract {

	/**
	 * @param int $adapterType
	 */
	public function __construct($adapterType) {
		$source = new CM_PagingSource_Sql(TBL_CM_STREAM_SUBSCRIBE . '.`id`', TBL_CM_STREAM_SUBSCRIBE, '`adapterType` = ' . $adapterType, null, 'JOIN ' . TBL_CM_STREAMCHANNEL . ' ON ' . TBL_CM_STREAM_SUBSCRIBE . '.channelId = ' . TBL_CM_STREAMCHANNEL . '.id');
		$source->enableCache();
		return parent::__construct($source);
	}

	/**
	 * @param string $key
	 * @return CM_Model_Stream_Subscribe|null
	 */
	public function findKey($key) {
		$key = (string) $key;
		/** @var CM_Model_Stream_Subscribe $streamSubscribe */
		foreach ($this as $streamSubscribe) {
			if ($streamSubscribe->getKey() == $key) {
				return $streamSubscribe;
			}
		}
		return null;
	}
}
