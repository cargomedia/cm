<?php

class CM_PagingSource_Elasticsearch_Location extends CM_PagingSource_Elasticsearch {

    function __construct(CM_Elasticsearch_Query $query) {
        parent::__construct(new CM_Elasticsearch_Type_Location(), $query, array('level', 'id'));
    }
}
