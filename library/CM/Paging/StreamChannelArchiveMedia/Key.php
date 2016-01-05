<?php

class CM_Paging_StreamChannelArchiveMedia_Key extends CM_Paging_StreamChannelArchiveMedia_Abstract {

    /**
     * @param string $key
     */
    public function __construct($key) {
        $source = new CM_PagingSource_Sql('id', 'cm_streamChannelArchive_media', '`key` = ?', 'createStamp DESC', null, null, [(string) $key]);
        parent::__construct($source);
    }
}
