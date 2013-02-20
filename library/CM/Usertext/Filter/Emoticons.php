<?php

class CM_Usertext_Filter_Emoticons extends CM_Usertext_Filter_Abstract {

	public function transform($text) {
		$text = (string) $text;
		$emoticons = $this->_getEmoticonData();
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
							'<img class="emoticon" title="' . $code . '" alt="' . $code . '" src="/img/emoticons/' . $smiley['path'] . '" />';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}

}
