<?php

class CM_StreamChannel_ThumbnailList_Channel extends CM_StreamChannel_ThumbnailList_Abstract {

    /**
     * @param int $channelId $streamChannelArchive
     */
    public function __construct($channelId) {
        $source = new CM_PagingSource_Sql('id', 'cm_streamchannel_thumbnail', 'channelId = ?', 'createStamp', null, null, [(int) $channelId]);
        $source->enableCache();
        parent::__construct($source);
    }
}
