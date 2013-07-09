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
		$arguments->checkUnused();
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
		foreach (CM_Cli_Arguments::getNumericForMethod($this->_method) as $paramString) {
			$helpText .= ' ' . $paramString;
		}

		foreach (CM_Cli_Arguments::getNamedForMethod($this->_method) as $paramString) {
			$helpText .= ' [' . $paramString . ']';
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
