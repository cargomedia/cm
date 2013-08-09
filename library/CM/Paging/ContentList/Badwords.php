<?php

class CM_Paging_ContentList_Badwords extends CM_Paging_ContentList_Abstract {

	const TYPE = 2;

	function __construct() {
		parent::__construct(self::TYPE);
	}

	/**
	 * @return null|string
	 */
	public function toRegex() {
		$regexList = array();
		foreach ($this as $badword) {
			$badword = preg_quote($badword, '#');
			$badword = str_replace('\*', '[^\s]*', $badword);
			$regexList[] = $badword;
		}

		if (empty($regexList)) {
			return '#\z.#';
		}

		return '#\b(?:' . implode('|', $regexList) . ')\b#i';
	}

	/**
	 * @return string[]
	 */
	public function toRegexList() {
		$regexList = array();
		foreach ($this as $badword) {
			$badwordRegex = preg_quote($badword, '#');
			$badwordRegex = str_replace('\*', '[^\s]*', $badwordRegex);
			$regexList[$badword] = '#\b' . $badwordRegex . '\b#i';
		}

		return $regexList;
	}
}
