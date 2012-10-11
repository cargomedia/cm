<?php

class CM_Paging_StreamChannelArchiveVideo_Type extends CM_Paging_StreamChannelArchiveVideo_Abstract {

	/**
	 * @param int      $type
	 * @param int|null $createStampMax
	 */
	public function __construct($type, $createStampMax = null) {
		$type = (int) $type;
		$where = '`streamChannelType` = ' . $type;
		if (!is_null($createStampMax)) {
			$createStampMax = (int) $createStampMax;
			$where .= ' AND `createStamp` <= ' . $createStampMax;
		}
		$source = new CM_PagingSource_Sql('id', TBL_CM_STREAMCHANNELARCHIVE_VIDEO, $where);
		parent::__construct($source);
	}
}