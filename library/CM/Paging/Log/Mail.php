<?php

class CM_Paging_Log_Mail extends CM_Paging_Log {

    public function __construct(array $levelList, $aggregate = false, $ageMax = null) {
        parent::__construct($levelList, $aggregate, $ageMax, self::getTypeStatic());
    }
}
