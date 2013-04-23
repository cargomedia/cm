<?php

class CM_Usertext_Filter_Emoticon implements CM_Usertext_Filter_Interface {

	/** @var int $_fixedHeight */
	private $_fixedHeight;

	/**
	 * @param int|null $fixedHeight
	 */
	function __construct($fixedHeight = null) {
		$this->_fixedHeight = (int) $fixedHeight;
	}

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
		$cacheKey = CM_CacheConst::Usertext_Filter_EmoticonList . '_fixedHeight:' . $this->_fixedHeight;
		if (($emoticons = CM_CacheLocal::get($cacheKey)) === false) {
			$emoticons = array('codes' => array(), 'htmls' => array());
			$fixedHeight = $this->_fixedHeight ? ' height="' . $this->_fixedHeight . '"' : null;
			foreach (new CM_Paging_Emoticon_All() as $emoticon) {
				foreach ($emoticon['codes'] as $code) {
					$emoticons['codes'][] = $code;
					$emoticons['htmls'][] =
							'<img src="' . $render->getUrlResource('layout', 'img/emoticon/' . $emoticon['file']) . '" class="emoticon emoticon-' .
									$emoticon['id'] . '" title="' . $emoticon['code'] . '"' . $fixedHeight . ' />';
				}
			}
			CM_CacheLocal::set($cacheKey, $emoticons);
		}
		return $emoticons;
	}
}
