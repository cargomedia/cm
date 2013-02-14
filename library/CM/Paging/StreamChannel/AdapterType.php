<?php

class CM_Paging_StreamChannel_AdapterType extends CM_Paging_StreamChannel_Abstract {

	/**
	 * @param int|int[] $adapterTypes
	 */
	public function __construct($adapterTypes) {
		$adapterTypes = (array) $adapterTypes;
		$source = new CM_PagingSource_Sql('`id`, `type`', TBL_CM_STREAMCHANNEL, '`adapterType` IN(' . implode(',', $adapterTypes) . ')');
		parent::__construct($source);
	}
}
