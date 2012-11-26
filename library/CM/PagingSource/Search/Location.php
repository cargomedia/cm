<?php

class CM_PagingSource_Search_Location extends CM_PagingSource_Search {

	function __construct(CM_SearchQuery_Abstract $query) {
		parent::__construct(CM_Elastica_Type_Location::INDEX_NAME, CM_Elastica_Type_Location::INDEX_NAME, $query, array('level', 'id'));
	}
}
