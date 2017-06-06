<?php

abstract class CM_Paging_SplittestVariation_Abstract extends CM_Paging_Abstract {

    protected function _processItem($itemRaw) {
        return new CM_Model_SplittestVariation($itemRaw);
    }

    /**
     * @param string $name
     * @return CM_Model_SplittestVariation|null
     */
    public function findByName($name) {
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->getItems() as $variation) {
            if ($variation->getName() == $name) {
                return $variation;
            }
        }
        return null;
    }

    /**
     * @param string $name
     * @return CM_Model_SplittestVariation
     * @throws CM_Exception_Nonexistent
     */
    public function getByName($name) {
        $variation = $this->findByName($name);
        if (!$variation) {
            throw new CM_Exception_Nonexistent('Variation not found', null, ['name' => $name]);
        }
        return $variation;
    }
}
