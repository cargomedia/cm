<?php

interface CM_Usertext_Filter_Interface {

	/**
	 * @param string    $text
	 * @param CM_Render $render
	 * @return string
	 */
	public function transform($text, CM_Render $render);
}
