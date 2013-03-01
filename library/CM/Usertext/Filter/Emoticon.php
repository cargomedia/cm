<?php

class CM_Usertext_Filter_Emoticon implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$emoticons = $this->_getEmoticonData($render);
		$text = str_replace($emoticons['codes'], $emoticons['htmls'], $text);
		return $text;
	}

	/**
	 * @param CM_Render $render
	 * @return array
	 */
	private function _getEmoticonData(CM_Render $render) {
		$cacheKey = CM_CacheConst::Usertext_Filter_EmoticonList;
		if (($emoticons = CM_CacheLocal::get($cacheKey)) === false) {
			$emoticons = array('codes' => array(), 'htmls' => array());
			foreach (new CM_Paging_Emoticon_All() as $emoticon) {
				foreach ($emoticon['codes'] as $code) {
					$emoticons['codes'][] = $code;
					$emoticons['htmls'][] =
							'<img src="' . $render->getUrlResource('img', 'emoticon/' . $emoticon['file']) . '" class="emoticon emoticon-' .
									$emoticon['id'] . '" title="' . $emoticon['code'] . '" />';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}
}
