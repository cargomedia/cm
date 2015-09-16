<?php

class CM_Elasticsearch_Index_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string|null $indexName
     * @param bool|null   $skipIfExist
     */
    public function create($indexName = null, $skipIfExist = null) {
        $typeList = $this->_getTypeList($indexName);
        foreach ($typeList as $type) {
            if (!$type->indexExists() || !$skipIfExist) {
                $this->_getStreamOutput()->writeln('Creating elasticsearch index `' . $type->getIndexName() . '`…');
                $type->createIndex();
                $type->refreshIndex();
            }
        }
    }

    /**
     * @param string|null $indexName
     */
    public function update($indexName = null) {
        $typeList = $this->_getTypeList($indexName);
        foreach ($typeList as $type) {
            $this->_getStreamOutput()->writeln('Updating elasticsearch index `' . $type->getIndexName() . '`…');
            $type->updateIndex();
        }
    }

    /**
     * @param string|null $indexName
     */
    public function delete($indexName = null) {
        $typeList = $this->_getTypeList($indexName);
        foreach ($typeList as $type) {
            if ($type->indexExists()) {
                $this->_getStreamOutput()->writeln('Deleting elasticsearch index `' . $type->getIndexName() . '`…');
                $type->deleteIndex();
            }
        }
    }

    public function optimize() {
        CM_Service_Manager::getInstance()->getElasticsearch()->getClient()->indices()->optimize(['index' => '_all']);
    }

    /**
     * @keepalive
     */
    public function startMaintenance() {
        $clockwork = new CM_Clockwork_Manager();
        $storage = new CM_Clockwork_Storage_FileSystem('search-maintenance');
        $storage->setServiceManager(CM_Service_Manager::getInstance());
        $clockwork->setStorage($storage);
        $clockwork->registerCallback('search-index-update', '1 minute', array($this, 'update'));
        $clockwork->registerCallback('search-index-optimize', '1 hour', array($this, 'optimize'));
        $clockwork->start();
    }

    /**
     * @param string|null $filterIndexName
     * @throws CM_Exception_Invalid
     * @return CM_Elasticsearch_Type_Abstract[]
     */
    protected function _getTypeList($filterIndexName = null) {
        $typeList = CM_Service_Manager::getInstance()->getElasticsearch()->getTypeList();

        if (null !== $filterIndexName) {
            $typeList = \Functional\filter($typeList, function (CM_Elasticsearch_Type_Abstract $type) use ($filterIndexName) {
                return $type->getIndexName() === $filterIndexName;
            });
            if (count($typeList) === 0) {
                throw new CM_Exception_Invalid('No type with such index name: ' . $filterIndexName);
            }
        }
        return $typeList;
    }

    public static function getPackageName() {
        return 'search-index';
    }
}
