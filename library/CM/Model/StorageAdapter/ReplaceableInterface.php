<?php

interface CM_Model_StorageAdapter_ReplaceableInterface {

    /**
     * @param int   $type
     * @param array $data
     * @return array
     */
    public function replace($type, array $data);
}
