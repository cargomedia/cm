<?php

class CM_PagingSource_Sql_Deferred extends CM_PagingSource_Sql {
	protected $_dbSlave = true;

	public function getStalenessChance() {
		return 0.1;
	}
}
