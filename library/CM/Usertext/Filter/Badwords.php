<?php

class CM_Usertext_Filter_Badwords implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$cacheKey = CM_CacheConst::Usertext_Filter_BadwordRegexp;
		if (($badwordsRegexp = CM_CacheLocal::get($cacheKey)) === false) {
			$badwordsRegexpList = array();
			foreach (new CM_Paging_ContentList_Badwords() as $badword) {
				$badword = preg_quote($badword, '#');
				$badword = str_replace('\*', '[^\s]*', $badword);
				$badwordsRegexpList[] = $badword;
			}
			$badwordsRegexp = null;
			if ($badwordsRegexpList) {
				$badwordsRegexp = '#\b(?:' . implode('|', $badwordsRegexpList) . ')\b#i';
			}
			CM_CacheLocal::set($cacheKey, $badwordsRegexp);
		}
		if (!$badwordsRegexp) {
			return $text;
		}
		return preg_replace($badwordsRegexp, '…', $text);
	}
}
