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
	 * @param CM_Cli_Arguments $arguments
	 * @throws CM_Cli_Exception_InvalidArguments
	 * @return string
	 */
	public function run(CM_Cli_Arguments $arguments) {
		$parameters = array();
		foreach ($this->_method->getParameters() as $param) {
			$parameters[] = $this->_getParamValue($param, $arguments);
		}
		if ($arguments->getNumeric()->getAll()) {
			throw new CM_Cli_Exception_InvalidArguments('Too many arguments provided');
		}
		if ($named = $arguments->getNamed()->getAll()) {
			throw new CM_Cli_Exception_InvalidArguments('Illegal option used: `--' . key($named) . '`');
		}
		return call_user_func_array(array($this->_class->newInstance(), $this->_method->getName()), $parameters);
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

	/**
	 * @param ReflectionParameter   $param
	 * @param CM_Cli_Arguments      $arguments
	 * @throws CM_Cli_Exception_InvalidArguments
	 * @return mixed
	 */
	private function _getParamValue(ReflectionParameter $param, CM_Cli_Arguments $arguments) {
		if (!$param->isOptional()) {
			$argumentsNumeric = $arguments->getNumeric();
			if (!$argumentsNumeric->getAll()) {
				throw new CM_Cli_Exception_InvalidArguments('Missing argument `' . $param->getName() . '`');
			}
			$value = $argumentsNumeric->shift();
		} else {
			$paramName = CM_Util::uncamelize($param->getName());
			$argumentsNamed = $arguments->getNamed();
			if (!$argumentsNamed->has($paramName)) {
				return $param->getDefaultValue();
			}
			$value = $argumentsNamed->get($paramName);
			$argumentsNamed->remove($paramName);
		}
		return $this->_forceType($value, $param);
	}

	/**
	 * @param mixed               $value
	 * @param ReflectionParameter $param
	 * @throws CM_Cli_Exception_InvalidArguments
	 * @return array|mixed
	 */
	private function _forceType($value, ReflectionParameter $param) {
		if ($param->isArray()) {
			return explode(',', $value);
		}
		if (!$param->getClass()) {
			return $value;
		}
		try {
			return $param->getClass()->newInstance($value);
		} catch (Exception $e) {
			throw new CM_Cli_Exception_InvalidArguments('Invalid value for parameter `' . $param->getName() . '`. ' . $e->getMessage());
		}
	}

	/**
	 * @return string
	 */
	private function _getMethodName() {
		return CM_Util::uncamelize($this->_method->getName());
	}

}