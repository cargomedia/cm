<?php

class CM_Paging_Location_Suggestions extends CM_Paging_Location_Abstract {

	/**
	 * @param string $term
	 * @param int $minLevel
	 * @param CM_Location $location OPTIONAL
	 */
	function __construct($term, $minLevel, CM_Location $location = null) {
		$query = new SK_SearchQuery_Location();
		$query->filterLevel((int) $minLevel);
		$query->filterNamePrefix($term);
		$query->sortLevel();
		if ($location) {
			$query->sortDistance($location);
		}
		$source = new CM_PagingSource_Search_Location($query);
		$source->enableCacheLocal();

		parent::__construct($source);
	}
}
