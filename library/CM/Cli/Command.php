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
     * @param CM_InputStream_Interface  $streamInput
     * @param CM_OutputStream_Interface $streamOutput
     * @param CM_OutputStream_Interface $streamError
     */
    public function run(CM_Cli_Arguments $arguments, CM_InputStream_Interface $streamInput, CM_OutputStream_Interface $streamOutput, CM_OutputStream_Interface $streamError) {
        $parameters = $arguments->extractMethodParameters($this->_method);
        $arguments->checkUnused();
        call_user_func_array(array($this->_class->newInstance($streamInput, $streamOutput, $streamError), $this->_method->getName()), $parameters);
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
     * @return string
     */
    public function getName() {
        return $this->getPackageName() . ' ' . $this->_getMethodName();
    }

    /**
     * @return string
     */
    public function getPackageName() {
        return $this->_class->getMethod('getPackageName')->invoke(null);
    }

    /**
     * @return boolean
     */
    public function getSynchronized() {
        $methodDocComment = $this->_method->getDocComment();
        return (bool) preg_match('/\*\s+@synchronized\s+/', $methodDocComment);
    }

    /**
     * @return bool
     */
    public function isAbstract() {
        return $this->_method->getDeclaringClass()->isAbstract();
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
     * @return string
     */
    protected function _getMethodName() {
        return CM_Util::uncamelize($this->_method->getName());
    }
}
