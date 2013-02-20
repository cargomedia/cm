<?php

class CM_Usertext_Filter_CutWhitespace extends CM_Usertext_Filter_Abstract {

	public function transform($text) {
		$text = (string) $text;
		$text = preg_replace('/([\s])\1+/', ' ', $text);
		$text = str_replace(" \n", "\n", $text);
		$text = str_replace(' </p>', '</p>', $text);
		$text = trim($text, " \n\t");
		return $text;
	}

}
