<?php

class CM_Cli_CommandManager {

    const TIMEOUT = 600;

    /** @var CM_Cli_Command[]|null */
    private $_commands = null;

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
     */
    public function configure($quiet = null, $quietWarnings = null, $nonInteractive = null) {
        if ($quiet) {
            $this->_setStreamOutput(new CM_OutputStream_Null());
            $this->_setStreamError(new CM_OutputStream_Null());
            CM_Bootloader::getInstance()->getExceptionHandler()->setOutput(new CM_OutputStream_Null());
        }
        if ($quietWarnings) {
            CM_Bootloader::getInstance()->getExceptionHandler()->setPrintSeverityMin(CM_Exception::ERROR);
        }
        if ($nonInteractive) {
            $this->_setStreamInput(new CM_InputStream_Null());
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
        $machineId = $this->_getMachineId();
        $result = CM_Db_Db::select('cm_cli_command_manager_process', ['commandName', 'processId'], ['machineId' => $machineId]);
        foreach ($result->fetchAll() as $row) {
            $commandName = $row['commandName'];
            $processId = (int) $row['processId'];
            $where = ['machineId' => $machineId, 'commandName' => $commandName];
            if ($process->isRunning($processId)) {
                CM_Db_Db::update('cm_cli_command_manager_process', ['timeoutStamp' => $timeoutStamp], $where);
            } else {
                CM_Db_Db::delete('cm_cli_command_manager_process', $where);
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
            }

            $success = $this->_executeCommand($command, $arguments, $this->_streamInput, $this->_streamOutput, $this->_streamError);

            if ($command->getSynchronized()) {
                $this->unlockCommand($command);
            }
            if (!$success) {
                return 1;
            }
            return 0;
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
        $machineId = $this->_getMachineId();
        $processId = $this->_getProcess()->getProcessId();

        CM_Db_Db::delete('cm_cli_command_manager_process', [
            'commandName' => $commandName,
            'machineId'   => $machineId,
            'processId'   => $processId,
        ]);
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
        $machineId = $lock['machineId'];
        $processId = (int) $lock['processId'];
        throw new CM_Cli_Exception_Internal("Command `$commandName` still running (process `$processId` on machine `$machineId`)");
    }

    /**
     * @param CM_Cli_Arguments $arguments
     * @throws CM_Cli_Exception_InvalidArguments
     */
    protected function _checkUnusedArguments(CM_Cli_Arguments $arguments) {
        if ($arguments->getNumeric()->getParamsDecoded()) {
            throw new CM_Cli_Exception_InvalidArguments('Too many arguments provided');
        }
        if ($named = $arguments->getNamed()->getParamsDecoded()) {
            throw new CM_Cli_Exception_InvalidArguments('Illegal option used: `--' . key($named) . '`');
        }
    }

    /**
     * @param CM_Cli_Command $command
     * @return array|null
     */
    protected function _findLock(CM_Cli_Command $command) {
        $commandName = $command->getName();
        $lock = CM_Db_Db::select('cm_cli_command_manager_process', ['machineId', 'processId'], ['commandName' => $commandName])->fetch();
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
    protected function _getProcess() {
        return CM_Process::getInstance();
    }

    /**
     * @param CM_Cli_Command            $command
     * @param CM_Cli_Arguments          $arguments
     * @param CM_InputStream_Interface  $streamInput
     * @param CM_OutputStream_Interface $streamOutput
     * @param CM_OutputStream_Interface $streamError
     * @return boolean
     */
    protected function _executeCommand(CM_Cli_Command $command, CM_Cli_Arguments $arguments, CM_InputStream_Interface $streamInput, CM_OutputStream_Interface $streamOutput, CM_OutputStream_Interface $streamError) {
        try {
            $parameters = $command->extractParameters($arguments);
            $this->_checkUnusedArguments($arguments);

            $command->run($parameters, $streamInput, $streamOutput, $streamError);
            return true;
        } catch (CM_Cli_Exception_InvalidArguments $ex) {
            $this->_outputError('ERROR: ' . $ex->getMessage() . PHP_EOL);
            if (isset($command)) {
                $this->_outputError('Usage: ' . $arguments->getScriptName() . ' ' . $command->getHelp());
            } else {
                $this->_outputError($this->getHelp());
            }
            return false;
        } catch (Exception $ex) {
            $this->_outputError('ERROR: ' . $ex->getMessage() . PHP_EOL);
            return false;
        }
    }

    /**
     * @param CM_Cli_Command $command
     */
    protected function _lockCommand(CM_Cli_Command $command) {
        $commandName = $command->getName();
        $machineId = $this->_getMachineId();
        $processId = $this->_getProcess()->getProcessId();
        $timeoutStamp = time() + self::TIMEOUT;
        CM_Db_Db::insert('cm_cli_command_manager_process', [
            'commandName'  => $commandName,
            'machineId'    => $machineId,
            'processId'    => $processId,
            'timeoutStamp' => $timeoutStamp,
        ]);
    }

    /**
     * @param string $message
     */
    protected function _outputError($message) {
        $this->_streamError->writeln($message);
    }

    /**
     * @return string
     */
    protected function _getMachineId() {
        // Global machine-id from systemd https://www.freedesktop.org/software/systemd/man/machine-id.html
        $file = new CM_File('/etc/machine-id');

        if (!$file->exists()) {
            // Local machine-id as a backup
            $serviceManager = CM_Service_Manager::getInstance();
            $file = new CM_File('machine-id', $serviceManager->getFilesystems()->getData());
            if (!$file->exists()) {
                $uuid = Ramsey\Uuid\Uuid::uuid4()->toString();
                $file->write($uuid);
            }
        }

        return trim($file->read());
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
