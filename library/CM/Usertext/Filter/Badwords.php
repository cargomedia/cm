<?php

class CM_Usertext_Filter_Badwords implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$badwordList = new CM_Paging_ContentList_Badwords;

		do {
			$textOld = $text;
			$text = $badwordList->replaceMatch($text);
		} while ($textOld !== $text);

		return $text;
	}
}
