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
						$this->_addCommand(new CM_Cli_Command($method));
					}
				}
			}
		}
		return $this->_commands;
	}

	/**
	 * @param CM_Cli_Command $command
	 */
	private function _addCommand(CM_Cli_Command $command) {
		$this->_commands[] = $command;
	}

	/**
	 * @return string
	 */
	public function getHelp() {
		$help = 'Available commands:';
		$help .= PHP_EOL . str_repeat('-', strlen($help)) . PHP_EOL;
		foreach ($this->getCommands() as $command) {
			$help .= $command->getHelp() . PHP_EOL;
		}
		return $help;
	}

	/**
	 * @param CM_Cli_Arguments $arguments
	 * @return string
	 */
	public function run(CM_Cli_Arguments $arguments) {
		try {
			$packageName = $arguments->getNumeric()->shift();
			$methodName = $arguments->getNumeric()->shift();
			if (!$packageName || !$methodName) {
				return $this->getHelp();
			}
			$command = $this->_getCommand($packageName, $methodName);
			return $command->run($arguments);
		} catch (CM_Cli_Exception_InvalidArguments $e) {
			$output = 'ERROR: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
			if (isset($command)) {
				$output .= $command->getHelpExtended();
			} else {
				$output .= $this->getHelp();
			}
			return $output;
		} catch (Exception $e) {
			return PHP_EOL . 'ERROR: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
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