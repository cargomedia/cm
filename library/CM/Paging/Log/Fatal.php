<?php

class CM_Paging_Log_Fatal extends CM_Paging_Log_Abstract {

    public function cleanUp() {
        $this->_deleteOlderThan(30 * 86400);
    }
}
