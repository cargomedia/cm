<?php

interface CM_Usertext_Filter_Interface {

	/**
	 * @param string $text
	 * @return string
	 */
	public function transform($text);

}
