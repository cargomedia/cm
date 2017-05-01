<?php

class CM_MongoDb_SetupScript extends CM_Provision_Script_Abstract implements CM_Provision_Script_UnloadableInterface {

    use CM_Provision_Script_IsLoadedTrait;

    public function load(CM_OutputStream_Interface $output) {
        $mongoClient = $this->getServiceManager()->getMongoDb();

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

    public function unload(CM_OutputStream_Interface $output) {
        $this->getServiceManager()->getMongoDb()->dropDatabase();
    }

    public function reload(CM_OutputStream_Interface $output) {
        $mongoDb = $this->getServiceManager()->getMongoDb();
        foreach ($mongoDb->listCollectionNames() as $collectionName) {
            $mongoDb->deleteMany($collectionName);
        }
    }

    public function getRunLevel() {
        return 1;
    }

    protected function _isLoaded() {
        return $this->getServiceManager()->getMongoDb()->databaseExists();
    }
}
