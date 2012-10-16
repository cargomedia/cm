<?php

class CM_Paging_StreamChannelArchiveVideo_All extends CM_Paging_StreamChannelArchiveVideo_Abstract {

	public function __construct() {
		$source = new CM_PagingSource_Sql('id', TBL_CM_STREAMCHANNELARCHIVE_VIDEO, null, '`createStamp` DESC');

		parent::__construct($source);
	}

}
