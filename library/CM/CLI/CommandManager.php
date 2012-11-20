<?php

class CM_Cli_CommandManager {

	/** @var array */
	private $_scannedDirs = array();

	/** @var bool */
	private $_scanned = false;

	/** @var CM_Cli_Command[] */
	private $_commands = array();

	/**
	 * @param string $scannedDir
	 */
	public function addScannedDir($scannedDir) {
		array_push($this->_scannedDirs, $scannedDir);
	}

	/**
	 * @return CM_Cli_Command[]
	 */
	public function getCommands() {
		if (!$this->_scanned) {
			$classes = CM_Util::getClassChildren('CM_CLI_Runnable_Abstract', false, $this->_scannedDirs);
			foreach ($classes as $className) {
				$class = new ReflectionClass($className);
				foreach ($class->getMethods() as $method) {
					if (!$method->isConstructor() && $method->isPublic() && !$method->isStatic()) {
						$this->addCommand(new CM_Cli_Command($method));
					}
				}
			}
			$this->_scanned = true;
		}
		return $this->_commands;
	}

	/**
	 * @param CM_Cli_Command $command
	 */
	public function addCommand(CM_Cli_Command $command) {
		$this->_commands[] = $command;
	}

	/**
	 * @param CM_Cli_Command[] $commands
	 */
	public function addCommands(array $commands) {
		foreach ($commands as $command) {
			$this->addCommand($command);
		}
	}

	/**
	 * @return string
	 */
	public function getHelp() {
		$help = 'Available commands:';
		$help .= PHP_EOL . str_repeat('-', strlen($help)) . PHP_EOL;
		foreach ($this->getCommands() as $command) {
			$help .= $command->getHelp();
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
			$output = PHP_EOL . 'ERROR: ' . $e->getMessage() . PHP_EOL . PHP_EOL;
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