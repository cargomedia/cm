<?php

class CM_Cache_Runtime extends CM_Cache_Abstract {
	protected static $_instance;

	protected function _getName() {
		return 'Runtime';
	}

	protected function _set($key, $data, $lifeTime = null) {
		return false;
	}

	protected function _get($key) {
		return false;
	}

	protected function _delete($key) {
		return false;
	}

	protected function _flush() {
		return false;
	}

}
