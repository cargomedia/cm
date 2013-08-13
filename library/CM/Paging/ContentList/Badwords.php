<?php

class CM_Paging_ContentList_Badwords extends CM_Paging_ContentList_Abstract {

	const TYPE = 2;

	function __construct() {
		parent::__construct(self::TYPE);
	}

	/**
	 * @return string
	 */
	public function toRegex() {
		if (0 === $this->getCount()) {
			return '#\z.#';
		}

		$regexList = array();
		foreach ($this as $badword) {
			$badword = preg_quote($badword, '#');
			$badword = str_replace('\*', '[^A-Za-z]*', $badword);
			$regexList[] = '\S*' . $badword . '\S*';
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
			$badwordRegex = str_replace('\*', '[^A-Za-z]*', $badwordRegex);
			$regexList[$badword] = '#\b\S*' . $badwordRegex . '\S*\b#i';
		}

		return $regexList;
	}
}
