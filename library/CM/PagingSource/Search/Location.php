<?php

class CM_PagingSource_Search_Location extends CM_PagingSource_Search {

    function __construct(CM_SearchQuery_Abstract $query) {
        parent::__construct(new CM_Elasticsearch_Type_Location(), $query, array('level', 'id'));
    }
}
