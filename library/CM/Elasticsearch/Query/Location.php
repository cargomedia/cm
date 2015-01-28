<?php

class CM_Elasticsearch_Query_Location extends CM_Elasticsearch_Query {

    /**
     * @param CM_Model_Location $location
     */
    public function filterLocation(CM_Model_Location $location) {
        $this->filterTerm('ids.' . $location->getLevel(), $location->getId());
    }

    /**
     * @param int $levelMin
     * @param int $levelMax OPTIONAL
     */
    public function filterLevel($levelMin, $levelMax = null) {
        $levelMax = $levelMax ? (int) $levelMax : null;
        $this->filterRange('level', (int) $levelMin, $levelMax);
    }

    /**
     * @param string $term
     */
    public function queryName($term) {
        $subquery = new CM_Elasticsearch_Query_Location();
        $subquery->queryMatch('name', $term, array(
            'operator' => 'or',
            'analyzer' => 'standard',
        ));
        $subquery->queryMatch('nameFull', $term, array(
            'operator' => 'and',
            'analyzer' => 'standard',
        ));
        $this->query($subquery);
    }

    /**
     * @param string $term
     */
    public function queryNameSuggestion($term) {
        $subquery = new CM_Elasticsearch_Query_Location();
        $subquery->queryMatch('name.prefix', $term, array(
            'operator' => 'or',
            'analyzer' => 'standard',
        ));
        $subquery->queryMatch('nameFull.prefix', $term, array(
            'operator' => 'and',
            'analyzer' => 'standard',
        ));
        $this->query($subquery);
    }

    public function sortLevel() {
        $this->_sort(array('level' => 'asc'));
    }

    /**
     * @param CM_Model_Location $location
     */
    public function sortDistance(CM_Model_Location $location) {
        $this->sortGeoDistance('coordinates', $location);
    }
}
