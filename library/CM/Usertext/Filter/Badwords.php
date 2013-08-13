<?php

class CM_Usertext_Filter_Badwords implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$cacheKey = CM_CacheConst::Usertext_Filter_BadwordRegexp;
		if (false == ($badwordsRegex = CM_CacheLocal::get($cacheKey))) {
			$badwordList = new CM_Paging_ContentList_Badwords;
			$badwordsRegex = $badwordList->toRegex();
			CM_CacheLocal::set($cacheKey, $badwordsRegex);
		}
		if (!$badwordsRegex) {
			return $text;
		}

		do {
			$textOld = $text;
			$text = preg_replace($badwordsRegex, '…', $text);
		} while ($textOld !== $text);

		return $text;
	}
}
