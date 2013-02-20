<?php

class CM_Usertext_Filter_Badwords extends CM_Usertext_Filter_Abstract {

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text) {
		$text = (string) $text;
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

}
