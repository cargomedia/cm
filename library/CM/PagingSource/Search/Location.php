<?php

class CM_PagingSource_Search_Location extends CM_PagingSource_Search {

	function __construct(CM_SearchQuery_Abstract $query) {
		parent::__construct(CM_Search::INDEX_LOCATION, CM_Search::INDEX_LOCATION, $query, array('level', 'id'));
	}
}
