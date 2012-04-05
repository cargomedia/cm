<?php

class CM_FormField_Tags extends CM_FormField_Text {
	/**
	 * Constructor.
	 *
	 * @param string $name
	 */
	public function __construct($name = 'tags') {
		parent::__construct($name);
	}

	public function validate($userInput) {
		$userInput = preg_split('/[,\s\n\r]+/', $userInput);
		foreach ($userInput as &$tag) {
			$tag = strip_tags($tag);
			$tag = mb_strtolower($tag);
			$tag = trim($tag);
			$tag = preg_replace('/[^\d\p{L}]/u', '', $tag);
			$tag = substr($tag, 0, 20);
		}
		$userInput = array_filter($userInput);
		$userInput = array_unique($userInput);

		return array_slice($userInput, 0, 20);
	}
	
}
