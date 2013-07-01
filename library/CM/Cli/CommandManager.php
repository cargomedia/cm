<?php

class CM_Cli_CommandManager {

	/** @var CM_Cli_Command[]|null */
	private $_commands = null;

	/** @var CM_InputStream_Interface */
	private $_streamInput;

	/** @var CM_OutputStream_Interface */
	private $_streamOutput;

	/** @var CM_OutputStream_Interface */
	private $_streamError;

	public function __construct() {
		$this->_setStreamInput(new CM_InputStream_Readline());
		$this->_setStreamOutput(new CM_OutputStream_Stream_StandardOutput());
		$this->_setStreamError(new CM_OutputStream_Stream_StandardError());
	}

	/**
	 * @return CM_Cli_Command[]
	 */
	public function getCommands() {
		if (null === $this->_commands) {
			$classes = CM_Util::getClassChildren('CM_Cli_Runnable_Abstract', false);
			foreach ($classes as $className) {
				$class = new ReflectionClass($className);
				if (!$class->isAbstract()) {
					foreach ($class->getMethods() as $method) {
						if (!$method->isConstructor() && $method->isPublic() && !$method->isStatic()) {
							$command = new CM_Cli_Command($method, $class);
							$this->_commands[$command->getName()] = $command;
						}
					}
				}
			}
		}
		return $this->_commands;
	}

	/**
	 * @param string|null $packageName
	 * @throws CM_Cli_Exception_InvalidArguments
	 * @return string
	 */
	public function getHelp($packageName = null) {
		$helpHeader = '';
		$helpHeader .= 'Usage:' . PHP_EOL;
		$helpHeader .= ' [options] <command> [arguments]' . PHP_EOL;
		$helpHeader .= PHP_EOL;
		$helpHeader .= 'Options:' . PHP_EOL;
		$reflectionMethod = new ReflectionMethod($this, 'configure');
		foreach (CM_Cli_Arguments::getNamedForMethod($reflectionMethod) as $paramString) {
			$helpHeader .= ' ' . $paramString . PHP_EOL;
		}
		$helpHeader .= PHP_EOL;
		$helpHeader .= 'Commands:' . PHP_EOL;
		$help = '';
		foreach ($this->getCommands() as $command) {
			if (!$command->isAbstract() && (!$packageName || $packageName === $command->getPackageName())) {
				$help .= ' ' . $command->getHelp() . PHP_EOL;
			}
		}
		if ($packageName && !$help) {
			throw new CM_Cli_Exception_InvalidArguments('Package `' . $packageName . '` not found.');
		}
		return $helpHeader . $help;
	}

	/**
	 * @param CM_Cli_Arguments $arguments
	 * @return int
	 */
	public function run(CM_Cli_Arguments $arguments) {
		$method = new ReflectionMethod($this, 'configure');
		$parameters = $arguments->extractMethodParameters($method);
		$method->invokeArgs($this, $parameters);
		try {
			$packageName = $arguments->getNumeric()->shift();
			$methodName = $arguments->getNumeric()->shift();

			if (!$packageName) {
				$this->_streamError->writeln($this->getHelp());
				return 1;
			}
			if (!$methodName) {
				$this->_streamError->writeln($this->getHelp($packageName));
				return 1;
			}
			$command = $this->_getCommand($packageName, $methodName);
			CMService_Newrelic::getInstance()->startTransaction($packageName . ' ' . $methodName);
			$command->run($arguments, $this->_streamInput, $this->_streamOutput);
			return 0;
		} catch (CM_Cli_Exception_InvalidArguments $e) {
			$this->_streamError->writeln('ERROR: ' . $e->getMessage() . PHP_EOL);
			if (isset($command)) {
				$this->_streamError->writeln('Usage: ' . $arguments->getScriptName() . ' ' . $command->getHelp());
			} else {
				$this->_streamError->writeln($this->getHelp());
			}
			return 1;
		} catch (CM_Cli_Exception_Internal $e) {
			$this->_streamError->writeln('ERROR: ' . $e->getMessage() . PHP_EOL);
			return 1;
		} catch (Exception $e) {
			CM_Bootloader::getInstance()->handleException($e, $this->_streamError);
			return 1;
		}
	}

	/**
	 * @param boolean|null $quiet
	 * @param boolean|null $quietWarnings
	 * @param boolean|null $nonInteractive
	 */
	public function configure($quiet = null, $quietWarnings = null, $nonInteractive = null) {
		if ($quiet) {
			$this->_setStreamOutput(new CM_OutputStream_Null());
		}
		if ($quietWarnings) {
			CM_Bootloader::getInstance()->setExceptionOutputSeverityMin(CM_Exception::ERROR);
		}
		if ($nonInteractive) {
			$this->_setStreamInput(new CM_InputStream_Null());
		}
	}

	/**
	 * @param string $packageName
	 * @param string $methodName
	 * @throws CM_Cli_Exception_InvalidArguments
	 * @return CM_Cli_Command
	 */
	private function _getCommand($packageName, $methodName) {
		foreach ($this->getCommands() as $command) {
			if ($command->match($packageName, $methodName)) {
				return $command;
			}
		}
		throw new CM_Cli_Exception_InvalidArguments('Command `' . $packageName . ' ' . $methodName . '` not found');
	}

	/**
	 * @param CM_InputStream_Interface $input
	 */
	private function _setStreamInput(CM_InputStream_Interface $input) {
		$this->_streamInput = $input;
	}

	/**
	 * @param CM_OutputStream_Interface $output
	 */
	private function _setStreamOutput(CM_OutputStream_Interface $output) {
		$this->_streamOutput = $output;
	}

	/**
	 * @param CM_OutputStream_Interface $output
	 */
	private function _setStreamError(CM_OutputStream_Interface $output) {
		$this->_streamError = $output;
	}
}
