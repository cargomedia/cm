<?php

class CM_Paging_ContentList_Badwords extends CM_Paging_ContentList_Abstract {

	const TYPE = 2;

	function __construct() {
		parent::__construct(self::TYPE);
	}

	public function toRegex() {
		$regexList = array();
		foreach ($this as $badword) {
			$badword = preg_quote($badword, '#');
			$badword = str_replace('\*', '[^\s]*', $badword);
			$regexList[] = $badword;
		}
		$regex = null;
		if ($regexList) {
			$regex = '#\b(?:' . implode('|', $regexList) . ')\b#i';
		}

		return $regex;
	}

	public function toRegexList() {
		$regexList = array();
		foreach ($this as $badword) {
			$badwordRegex = preg_quote($badword, '#');
			$badwordRegex = str_replace('\*', '[^\s]*', $badwordRegex);
			$regexList[$badword] = '#' . $badwordRegex . '#i';
		}

		return $regexList;
	}
}
