<?php

class CM_Usertext_Filter_Emoticons extends CM_Usertext_Filter_Abstract {

	private $_strip;

	/**
	 * @param boolean|null $strip
	 */
	function __construct($strip = null) {
		$strip = $strip ? (boolean) $strip : null;
		$this->_strip = (boolean) $strip;
	}

	public function transform($text) {
		$text = (string) $text;
		$emoticons = $this->_getEmoticonData();
		if ($this->_strip) {
			$emoticons['htmls'] = '';
		}
		$text = str_replace($emoticons['codes'], $emoticons['htmls'], $text);
		return $text;
	}

	/**
	 * @return array
	 */
	private function _getEmoticonData() {
		$cacheKey = CM_CacheConst::Usertext_Filter_Emoticons;
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
