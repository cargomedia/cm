<?php

class CM_Dom_NodeList {

	/**
	 * @param string $html
	 * @throws CM_Exception_Invalid
	 */
	public function __construct($html) {
		$doc = new DOMDocument();

		try {
			$doc->loadHTML($html);
		} catch(ErrorException $e) {
			throw new CM_Exception_Invalid('Cannot load html');
		}
	}
}
