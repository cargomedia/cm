<?php

abstract class CM_Usertext_Filter_Abstract extends CM_Class_Abstract {

	/**
	 * @param string $text
	 * @return string
	 */
	abstract public function transform($text);

}
