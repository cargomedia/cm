<?php

class CM_Paging_Location_Suggestions extends CM_Paging_Location_Abstract {

	/**
	 * @param string                 $term
	 * @param int                    $minLevel
	 * @param int                    $maxLevel
	 * @param CM_Model_Location|null $location
	 */
	function __construct($term, $minLevel, $maxLevel, CM_Model_Location $location = null) {
		$minLevel = (int) $minLevel;
		$maxLevel = (int) $maxLevel;
		if (CM_Search::getInstance()->getEnabled()) {
			$query = new CM_SearchQuery_Location();
			$query->filterLevel($minLevel, $maxLevel);
			$query->filterNamePrefix($term);
			$query->sortLevel();
			if ($location) {
				$query->sortDistance($location);
			}
			$source = new CM_PagingSource_Search_Location($query);
		} else {
			$source = new CM_PagingSource_Sql_Deferred('level,id', 'cm_tmp_location',
				"level >= ? AND level <= ? AND `name` LIKE ?", 'level', array($minLevel, $maxLevel, '%' . $term . '%'));
		}
		$source->enableCacheLocal();

		parent::__construct($source);
	}
}
