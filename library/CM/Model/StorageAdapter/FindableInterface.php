<?php

interface CM_Model_StorageAdapter_FindableInterface {

    /**
     * @param int   $type
     * @param array $data
     * @return int|null
     */
    public function findByData($type, array $data);
}
