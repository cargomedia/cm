<?php

abstract class CM_Paging_Bound extends CM_Paging_Abstract implements CM_ArrayConvertible, CM_Typed {

    public function toArrayIdOnly() {
        return array('_type' => $this->getType());
    }

    public function toArray() {
        $array = $this->toArrayIdOnly();
        $streamChannel = $this->_getStreamChannel();
        $array['items'] = $this->getItems();
        $array['streamChannel'] = $streamChannel->getDefinition();
        return $array;
    }

    protected function _processItem($itemId) {
        return new CM_Model_Example_Todo($itemId);
    }

    public static function fromArray(array $array) {
        throw new CM_Exception('Not supported.');
    }

    /**
     * @return CM_Model_StreamChannel_Message_Model
     */
    abstract protected function _getStreamChannel();
}
