<?php

class CM_Config_Mapping extends CM_Class_Abstract {

	/**
	 * @param $key
	 * @return string
	 * @throws CM_Exception_Invalid
	 */
	public function getConfigKey($key) {
		$mapping = $this->_getMapping();
		if (!array_key_exists($key, $mapping)) {
			throw new CM_Exception_Invalid('There is no mapping for `' . $key . '`');
		}
		return $mapping[$key];
	}

	/**
	 * @return array
	 */
	protected function _getMapping() {
		return array(
			'foo' => 'CM_Foo',
			'foobar' => 'CM_FooBar'
		);
	}

	/**
	 * @return CM_Config_Mapping
	 */
	public static function factory() {
		$className = self::_getClassName();
		return new $className();
	}

}
