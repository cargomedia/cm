<?php

class CM_Usertext_Filter_CutWhitespace implements CM_Usertext_Filter_Interface {

	public function transform($text, CM_Render $render) {
		$text = (string) $text;
		$text = str_replace("\r", '', $text);
		$text = str_replace("\t", " ", $text);
		$text = preg_replace('# {1,}#', ' ', $text);
		$text = str_replace(array(" \n", ' </p>', ' <br />'), array("\n", '</p>', '<br />'), $text);
		$text = trim($text);
		return $text;
	}

}
