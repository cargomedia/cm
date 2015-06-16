<?php

class CM_Paging_Currency_Abstract extends CM_Paging_Abstract {

    protected function _processItem($item) {
        return new CM_Model_Currency($item);
    }

    /**
     * @param CM_Model_Currency $currency
     * @return boolean
     */
    public function contains(CM_Model_Currency $currency) {
        return in_array($currency->getId(), $this->getItemsRaw());
    }
}
