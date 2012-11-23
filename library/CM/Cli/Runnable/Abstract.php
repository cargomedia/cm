<?php

abstract class CM_Cli_Runnable_Abstract {

	/**
	 * @throws CM_Exception_NotImplemented
	 * @return string
	 */
	public static function getPackageName() {
		throw new CM_Exception_NotImplemented('Package `' . get_called_class() . '` has no `getPackageName` implemented.');
	}

	/**
	 * @return string
	 */
	public function info() {
		$details = array(
			'Package name' => static::getPackageName(),
			'Class name' => get_class($this),
		);
		$output = '';
		foreach ($details as $name => $value) {
			$output .= str_pad($name . ':', 20) . $value . PHP_EOL;
		}
		return $output;
	}

	/**
	 * @param string $value
	 */
	protected function _echo($value) {
		// TODO: Implement more complex CM_OutputStream_Abstract related echoing
		echo $value . PHP_EOL;
	}
}