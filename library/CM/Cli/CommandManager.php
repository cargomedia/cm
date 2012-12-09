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
				if (!$class->isAbstract()) {
					foreach ($class->getMethods() as $method) {
						if (!$method->isConstructor() && $method->isPublic() && !$method->isStatic()) {
							$this->_commands[] = new CM_Cli_Command($method, $class);
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
		$helpHeader = 'Available commands:';
		$helpHeader .= PHP_EOL . str_repeat('-', strlen($helpHeader)) . PHP_EOL;
		$help = '';
		foreach ($this->getCommands() as $command) {
			if (!$command->isAbstract() && (!$packageName || $packageName === $command->getPackageName())) {
				$help .= $command->getHelp() . PHP_EOL;
			}
		}
		if ($packageName && !$help) {
			throw new CM_Cli_Exception_InvalidArguments('Package `' . $packageName . '` not found.');
		}
		return $helpHeader . $help;
	}

	/**
	 * @param CM_Cli_Arguments    $arguments
	 * @param CM_Output_Interface $output
	 * @return string
	 */
	public function run(CM_Cli_Arguments $arguments, CM_Output_Interface $output) {
		CM_Filesystem::getInstance()->setOutput($output);
		try {
			$packageName = $arguments->getNumeric()->shift();
			$methodName = $arguments->getNumeric()->shift();
			if (!$packageName) {
				$output->writeln($this->getHelp());
				return 1;
			}
			if (!$methodName) {
				$output->writeln($this->getHelp($packageName));
				return 1;
			}
			$command = $this->_getCommand($packageName, $methodName);
			$command->run($arguments, $output);
			return 0;
		} catch (CM_Cli_Exception_InvalidArguments $e) {
			$output->writeln('ERROR: ' . $e->getMessage() . PHP_EOL);
			if (isset($command)) {
				$output->writeln('Usage: ' . $arguments->getScriptName() . ' ' . $command->getHelp());
			} else {
				$output->writeln($this->getHelp());
			}
			return 1;
		} catch (Exception $e) {
			$output->writeln('ERROR: ' . $e->getMessage() . PHP_EOL);
			$output->writeln($e->getTraceAsString());
			return 1;
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