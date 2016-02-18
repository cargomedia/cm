<?php

class CM_Paging_Log_Mail extends CM_Paging_Log {

    public function __construct($level, $aggregate = false, $ageMax = null) {
        parent::__construct($level, $aggregate, $ageMax, self::getTypeStatic());
    }
}
