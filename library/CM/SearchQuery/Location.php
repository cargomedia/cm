<?php

class CM_SearchQuery_Location extends CM_SearchQuery_Abstract {

	/**
	 * @param int $levelMin
	 * @param int $levelMax OPTIONAL
	 */
	public function filterLevel($levelMin, $levelMax = null) {
		$levelMax = $levelMax ? (int) $levelMax : null;
		$this->filterRange('level', (int) $levelMin, $levelMax);
	}
	
	/**
	 * @param string $value
	 */
	public function filterNamePrefix($value) {
		$this->filterPrefix('name', mb_strtolower($value));
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
