<?php

class CM_Cli_CommandManager {

    const TIMEOUT = 600;

    /** @var CM_Cli_Command[]|null */
    private $_commands = null;

    /** @var int */
    private $_forks = null;

    /** @var CM_InputStream_Interface */
    private $_streamInput;

    /** @var CM_OutputStream_Interface */
    private $_streamOutput, $_streamError;

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
            $this->_setStreamError(new CM_OutputStream_Null());
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

    public function monitorSynchronizedCommands() {
        $time = time();
        $timeoutStamp = $time + self::TIMEOUT;
        $process = $this->_getProcess();
        $hostId = $process->getHostId();
        $result = CM_Db_Db::select('cm_cli_command_manager_process', array('commandName', 'processId'), array('hostId' => $hostId));
        foreach ($result->fetchAll() as $row) {
            $commandName = $row['commandName'];
            $processId = (int) $row['processId'];
            if ($process->isRunning($processId)) {
                CM_Db_Db::update('cm_cli_command_manager_process', array('timeoutStamp' => $timeoutStamp), array('commandName' => $commandName));
            } else {
                CM_Db_Db::delete('cm_cli_command_manager_process', array('commandName' => $commandName));
            }
        }
        CM_Db_Db::delete('cm_cli_command_manager_process', '`timeoutStamp` < ' . $time);
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
                $this->_outputError($this->getHelp());
                return 1;
            }
            if (!$methodName) {
                $this->_outputError($this->getHelp($packageName));
                return 1;
            }
            $command = $this->_getCommand($packageName, $methodName);

            if ($command->getSynchronized()) {
                $this->monitorSynchronizedCommands();
                $this->_checkLock($command);
                $this->_lockCommand($command);
                $commandManager = $this;
                $terminationCallback = function () use ($commandManager, $command) {
                    $commandManager->unlockCommand($command);
                };
            } else {
                $terminationCallback = null;
            }

            $transactionName = 'cm ' . $packageName . ' ' . $methodName;
            $streamInput = $this->_streamInput;
            $streamOutput = $this->_streamOutput;
            $streamError = $this->_streamError;
            $workload = function () use ($transactionName, $command, $arguments, $streamInput, $streamOutput, $streamError) {
                CMService_Newrelic::getInstance()->startTransaction($transactionName);
                $command->run($arguments, $streamInput, $streamOutput, $streamError);
            };

            $forks = max($this->_forks, 1);
            $process = $this->_getProcess();
            for ($i = 0; $i < $forks; $i++) {
                $process->fork($workload);
            }
            $resultList = $process->waitForChildren($command->getKeepalive(), $terminationCallback);
            $failure = Functional\some($resultList, function (CM_Process_WorkloadResult $result) {

                return !$result->isSuccess();
            });
            if ($failure) {
                return 1;
            }
            return 0;
        } catch (CM_Cli_Exception_InvalidArguments $e) {
            $this->_outputError('ERROR: ' . $e->getMessage() . PHP_EOL);
            if (isset($command)) {
                $this->_outputError('Usage: ' . $arguments->getScriptName() . ' ' . $command->getHelp());
            } else {
                $this->_outputError($this->getHelp());
            }
            return 1;
        } catch (CM_Cli_Exception_Internal $e) {
            $this->_outputError('ERROR: ' . $e->getMessage() . PHP_EOL);
            return 1;
        }
    }

    /**
     * @param CM_Cli_Command $command
     */
    public function unlockCommand(CM_Cli_Command $command) {
        $commandName = $command->getName();
        $process = $this->_getProcess();
        $hostId = $process->getHostId();
        $processId = $process->getProcessId();
        CM_Db_Db::delete('cm_cli_command_manager_process', array('commandName' => $commandName, 'hostId' => $hostId, 'processId' => $processId));
    }

    /**
     * @param CM_Cli_Command $command
     * @throws CM_Cli_Exception_Internal
     */
    protected function _checkLock(CM_Cli_Command $command) {
        $lock = $this->_findLock($command);
        if (null === $lock) {
            return;
        }
        $commandName = $command->getName();
        $hostId = dechex($lock['hostId']);
        $processId = (int) $lock['processId'];
        throw new CM_Cli_Exception_Internal("Command `$commandName` still running (process `$processId` on host `$hostId`)");
    }

    /**
     * @param CM_Cli_Command $command
     * @return array|null
     */
    protected function _findLock(CM_Cli_Command $command) {
        $commandName = $command->getName();
        $lock = CM_Db_Db::select('cm_cli_command_manager_process', array('hostId', 'processId'), array('commandName' => $commandName))->fetch();
        if (false === $lock) {
            return null;
        }
        return $lock;
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
    protected function  _getProcess() {
        return CM_Process::getInstance();
    }

    /**
     * @param CM_Cli_Command $command
     */
    protected function _lockCommand(CM_Cli_Command $command) {
        $commandName = $command->getName();
        $process = $this->_getProcess();
        $hostId = $process->getHostId();
        $processId = $process->getProcessId();
        $timeoutStamp = time() + self::TIMEOUT;
        CM_Db_Db::insert('cm_cli_command_manager_process',
            array('commandName' => $commandName, 'hostId' => $hostId, 'processId' => $processId, 'timeoutStamp' => $timeoutStamp));
    }

    /**
     * @param string $message
     */
    protected function _outputError($message) {
        $this->_streamError->writeln($message);
    }

    /**
     * @param CM_InputStream_Interface $streamInput
     */
    private function _setStreamInput(CM_InputStream_Interface $streamInput) {
        $this->_streamInput = $streamInput;
    }

    /**
     * @param CM_OutputStream_Interface $streamOutput
     */
    private function _setStreamOutput(CM_OutputStream_Interface $streamOutput) {
        $this->_streamOutput = $streamOutput;
    }

    /**
     * @param CM_OutputStream_Interface $streamError
     */
    private function _setStreamError(CM_OutputStream_Interface $streamError) {
        $this->_streamError = $streamError;
    }
}
