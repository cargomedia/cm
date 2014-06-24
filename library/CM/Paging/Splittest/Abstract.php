<?php

abstract class CM_Paging_Splittest_Abstract extends CM_Paging_Abstract {

    /**
     * @param string $name
     * @return bool
     */
    public function contains($name) {
        return in_array($name, $this->getItemsRaw());
    }

    protected function _processItem($itemRaw) {
        return new CM_Model_Splittest($itemRaw);
    }
}
