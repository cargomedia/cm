<?php

class CM_Push_SubscriptionList_All extends CM_Push_SubscriptionList_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Sql('id', CM_Push_Subscription::getTableName());
        $source->enableCache();
        parent::__construct($source);
    }
}
