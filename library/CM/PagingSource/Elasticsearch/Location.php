<?php

class CM_PagingSource_Elasticsearch_Location extends CM_PagingSource_Elasticsearch {

    function __construct(CM_Elasticsearch_Query $query) {
        $elasticClient = CM_Service_Manager::getInstance()->getElasticsearch()->getClient();
        parent::__construct(new CM_Elasticsearch_Type_Location($elasticClient), $query, array('level', 'id'));
    }
}
