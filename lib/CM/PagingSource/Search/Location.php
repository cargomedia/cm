<?php

class CM_PagingSource_Search_Location extends CM_PagingSource_Search {

	function __construct(CM_SearchQuery_Abstract $query) {
		parent::__construct(SK_Search::INDEX_LOCATION, SK_Search::INDEX_LOCATION, $query, array('level', 'id'));
	}
}
