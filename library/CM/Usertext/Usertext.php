<?php

class CM_Usertext_Usertext extends CM_Class_Abstract {
	private $_text;

	function __construct($text) {
		$this->_text = (string) $text;
	}

	/**
	 * @param boolean $lengthMax
	 * @param boolean $stripEmoji
	 * @return string
	 */
	public function getMarkdown($lengthMax = null, $stripEmoji = null) {
		$text = $this->_text;
		$text = $this->_escape($text);

		$text = $this->_applyBadwords($text);
		if ($lengthMax) {
			$text = $this->_applyMaxLength($text, $lengthMax);
		}
		$text = $this->_applyEmoji($text, $stripEmoji);
		$text = $this->_applyMarkdown($text);
		$text = $this->_cutWhitespace($text);

		return $text;
	}

	/**
	 * @param int     $lengthMax
	 * @param boolean $preserveParagraph
	 * @param boolean $preserveEmoji
	 * @return string
	 */
	public function getPlain($lengthMax = null, $preserveParagraph = null, $preserveEmoji = null) {
		$text = $this->_text;
		$text = $this->_escape($text);

		$text = $this->_applyBadwords($text);
		if ($lengthMax) {
			$text = $this->_applyMaxLength($text, $lengthMax);
		}
		$text = $this->_applyMarkdown($text);

		$allowedTags = null;
		if ($preserveParagraph) {
			$search = array('<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>');
			$text = str_replace($search, '<p>', $text);
			$search = array('</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>');
			$text = str_replace($search, '</p>', $text);
			$allowedTags = '<p>';
		}

		$text = strip_tags($text, $allowedTags);

		if ($preserveEmoji) {
			$text = $this->_applyEmoji($text, null);
		} else {
			$text = $this->_applyEmoji($text, true);
		}

		$text = $this->_cutWhitespace($text);

		return $text;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function _applyMarkdown($text) {
		$markdownParser = new CM_Usertext_Markdown();
		return $markdownParser::defaultTransform($text);
	}

	/**
	 * @param string $text
	 * @param int    $lengthMax
	 * @return string
	 */
	private function _applyMaxLength($text, $lengthMax) {
		if (strlen($text) > $lengthMax) {
			$text = substr($text, 0, $lengthMax);

			$lastBlank = strrpos($text, ' ');
			if ($lastBlank > 0) {
				$text = substr($text, 0, $lastBlank);
			}
			$text = $text . '…';
		}
		return $text;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function _applyBadwords($text) {
		$cacheKey = CM_CacheConst::Usertext_Badwords;
		if (($badwords = CM_CacheLocal::get($cacheKey)) === false) {
			$badwords = array('search' => array(), 'replace' => '…');
			foreach (new CM_Paging_ContentList_Badwords() as $badword) {
				$badword = preg_quote($badword, '#');
				$badword = str_replace('\*', '[^\s]*', $badword);
				$badwords['search'][] = '#(\b' . $badword . '\b)#i';
			}

			CM_CacheLocal::set($cacheKey, $badwords);
		}
		return preg_replace($badwords['search'], $badwords['replace'], $text);
	}

	/**
	 * @param string  $text
	 * @param boolean $stripEmoji
	 * @return string
	 */
	private function _applyEmoji($text, $stripEmoji) {
		$emoticons = $this->_getEmoticonData();
		if (null === $stripEmoji) {
			$text = str_replace($emoticons['codes'], $emoticons['htmls'], $text);
		} else {
			if (true === $stripEmoji) {
				$text = str_replace($emoticons['codes'], '', $text);
			}
		}
		return $text;
	}

	/**
	 * @return array
	 */
	private function _getEmoticonData() {
		$cacheKey = CM_CacheConst::Usertext_Emoticons;
		if (($emoticons = CM_CacheLocal::get($cacheKey)) === false) {
			$emoticons = array('codes' => array(), 'htmls' => array());
			foreach (new CM_Paging_Smiley_All() as $smiley) {
				foreach ($smiley['codes'] as $key => $code) {
					$emoticons['codes'][] = $code;
					$emoticons['htmls'][] =
							'<img class="emoji" title="' . $code . '" alt="' . $code . '" src="/img/emoji/' . $smiley['path'] . '" />';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	private function _cutWhitespace($text) {
		$text = preg_replace('/([\s])\1+/', ' ', $text);
		$text = str_replace(" \n", "\n", $text);
		$text = str_replace(' </p>', '</p>', $text);
		$text = trim($text, " \n\t");
		return $text;
	}

	/**
	 * @param string $text
	 * @param string $char_set
	 * @return string
	 */
	private function _escape($text, $char_set = 'UTF-8') {
		return htmlspecialchars($text, ENT_QUOTES, $char_set);
	}

}
