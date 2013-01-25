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
	 * @param CM_Cli_Arguments          $arguments
	 * @param CM_InputStream_Interface  $input
	 * @param CM_OutputStream_Interface $output
	 * @throws CM_Cli_Exception_InvalidArguments
	 * @throws CM_Exception
	 */
	public function run(CM_Cli_Arguments $arguments, CM_InputStream_Interface $input, CM_OutputStream_Interface $output) {
		$pidFile = null;
		if ($this->_getSynchronized()) {
			if ($this->_isRunning()) {
				throw new CM_Exception('Process `' . $this->_getMethodName() . '` still running.');
			}
			$pidFile = $this->_createPidFile();
		}
		$parameters = $arguments->extractMethodParameters($this->_method);
		if ($arguments->getNumeric()->getAll()) {
			throw new CM_Cli_Exception_InvalidArguments('Too many arguments provided');
		}
		if ($named = $arguments->getNamed()->getAll()) {
			throw new CM_Cli_Exception_InvalidArguments('Illegal option used: `--' . key($named) . '`');
		}
		call_user_func_array(array($this->_class->newInstance($input, $output), $this->_method->getName()), $parameters);
		if ($pidFile) {
			$pidFile->delete();
		}
	}

	/**
	 * @return string
	 */
	public function getHelp() {
		$helpText = $this->getName();
		foreach ($this->_getRequiredParameters() as $paramName) {
			$helpText .= ' <' . CM_Util::uncamelize($paramName) . '>';
		}
		$optionalParameters = $this->_getOptionalParameters();
		if ($optionalParameters) {
			foreach ($optionalParameters as $paramName => $defaultValue) {
				$paramName = CM_Util::uncamelize($paramName);
				$valueDoc = $this->_getParamDoc($paramName);
				if (empty($valueDoc)) {
					$valueDoc = 'value';
				}
				$helpText .= ' [--' . $paramName . '=<' . $valueDoc . '>]';
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
	 * @return string
	 */
	public function getName() {
		return $this->getPackageName() . ' ' . $this->_getMethodName();
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
	 * @return string
	 */
	private function _getMethodName() {
		return CM_Util::uncamelize($this->_method->getName());
	}

	/**
	 * @return boolean
	 */
	private function _getSynchronized() {
		$methodDocComment = $this->_method->getDocComment();
		return (bool) preg_match('/\*\s+@synchronized\s+/', $methodDocComment);
	}

	/**
	 * @return string
	 */
	private function _getPidFilePath() {
		return DIR_DATA_LOCKS . $this->_class->getName() . ':' . $this->_method->getName();
	}

	/**
	 * @return boolean
	 */
	private function _isRunning() {
		$path = $this->_getPidFilePath();
		if (!CM_File::exists($path)) {
			return false;
		}
		$file = new CM_File($path);
		$pid = $file->read();
		if (!ctype_digit($pid) || posix_getsid($pid) === false) {
			return false;
		}
		return true;
	}

	/**
	 * @return CM_File
	 */
	private function _createPidFile() {
		$pid = posix_getpid();
		return CM_File::create($this->_getPidFilePath(), $pid);
	}

}
