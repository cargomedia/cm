<?php

class CM_Elasticsearch_UpdateDocumentJob extends CM_Jobdistribution_Job_Abstract {

    protected function _execute(CM_Params $params) {
        $indexClassName = $params->getString('indexClassName');
        $id = $params->getString('id');

        /** @var CM_Elastica_Type_Abstract $index */
        $index = new $indexClassName();

        $index->update($id);
        $index->getIndex()->refresh();
    }
}
