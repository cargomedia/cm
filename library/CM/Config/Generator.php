<?php

class CM_Config_Generator extends CM_Class_Abstract {

	/** @var CM_File */
	private $_source = null;

	/**
	 * @param CM_File $source
	 */
	public function __construct(CM_File $source) {
		$this->_source = $source;
	}

	/**
	 * @return string
	 */
	public function generateOutput() {
		$entryList = CM_Params::decode($this->_source->read(), true);
		$output = '<?php' . PHP_EOL;
		$mapping = $this->_getMapping();
		foreach ($entryList as $key => $value) {
			if (is_array($value)) {
				$keyMapped = $mapping->getConfigKey($key);
				$output .= PHP_EOL;
				$output .= 'if (!isset($config->' . $keyMapped . ')) {' . PHP_EOL;
				$output .= '	$config->' . $keyMapped . ' = new stdClass();' . PHP_EOL;
				$output .= '}' . PHP_EOL;
				foreach ($value as $subKey => $subValue) {
					$output .= '$config->' . $keyMapped . '->' . $subKey . ' = ' . var_export($subValue, true) . ';' . PHP_EOL;
				}
			} else {
				$output .= '$config->' . $key . ' = ' . var_export($value, true) . ';' . PHP_EOL;
			}
		}
		return $output;
	}

	/**
	 * @return CM_Config_Mapping
	 */
	protected function _getMapping() {
		return new CM_Config_Mapping();
	}
}
