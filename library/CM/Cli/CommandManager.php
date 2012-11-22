<?php

class CM_Cli_CommandManager {

	/** @var CM_Cli_Command[]|null */
	private $_commands = null;

	/**
	 * @return CM_Cli_Command[]
	 */
	public function getCommands() {
		if (null === $this->_commands) {
			$classes = CM_Util::getClassChildren('CM_Cli_Runnable_Abstract', false);
			foreach ($classes as $className) {
				$class = new ReflectionClass($className);
				foreach ($class->getMethods() as $method) {
					if (!$method->isConstructor() && $method->isPublic() && !$method->isStatic()) {
						$this->_commands[] = new CM_Cli_Command($method);
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
		$helpHeader = 'Available commands:';
		$helpHeader .= PHP_EOL . str_repeat('-', strlen($helpHeader)) . PHP_EOL;
		$help = '';
		foreach ($this->getCommands() as $command) {
			if (!$packageName || $packageName === $command->getPackageName()) {
				$help .= $command->getHelp() . PHP_EOL;
			}
		}
		if ($packageName && !$help) {
			throw new CM_Cli_Exception_InvalidArguments('Package `' . $packageName . '` not found.');
		}
		return $helpHeader . $help;
	}

	/**
	 * @param CM_Cli_Arguments $arguments
	 * @return string
	 */
	public function run(CM_Cli_Arguments $arguments) {
		try {
			$packageName = $arguments->getNumeric()->shift();
			$methodName = $arguments->getNumeric()->shift();
			if (!$packageName) {
				return $this->getHelp();
			}
			if (!$methodName) {
				return $this->getHelp($packageName);
			}
			$command = $this->_getCommand($packageName, $methodName);
			return $command->run($arguments);
		} catch (CM_Cli_Exception_InvalidArguments $e) {
			$output = 'ERROR: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
			if (isset($command)) {
				$output .= 'Usage: ' . $arguments->getScriptName() . ' ' . $command->getHelp();
			} else {
				$output .= $this->getHelp();
			}
			return $output;
		} catch (Exception $e) {
			return 'ERROR: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
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

}