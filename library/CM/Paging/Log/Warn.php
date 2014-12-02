<?php

class CM_Paging_Log_Warn extends CM_Paging_Log_Abstract {

    /**
     * @param string     $msg
     * @param array|null $metaInfo
     */
    public function add($msg, array $metaInfo = null) {
        $this->_add($msg, array_merge($this->_getMetaInfoFromRequest(), (array) $metaInfo));
    }
}
