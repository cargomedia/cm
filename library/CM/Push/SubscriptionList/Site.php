<?php

class CM_Push_SubscriptionList_Site extends CM_Push_SubscriptionList_Abstract {

    /**
     * @param CM_Site_Abstract $site
     */
    public function __construct(CM_Site_Abstract $site) {
        $where = 'site = ' . $site->getType();
        $source = new CM_PagingSource_Sql('id', CM_Push_Subscription::getTableName(), $where);
        $source->enableCache();
        parent::__construct($source);
    }
}
