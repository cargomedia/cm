<?php

class CM_Paging_ContentList_Badwords extends CM_Paging_ContentList_Abstract {

	const TYPE = 2;

	function __construct() {
		parent::__construct(self::TYPE);
	}

	/**
	 * @param string $userInput
	 * @return bool
	 */
	public function isMatch($userInput) {
		if (preg_match($this->_toRegex(), (string) $userInput)) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $userInput
	 * @return string|false
	 */
	public function getMatch($userInput) {
		if (!$this->isMatch($userInput)) {
			return false;
		}
		$userInput = (string) $userInput;
		foreach ($this->getItems() as $badword) {
			$regexp = $this->_transformItemToRegex($badword);
			if (preg_match('#' . $regexp . '#i', $userInput)) {
				return $this->_transformItemToHumanreadable($badword);
			}
		}

		return false;
	}

	/**
	 * @param string $userInput
	 * @param string $replacementString
	 * @return string
	 */
	public function replaceMatch($userInput, $replacementString) {
		$userInput = (string) $userInput;
		$replacementString = str_replace('$', '\\$', str_replace('\\', '\\\\', (string) $replacementString));

		do {
			$userInputOld = $userInput;
			$userInput = preg_replace($this->_toRegex(), $replacementString, $userInput);
		} while ($userInputOld !== $userInput);

		return $userInput;
	}

	public function _change() {
		parent::_change();
		CM_Cache::delete(CM_CacheConst::ContentList_BadwordRegex);
	}

	/**
	 * @param string $badword
	 * @return string
	 */
	private function _transformItemToRegex($badword) {
		$regexp = preg_quote($badword, '#');
		$regexp = str_replace('\*', '[^A-Za-z]*', $regexp);
		$regexp = str_replace('\|', '\b', $regexp);
		$regexp = '\S*' . $regexp . '\S*';
		return $regexp;
	}

	/**
	 * @param string $badword
	 * @return mixed
	 */
	private function _transformItemToHumanreadable($badword) {
		return str_replace(array('*', '|'), '', $badword);
	}

	/**
	 * @return string
	 */
	private function _toRegex() {
		$cacheKey = CM_CacheConst::ContentList_BadwordRegex;
		if (false == ($badwordsRegex = CM_Cache::get($cacheKey))) {
			if ($this->isEmpty()) {
				$badwordsRegex = '#\z.#';
			} else {
				$regexList = array();
				foreach ($this as $badword) {
					$regexList[] = $this->_transformItemToRegex($badword);
				}
				$badwordsRegex = '#(?:' . implode('|', $regexList) . ')#i';
			}
			CM_Cache::set($cacheKey, $badwordsRegex);
		}

		return $badwordsRegex;
	}
}
