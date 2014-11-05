<?php

class CM_MongoDb_SetupScript extends CM_Provision_Script_Abstract {

    public function load(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $mongoClient = $manager->getMongoDb();

        foreach (CM_Util::getResourceFiles('mongo/collections.json') as $dump) {
            $collectionInfo = CM_Params::jsonDecode($dump->read());
            foreach ($collectionInfo as $collection => $indexes) {
                $mongoClient->createCollection($collection);
                foreach ($indexes as $indexInfo) {
                    $mongoClient->createIndex($collection, $indexInfo['key'], $indexInfo['options']);
                }
            }
        }
    }

    public function unload(CM_Service_Manager $manager, CM_OutputStream_Interface $output) {
        $manager->getMongoDb()->dropDatabase();
    }

    public function isLoaded(CM_Service_Manager $manager) {
        $hasCollections = count($manager->getMongoDb()->listCollectionNames()) > 0;
        return $hasCollections;
    }

    public function getRunLevel() {
        return 1;
    }
}
