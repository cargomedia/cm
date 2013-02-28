<?php

class CM_Usertext_Filter_Emoticon implements CM_Usertext_Filter_Interface {

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
		$cacheKey = CM_CacheConst::Usertext_Filter_EmoticonList;
		if (($emoticons = CM_CacheLocal::get($cacheKey)) === false) {
			$emoticons = array('codes' => array(), 'htmls' => array());
			foreach (new CM_Paging_Emoticon_All() as $emoticon) {
				foreach ($emoticon['codes'] as $code) {
					$emoticons['codes'][] = $code;
					$emoticons['htmls'][] =
							'<span class="emoticon emoticon-' . $emoticon['id'] . '" title="' .
									CM_Util::htmlspecialchars($emoticon['name']) . '"></span>';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}
}
