<?php

class CM_Paging_Location_Suggestions extends CM_Paging_Location_Abstract {

	/**
	 * @param string	  $term
	 * @param int		 $minLevel
	 * @param CM_Model_Location $location OPTIONAL
	 */
	function __construct($term, $minLevel, CM_Model_Location $location = null) {
		if (CM_Search::getEnabled()) {
			$query = new CM_SearchQuery_Location();
			$query->filterLevel((int) $minLevel);
			$query->filterNamePrefix($term);
			$query->sortLevel();
			if ($location) {
				$query->sortDistance($location);
			}
			$source = new CM_PagingSource_Search_Location($query);
		} else {
			$where = CM_Mysql::placeholder("level >= ? AND `name` LIKE '?'", $minLevel, '%' . $term . '%');
			$source = new CM_PagingSource_Sql_Deferred('level,id', TBL_CM_TMP_LOCATION, $where, 'level');
		}
		$source->enableCacheLocal();

		parent::__construct($source);
	}
}
