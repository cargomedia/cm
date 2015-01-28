<?php

interface CM_Model_StorageAdapter_FindableInterface {

    /**
     * @param int   $type
     * @param array $data
     * @return array|null
     */
    public function findByData($type, array $data);
}
