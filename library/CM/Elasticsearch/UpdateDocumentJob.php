<?php

class CM_Elasticsearch_UpdateDocumentJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $indexClassName = $params->getString('indexClassName');
        $id = $params->getString('id');
        $elasticClient = CM_Service_Manager::getInstance()->getElasticsearch()->getClient();

        /** @var CM_Elasticsearch_Type_Abstract $index */
        $index = new $indexClassName($elasticClient);

        $index->updateDocuments(array($id));
        $index->refreshIndex();
    }
}
