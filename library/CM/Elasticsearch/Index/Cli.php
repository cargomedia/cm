<?php

class CM_Elasticsearch_Index_Cli extends CM_Cli_Runnable_Abstract {

    /**
     * @param string|null $indexName
     * @param bool|null $skipIfExist
     */
    public function create($indexName = null, $skipIfExist = null) {
        $types = $this->_getTypes($indexName);
        foreach ($types as $type) {
            if (!$type->indexExists() || !$skipIfExist) {
                $this->_getStreamOutput()->writeln('Creating elasticsearch index `' . $type->getIndex()->getName() . '`…');
                $type->createIndex();
                $type->refreshIndex();
            }
        }
    }

    /**
     * @param string|null $indexName
     */
    public function update($indexName = null) {
        $types = $this->_getTypes($indexName);
        foreach ($types as $type) {
            $this->_getStreamOutput()->writeln('Updating elasticsearch index `' . $type->getIndex()->getName() . '`...');
            $type->updateIndex();
        }
    }

    /**
     * @param string|null $indexName
     */
    public function delete($indexName = null) {
        $types = $this->_getTypes($indexName);
        foreach ($types as $type) {
            if ($type->indexExists()) {
                $this->_getStreamOutput()->writeln('Deleting elasticsearch index `' . $type->getIndex()->getName() . '`…');
                $type->deleteIndex();
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
        $clockwork->registerCallback('search-index-update', '1 minute', function () {
            $this->update();
        });
        $clockwork->registerCallback('search-index-optimize', '1 hour', function () {
            $this->optimize();
        });
        $clockwork->start();
    }

    /**
     * @param string|null $filterIndexName
     * @throws CM_Exception_Invalid
     * @return CM_Elasticsearch_Type_Abstract[]
     */
    protected function _getTypes($filterIndexName = null) {
        $types = CM_Service_Manager::getInstance()->getElasticsearch()->getTypes();

        if (null !== $filterIndexName) {
            $types = \Functional\filter($types, function (CM_Elasticsearch_Type_Abstract $type) use ($filterIndexName) {
                return $type->getIndex()->getName() === $filterIndexName;
            });
            if (count($types) === 0) {
                throw new CM_Exception_Invalid('No type with such index name: ' . $filterIndexName);
            }
        }
        return $types;
    }

    public static function getPackageName() {
        return 'search-index';
    }
}
