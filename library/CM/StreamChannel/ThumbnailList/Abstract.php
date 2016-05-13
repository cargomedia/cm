<?php

abstract class CM_StreamChannel_ThumbnailList_Abstract extends CM_Paging_Abstract {

    protected function _processItem($itemRaw) {
        return new CM_StreamChannel_Thumbnail($itemRaw);
    }

}
