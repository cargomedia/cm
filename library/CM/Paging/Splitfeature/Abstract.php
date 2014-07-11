<?php

abstract class CM_Paging_Splitfeature_Abstract extends CM_Paging_Abstract {

    /**
     * @param string $name
     * @return CM_Model_Splitfeature|null
     */
    public function find($name) {
        if (!$this->contains($name)) {
            return null;
        }
        return CM_Model_Splitfeature::factory($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function contains($name) {
        return in_array($name, $this->getItemsRaw());
    }

    /**
     * @param string $itemRaw
     * @return CM_Model_Splitfeature
     */
    protected function _processItem($itemRaw) {
        return CM_Model_Splitfeature::factory($itemRaw);
    }
}
