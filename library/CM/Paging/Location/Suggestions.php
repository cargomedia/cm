<?php

class CM_Paging_Location_Suggestions extends CM_Paging_Location_Abstract {

    /**
     * @param string                 $term
     * @param int                    $minLevel
     * @param int                    $maxLevel
     * @param CM_Model_Location|null $location
     */
    public function __construct($term, $minLevel, $maxLevel, CM_Model_Location $location = null) {
        $term = (string) $term;
        $minLevel = (int) $minLevel;
        $maxLevel = (int) $maxLevel;
        $query = new CM_Elasticsearch_Query_Location();
        $query->filterLevel($minLevel, $maxLevel);
        if (strlen($term) > 0) {
            $query->queryNameSuggestion($term);
        }
        $query->sortLevel();
        if ($location) {
            $query->sortDistance($location);
        }
        $query->sortScore();

        $source = new CM_PagingSource_Elasticsearch_Location($query);
        $source->enableCacheLocal();

        parent::__construct($source);
    }
}
