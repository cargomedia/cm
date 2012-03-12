<?php

class CM_Paging_StreamChannel_Type extends CM_Paging_StreamChannel_Abstract {

	/**
	 * @param array int[] $types
	 */
	public function __construct(array $types) {
		$source = new CM_PagingSource_Sql('`id`', TBL_CM_STREAMCHANNEL, '`type` IN(' . implode(',', $types) . ')');
		parent::__construct($source);
	}
}
