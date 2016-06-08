<?php

abstract class CM_Model_Bound extends CM_Model_Abstract {

    protected function _onDeleteAfter() {
        CM_Model_StreamChannel_Message_Model::create($this->getType())->notify('DELETE', $this);
    }

    protected function _onChange() {
        CM_Model_StreamChannel_Message_Model::create($this->getType())->notify('UPDATE', $this);
    }

    protected function _onCreate() {
        CM_Model_StreamChannel_Message_Model::create($this->getType())->notify('CREATE', $this);
    }
}
