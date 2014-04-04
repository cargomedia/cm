<?php

class CM_Paging_Log_Fatal extends CM_Paging_Log_Abstract {

    /**
     * @param string $msg
     * @param array  $metaInfo
     */
    public function add($msg, array $metaInfo) {
        $this->_add($msg, array_merge($this->_getMetafInfoFromRequest(), $metaInfo));
    }
}
