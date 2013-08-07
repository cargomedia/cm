<?php

class CM_Paging_ContentList_Badwords extends CM_Paging_ContentList_Abstract {

	const TYPE = 2;

	function __construct() {
		parent::__construct(self::TYPE);
	}

	/**
	 * @param string $badword
	 * @return string
	 */
	public function transformToRegexp($badword) {
		$badword = preg_quote($badword, '#');
		$badword = str_replace('\*', '[^\s]*', $badword);

		return $badword;
	}
}
