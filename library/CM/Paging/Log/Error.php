<?php

class CM_Paging_Log_Error extends CM_Paging_Log_Abstract {

    /**
     * @param string     $msg
     * @param array|null $metaInfo
     */
    public function add($msg, array $metaInfo = null) {
        $metaInfo = array_merge((array) $metaInfo, $this->_getDefaultMetaInfo());
        $this->_add($msg, $metaInfo);
    }
}
