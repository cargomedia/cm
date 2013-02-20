<?php

class CM_Usertext_Filter_Escape extends CM_Usertext_Filter_Abstract {

	public function transform($text) {
		$text = (string) $text;
		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}

}
