<?php

class CM_Cli_Command {

    const TIMEOUT = 300;

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
        if ($this->_getSynchronized()) {
            if ($this->_isLocked()) {
                throw new CM_Exception('Process `' . $this->_getMethodName() . '` still running.');
            }
            if (!$this->_lockProcess()) {
                return;
            }
        }
        $parameters = $arguments->extractMethodParameters($this->_method);
        $arguments->checkUnused();
        call_user_func_array(array($this->_class->newInstance($input, $output), $this->_method->getName()), $parameters);
        if ($this->_getSynchronized()) {
            $this->_unlockProcess();
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
     * @return boolean
     */
    public function getKeepalive() {
        $methodDocComment = $this->_method->getDocComment();
        return (boolean) preg_match('/\*\s+@keepalive\s+/', $methodDocComment);
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

    public static function monitorSynchronizedProcesses() {
        self::_lockRunningProcesses();
        self::_unlockDeadProcesses();
    }

    /**
     * @return string
     */
    protected function _getMethodName() {
        return CM_Util::uncamelize($this->_method->getName());
    }

    /**
     * @return string
     */
    protected function _getProcessName() {
        return $this->_class->getName() . ':' . $this->_method->getName();
    }

    /**
     * @return boolean
     */
    protected function _getSynchronized() {
        $methodDocComment = $this->_method->getDocComment();
        return (bool) preg_match('/\*\s+@synchronized\s+/', $methodDocComment);
    }

    /**
     * @return bool
     */
    protected function _isLocked() {
        $processName = $this->_getProcessName();
        return (bool) CM_Db_Db::count('cm_process', array('name' => $processName));
    }

    /**
     * @return bool
     */
    protected function _lockProcess() {
        $processName = $this->_getProcessName();
        $hostId = self::_getHostId();
        $processId = self::_getProcessId();
        $timeoutStamp = time() + self::TIMEOUT;
        try {
            CM_Db_Db::insert('cm_process', array('name'         => $processName, 'hostId' => $hostId, 'processId' => $processId,
                                                 'timeoutStamp' => $timeoutStamp));
        } catch (CM_Db_Exception $e) {
            return false;
        }
        return true;
    }

    protected function _unlockProcess() {
        $processName = $this->_getProcessName();
        $hostId = self::_getHostId();
        $processId = self::_getProcessId();
        CM_Db_Db::delete('cm_process', array('name' => $processName, 'hostId' => $hostId, 'processId' => $processId));
    }

    /**
     * @return int
     */
    protected static function _getHostId() {
        return (int) hexdec(exec('hostid'));
    }

    /**
     * @return int
     */
    protected static function _getProcessId() {
        return posix_getpid();
    }

    protected static function _lockRunningProcesses() {
        $timeoutStamp = time() + self::TIMEOUT;
        $hostId = self::_getHostId();
        $result = CM_Db_Db::select('cm_process', array('name', 'processId'), array('hostId' => $hostId));
        foreach ($result->fetchAll() as $row) {
            $processName = $row['name'];
            $processId = (int) $row['processId'];
            if (false !== posix_getsid($processId)) {
                CM_Db_Db::update('cm_process', array('timeoutStamp' => $timeoutStamp), array('name' => $processName));
            }
        }
    }

    protected static function _unlockDeadProcesses() {
        $time = time();
        CM_Db_Db::delete('cm_process', '`timeoutStamp` < ' . $time);
    }
}
