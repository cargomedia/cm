<?php

class CM_Paging_StreamSubscribe_AdapterType extends CM_Paging_StreamSubscribe_Abstract {

	/**
	 * @param int $adapterType
	 */
	public function __construct($adapterType) {
		$adapterType = (int) $adapterType;
		$source = new CM_PagingSource_Sql(TBL_CM_STREAM_SUBSCRIBE . '.`id`', TBL_CM_STREAM_SUBSCRIBE, '`adapterType` = ' . $adapterType, null, 'JOIN ' . TBL_CM_STREAMCHANNEL . ' ON ' . TBL_CM_STREAM_SUBSCRIBE . '.channelId = ' . TBL_CM_STREAMCHANNEL . '.id');
		return parent::__construct($source);
	}
}
