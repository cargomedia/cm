<?php

class CM_Config_Generator extends CM_Class_Abstract {

	/**
	 * @param CM_File $source
	 * @return string
	 */
	public function generateOutput(CM_File $source) {
		$entryList = CM_Params::decode($source->read(), true);
		$output = '<?php' . PHP_EOL;
		$mapping = $this->_getMapping();
		foreach ($entryList as $key => $value) {
			if (is_array($value)) {
				$keyMapped = $mapping->getConfigKey($key);
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
