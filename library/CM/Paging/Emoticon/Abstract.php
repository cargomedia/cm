<?php

abstract class CM_Paging_Emoticon_Abstract extends CM_Paging_Abstract {

    protected function _processItem($itemRaw) {
        return new CM_Emoticon($itemRaw['name'], $itemRaw);
    }
}
