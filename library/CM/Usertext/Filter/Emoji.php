<?php

class CM_Usertext_Filter_Emoji extends CM_Usertext_Filter_Abstract {

	private $_stripEmoji;

	/**
	 * @param boolean $stripEmoji
	 */
	function __construct($stripEmoji = null) {
		$this->_stripEmoji = (boolean) $stripEmoji;
	}

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text) {
		$text = (string) $text;
		$emoticons = $this->_getEmoticonData();
		if (null === $this->_stripEmoji) {
			$text = str_replace($emoticons['codes'], $emoticons['htmls'], $text);
		} else {
			$text = str_replace($emoticons['codes'], '', $text);
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
				foreach ($smiley['codes'] as $code) {
					$emoticons['codes'][] = $code;
					$emoticons['htmls'][] =
							'<img class="emoji" title="' . $code . '" alt="' . $code . '" src="/img/emoji/' . $smiley['path'] . '" />';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}

}
