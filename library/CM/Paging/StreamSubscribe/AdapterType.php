<?php

class CM_Paging_StreamSubscribe_AdapterType extends CM_Paging_StreamSubscribe_Abstract {

	/**
	 * @param int $adapterType
	 */
	public function __construct($adapterType) {
		$adapterType = (int) $adapterType;
		$source = new CM_PagingSource_Sql('`cm_stream_subscribe`.`id`', 'cm_stream_subscribe', '`adapterType` = ' . $adapterType, null,
				'JOIN `cm_streamChannel` ON `cm_stream_subscribe`.`channelId` = `cm_streamChannel`.`id`');
		return parent::__construct($source);
	}
}
