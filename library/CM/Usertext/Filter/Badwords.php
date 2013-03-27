<?php

class CM_Usertext_Filter_Badwords implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$cacheKey = CM_CacheConst::Usertext_Filter_BadwordRegexp;
		if (($badwordsRegexp = CM_CacheLocal::get($cacheKey)) === false) {
			$badwords = array();
			foreach (new CM_Paging_ContentList_Badwords() as $badword) {
				$badword = preg_quote($badword, '#');
				$badword = str_replace('\*', '[^\s]*', $badword);
				$badwords[] = '\b' . $badword . '\b';
			}
			$badwordsRegexp = '#(' . implode('|', $badwords) . ')#i';
			CM_CacheLocal::set($cacheKey, $badwordsRegexp);
		}
		return preg_replace($badwordsRegexp, '…', $text);
	}
}
