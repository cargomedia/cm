<?php

class CM_Paging_Location_SearchText extends CM_Paging_Location_Abstract {

    /**
     * @param string                 $term
     * @param int                    $minLevel
     * @param int                    $maxLevel
     * @param CM_Model_Location|null $locationSort
     * @param CM_Model_Location|null $locationScope
     */
    public function __construct($term, $minLevel, $maxLevel, CM_Model_Location $locationSort = null, CM_Model_Location $locationScope = null) {
        $term = (string) $term;
        $minLevel = (int) $minLevel;
        $maxLevel = (int) $maxLevel;
        $query = new CM_Elasticsearch_Query_Location();
        $query->filterLevel($minLevel, $maxLevel);
        if (strlen($term) > 0) {
            $query->queryName($term);
        }
        if ($locationScope) {
            $query->filterLocation($locationScope);
        }
        $query->sortLevel();
        if ($locationSort) {
            $query->sortDistance($locationSort);
        }
        $query->sortScore();

        $source = new CM_PagingSource_Elasticsearch_Location($query);
        $source->enableCacheLocal();

        parent::__construct($source);
    }
}
