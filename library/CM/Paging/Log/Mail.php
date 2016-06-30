<?php

class CM_Paging_Log_Mail extends CM_Paging_Log {

    public function __construct(array $filterLevelList, $aggregate = false, $ageMax = null) {
        parent::__construct($filterLevelList, self::getTypeStatic(), $aggregate, $ageMax);
    }
}
