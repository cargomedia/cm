<?php

class CM_Paging_StreamChannelArchiveMedia_Type extends CM_Paging_StreamChannelArchiveMedia_Abstract {

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
        $source = new CM_PagingSource_Sql('id', 'cm_streamChannelArchive_media', $where, 'createStamp DESC');
        parent::__construct($source);
    }
}
