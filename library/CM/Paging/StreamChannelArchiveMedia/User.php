<?php

class CM_Paging_StreamChannelArchiveMedia_User extends CM_Paging_StreamChannelArchiveMedia_Abstract {

    /**
     * @param CM_Model_User $user
     */
    public function __construct(CM_Model_User $user) {
        $source = new CM_PagingSource_Sql('id', 'cm_streamChannelArchive_media', '`userId` = ' . $user->getId(), 'createStamp DESC');
        parent::__construct($source);
    }
}
