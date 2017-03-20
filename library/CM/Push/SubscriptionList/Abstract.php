<?php

abstract class CM_Push_SubscriptionList_Abstract extends CM_Paging_Abstract {

    protected function _processItem($itemRaw) {
        return new CM_Push_Subscription($itemRaw);
    }
}
