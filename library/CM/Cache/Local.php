<?php

class CM_Cache_Local extends CM_Cache_Abstract {

	public static function getInstance() {
		static $instance;
		if (!$instance) {
			$instance = new CM_Cache_Local();
		}
		return $instance;
	}
}
