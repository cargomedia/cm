<?php

class CM_ComponentFrontendHandler {

	/**
	 * Javascript operations queue.
	 *
	 * @var array
	 */
	protected $operations = array();

	/**
	 * Add a property set action.
	 *
	 * @param string $property
	 * @param mixed $value
	 */
	public function __set($property, $value) {
		$this->setParam($property, $value);
	}

	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function setParam($property, $value) {
		$this->operations[] = "$property = " . CM_Params::encode($value, true);
	}

	/**
	 * Add a handler method call.
	 *
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, array $args) {
		foreach ($args as &$arg) {
			$arg = CM_Params::encode($arg, true);
		}
		$this->operations[] = "$method(" . implode(', ', $args) . ")";
	}
	
	/**
	* @param string $js
	*/
	public function exec($js) {
		$this->operations[] = $js;
	}

	/**
	 * Returns the javascript code of called operations.
	 *
	 * @param string $var_name
	 * @return string
	 */
	public function compile_js($var_name) {
		$js_code = '';
		foreach ($this->operations as $operation) {
			$js_code .= "$var_name.$operation;\n";
		}

		$this->auto_var = $var_name;

		return $js_code;
	}

	private $auto_var;

	/**
	 * The getter for $this->auto_var
	 *
	 * @return string
	 */
	public function auto_var() {
		return $this->auto_var;
	}

	/**
	 * Show a MACOS-like error message.
	 *
	 * @param string $msg_text
	 */
	public function error($msg_text) {
		$this->__call('error', array($msg_text));
	}

	/**
	 * Show a MACOS-like message.
	 *
	 * @param string $msg_text
	 */
	public function message($msg_text) {
		$this->__call('message', array($msg_text));
	}

	/**
	 * Debug a variable value.
	 *
	 * @param mixed $var
	 */
	public function debug($var) {
		$this->__call('debug', array($var));
	}
}
