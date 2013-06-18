<?php

abstract class CM_Asset_Abstract extends CM_Class_Abstract {

	/**
	 * @param boolean|null $compress
	 * @return string
	 */
	abstract public function get($compress = null);
}
