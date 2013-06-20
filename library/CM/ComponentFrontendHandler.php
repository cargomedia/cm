<?php

class CM_ComponentFrontendHandler {

	/**
	 * Javascript operations queue.
	 *
	 * @var array
	 */
	protected $operations = array();

	/**
	 * @param string $property
	 * @param mixed $value
	 */
	public function setParam($property, $value) {
		$this->operations[] = "$property = " . CM_Params::encode($value, true);
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
	 * @param string $msg_text
	 */
	public function error($msg_text) {
		$this->operations[] = "error(" . CM_Params::encode($msg_text, true) . ")";
	}

	/**
	 * @param string $msg_text
	 */
	public function message($msg_text) {
		$this->operations[] = "message(" . CM_Params::encode($msg_text, true) . ")";

	}

	/**
	 * @param mixed $varList
	 */
	public function debug($varList) {
		foreach ($varList as &$var) {
			$var = CM_Params::encode($var, true);
		}
		$this->operations[] = "message(" . implode(', ', $varList) . ")";
	}
}
