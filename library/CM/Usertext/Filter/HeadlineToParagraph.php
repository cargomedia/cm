<?php

class CM_Usertext_Filter_HeadlineToParagraph extends CM_Usertext_Filter_Abstract {

	public function transform($text) {
		$text = (string) $text;
		$search = array('<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '<h6>');
		$text = str_replace($search, '<p>', $text);
		$search = array('</h1>', '</h2>', '</h3>', '</h4>', '</h5>', '</h6>');
		$text = str_replace($search, '</p>', $text);
		return $text;
	}

}
