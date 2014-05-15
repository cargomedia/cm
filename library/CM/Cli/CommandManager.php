<?php

class CM_Cli_CommandManager {

    const TIMEOUT = 120;

    /** @var CM_Cli_Command[]|null */
    private $_commands = null;

    /** @var int */
    private $_forks = null;

    /** @var CM_InputStream_Interface */
    private $_streamInput;

    /** @var CM_OutputStream_Interface */
    private $_streamOutput;

    /** @var CM_OutputStream_Interface */
    private $_streamError;

    public function __construct() {
        $this->_commands = array();
        $this->_setStreamInput(new CM_InputStream_Readline());
        $this->_setStreamOutput(new CM_OutputStream_Stream_StandardOutput());
        $this->_setStreamError(new CM_OutputStream_Stream_StandardError());
    }

    /**
     * @param string $className
     * @throws CM_Exception_Invalid
     */
    public function addRunnable($className) {
        $class = new ReflectionClass($className);
        if (!$class->isSubclassOf('CM_Cli_Runnable_Abstract')) {
            throw new CM_Exception_Invalid('Can only add subclasses of `CM_Cli_Runnable_Abstract`');
        }
        if ($class->isAbstract()) {
            throw new CM_Exception_Invalid('Cannot add abstract runnable');
        }
        foreach ($class->getMethods() as $method) {
            if (!$method->isConstructor() && $method->isPublic() && !$method->isStatic()) {
                $command = new CM_Cli_Command($method, $class);
                $this->_commands[$command->getName()] = $command;
            }
        }
    }

    public function autoloadCommands() {
        $classes = CM_Util::getClassChildren('CM_Cli_Runnable_Abstract', false);
        foreach ($classes as $className) {
            $this->addRunnable($className);
        }
    }

    /**
     * @param boolean|null $quiet
     * @param boolean|null $quietWarnings
     * @param boolean|null $nonInteractive
     * @param int|null     $forks
     */
    public function configure($quiet = null, $quietWarnings = null, $nonInteractive = null, $forks = null) {
        $forks = (int) $forks;
        if ($quiet) {
            $this->_setStreamOutput(new CM_OutputStream_Null());
        }
        if ($quietWarnings) {
            CM_Bootloader::getInstance()->getExceptionHandler()->setPrintSeverityMin(CM_Exception::ERROR);
        }
        if ($nonInteractive) {
            $this->_setStreamInput(new CM_InputStream_Null());
        }
        if ($forks > 1) {
            $this->_forks = $forks;
        }
    }

    /**
     * @return CM_Cli_Command[]
     */
    public function getCommands() {
        $commands = $this->_commands;
        ksort($commands);
        return $commands;
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
        $commands = $this->getCommands();
        foreach ($commands as $command) {
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
     * @throws CM_Exception
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

            if ($command->getSynchronized()) {
                if (self::_isLocked($command)) {
                    throw new CM_Exception('Command `' . $command->getName() . '` still running.');
                }
                if (!self::_lockCommand($command)) {
                    return 0;
                }
                $callbackUnlockCommand = function () use ($command) {
                    CM_Cli_CommandManager::unlockCommand($command);
                };
            } else {
                $callbackUnlockCommand = null;
            }

            $keepAlive = $command->getKeepalive();
            $forks = max($this->_forks, (int) $keepAlive);
            if ($forks) {
                $process = static::_getProcess();
                $process->fork($forks, $keepAlive, $callbackUnlockCommand);
            }

            CMService_Newrelic::getInstance()->startTransaction('cm ' . $packageName . ' ' . $methodName);
            $command->run($arguments, $this->_streamInput, $this->_streamOutput);
            if (!$forks && null !== $callbackUnlockCommand) {
                $callbackUnlockCommand();
            }
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
        }
    }

    /**
     * @param CM_Cli_Command $command
     */
    public static function unlockCommand(CM_Cli_Command $command) {
        $commandName = $command->getName();
        $hostId = static::_getProcess()->getHostId();
        $processId = static::_getProcess()->getProcessId();
        CM_Db_Db::delete('cm_cli_command_manager_process', array('commandName' => $commandName, 'hostId' => $hostId, 'processId' => $processId));
    }

    public static function monitorSynchronizedCommands() {
        self::_lockRunningCommands();
        self::_unlockDeadCommands();
    }

    /**
     * @param string $packageName
     * @param string $methodName
     * @throws CM_Cli_Exception_InvalidArguments
     * @return CM_Cli_Command
     */
    protected function _getCommand($packageName, $methodName) {
        foreach ($this->getCommands() as $command) {
            if ($command->match($packageName, $methodName)) {
                return $command;
            }
        }
        throw new CM_Cli_Exception_InvalidArguments('Command `' . $packageName . ' ' . $methodName . '` not found');
    }

    /**
     * @return CM_Process
     */
    protected static function  _getProcess() {
        return CM_Process::getInstance();
    }

    /**
     * @param CM_Cli_Command $command
     * @return bool
     */
    protected static function _isLocked(CM_Cli_Command $command) {
        $commandName = $command->getName();
        return (bool) CM_Db_Db::count('cm_cli_command_manager_process', array('commandName' => $commandName));
    }

    /**
     * @param CM_Cli_Command $command
     * @return bool
     */
    protected static function _lockCommand(CM_Cli_Command $command) {
        $commandName = $command->getName();
        $hostId = static::_getProcess()->getHostId();
        $processId = static::_getProcess()->getProcessId();
        $timeoutStamp = time() + self::TIMEOUT;
        try {
            CM_Db_Db::insert('cm_cli_command_manager_process',
                array('commandName' => $commandName, 'hostId' => $hostId, 'processId' => $processId, 'timeoutStamp' => $timeoutStamp));
        } catch (CM_Db_Exception $e) {
            return false;
        }
        return true;
    }

    protected static function _lockRunningCommands() {
        $timeoutStamp = time() + self::TIMEOUT;
        $hostId = static::_getProcess()->getHostId();
        $result = CM_Db_Db::select('cm_cli_command_manager_process', array('commandName', 'processId'), array('hostId' => $hostId));
        foreach ($result->fetchAll() as $row) {
            $commandName = $row['commandName'];
            $processId = (int) $row['processId'];
            if (false !== posix_getsid($processId)) {
                CM_Db_Db::update('cm_cli_command_manager_process', array('timeoutStamp' => $timeoutStamp), array('commandName' => $commandName));
            }
        }
    }

    protected static function _unlockDeadCommands() {
        $time = time();
        CM_Db_Db::delete('cm_cli_command_manager_process', '`timeoutStamp` < ' . $time);
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
