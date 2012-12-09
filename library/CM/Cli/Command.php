<?php

class CM_Cli_Command {

	/** @var ReflectionClass */
	private $_class;

	/** @var ReflectionMethod */
	private $_method;

	/**
	 * @param ReflectionMethod $method
	 * @param ReflectionClass  $class
	 */
	public function __construct(ReflectionMethod $method, ReflectionClass $class) {
		$this->_method = $method;
		$this->_class = $class;
	}

	/**
	 * @param CM_Cli_Arguments    $arguments
	 * @param CM_Output_Interface $output
	 * @throws CM_Cli_Exception_InvalidArguments
	 */
	public function run(CM_Cli_Arguments $arguments, CM_Output_Interface $output) {
		$parameters = $arguments->extractMethodParameters($this->_method);
		if ($arguments->getNumeric()->getAll()) {
			throw new CM_Cli_Exception_InvalidArguments('Too many arguments provided');
		}
		if ($named = $arguments->getNamed()->getAll()) {
			throw new CM_Cli_Exception_InvalidArguments('Illegal option used: `--' . key($named) . '`');
		}
		call_user_func_array(array($this->_class->newInstance($output), $this->_method->getName()), $parameters);
	}

	/**
	 * @return string
	 */
	public function getHelp() {
		$helpText = $this->getPackageName() . ' ' . $this->_getMethodName();
		foreach ($this->_getRequiredParameters() as $paramName) {
			$helpText .= ' <' . CM_Util::uncamelize($paramName) . '>';
		}
		$helpText .=  PHP_EOL;
		$optionalParameters = $this->_getOptionalParameters();
		if ($optionalParameters) {
			foreach ($optionalParameters as $paramName => $defaultValue) {
				$paramName = CM_Util::uncamelize($paramName);
				$helpText .= '   --' . str_pad($paramName, max(strlen($paramName) + 5, 20), ' ') . $this->_getParamDoc($paramName) . PHP_EOL;
			}
		}
		return $helpText;
	}

	/**
	 * @param string $packageName
	 * @param string $methodName
	 * @return bool
	 */
	public function match($packageName, $methodName) {
		$methodMatched = ($methodName === $this->_getMethodName());
		$packageMatched = ($packageName === $this->getPackageName());
		return ($packageMatched && $methodMatched);
	}

	/**
	 * @return bool
	 */
	public function isAbstract() {
		return $this->_method->getDeclaringClass()->isAbstract();
	}

	/**
	 * @return string
	 */
	public function getPackageName() {
		return $this->_class->getMethod('getPackageName')->invoke(null);
	}

	/**
	 * @return string[]
	 */
	protected function _getRequiredParameters() {
		$params = array();
		foreach ($this->_method->getParameters() as $param) {
			if (!$param->isOptional()) {
				$params[] = $param->getName();
			}
		}
		return $params;
	}

	/**
	 * @return array
	 */
	protected function _getOptionalParameters() {
		$params = array();
		foreach ($this->_method->getParameters() as $param) {
			if ($param->isOptional()) {
				$params[$param->getName()] = $param->getDefaultValue();
			}
		}
		return $params;
	}

	/**
	 * @param string $paramName
	 * @return string|null
	 */
	private function _getParamDoc($paramName) {
		$methodDocComment = $this->_method->getDocComment();
		if (!preg_match('/\*\s+@param\s+[^\$]*\s*\$' . preg_quote($paramName) . '\s*([^@\*]*)/', $methodDocComment, $matches)) {
			return null;
		}
		return trim($matches[1]);
	}

	private function _setOutput(CM_Output_Interface $output) {
		$this->_output = $output;
		CM_Filesystem::getInstance()->setOutput($output);
	}

	/**
	 * @return string
	 */
	private function _getMethodName() {
		return CM_Util::uncamelize($this->_method->getName());
	}

}