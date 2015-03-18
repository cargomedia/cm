<?php

class CM_Elasticsearch_Index_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string|null $indexName
     * @param bool|null   $skipIfExist
     */
    public function create($indexName = null, $skipIfExist = null) {
        $elasticsearchCluster = CM_Service_Manager::getInstance()->getElasticsearch();
        if (null !== $indexName) {
            $types = [$elasticsearchCluster->getTypeByIndexName($indexName)];
        } else {
            $types = $elasticsearchCluster->getTypes();
        }
        foreach ($types as $type) {
            if (!$type->indexExists() || !$skipIfExist) {
                $this->_getStreamOutput()->writeln('Creating elasticsearch index `' . $type->getIndex()->getName() . '`…');
                $elasticsearchCluster->createIndex($type, $skipIfExist);
            }
        }
    }

    /**
     * @param string|null $indexName
     */
    public function update($indexName = null) {
        $elasticsearchCluster = CM_Service_Manager::getInstance()->getElasticsearch();
        if (null !== $indexName) {
            $types = [$elasticsearchCluster->getTypeByIndexName($indexName)];
        } else {
            $types = $elasticsearchCluster->getTypes();
        }
        foreach ($types as $type) {
            $this->_getStreamOutput()->writeln('Updating elasticsearch index `' . $type->getIndex()->getName() . '`...');
            $elasticsearchCluster->updateIndex($type);
        }
    }

    /**
     * @param string|null $indexName
     */
    public function delete($indexName = null) {
        $elasticsearchCluster = CM_Service_Manager::getInstance()->getElasticsearch();
        if (null !== $indexName) {
            $types = [$elasticsearchCluster->getTypeByIndexName($indexName)];
        } else {
            $types = $elasticsearchCluster->getTypes();
        }
        foreach ($types as $type) {
            if ($type->indexExists()) {
                $this->_getStreamOutput()->writeln('Deleting elasticsearch index `' . $type->getIndex()->getName() . '`…');
                $elasticsearchCluster->deleteIndex($type);
            }
        }
    }

    public function optimize() {
        foreach (CM_Service_Manager::getInstance()->getElasticsearch()->getClientList() as $client) {
            $client->optimizeAll();
        }
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

    public static function getPackageName() {
        return 'search-index';
    }
}
