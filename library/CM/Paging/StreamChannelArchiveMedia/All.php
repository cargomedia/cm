<?php

class CM_Paging_StreamChannelArchiveMedia_All extends CM_Paging_StreamChannelArchiveMedia_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Sql('id', 'cm_streamChannelArchive_video', null, '`createStamp` DESC');

        parent::__construct($source);
    }
}
