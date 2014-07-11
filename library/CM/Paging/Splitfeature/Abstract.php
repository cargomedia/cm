<?php

abstract class CM_Paging_Splitfeature_Abstract extends CM_Paging_Abstract {

    /**
     * @param string $name
     * @return CM_Model_Splitfeature|null
     */
    public function find($name) {
        if (!in_array($name, $this->getItemsRaw())) {
            return null;
        }
        return CM_Model_Splitfeature::factory($name);
    }

    /**
     * @param string $itemRaw
     * @return CM_Model_Splitfeature
     */
    protected function _processItem($itemRaw) {
        return CM_Model_Splitfeature::factory($itemRaw);
    }
}
