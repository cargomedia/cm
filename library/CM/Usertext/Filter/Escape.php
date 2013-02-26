<?php

class CM_Usertext_Filter_Escape implements CM_Usertext_Filter_Interface {

	public function transform($text) {
		$text = (string) $text;
		return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
	}

}
