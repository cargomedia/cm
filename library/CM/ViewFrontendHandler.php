<?php

class CM_ViewFrontendHandler {

    /** @var string[] */
    protected $_operations = array();

    /**
     * @param string $property
     * @param mixed  $value
     */
    public function setParam($property, $value) {
        $this->append("this.${property} = " . CM_Params::encode($value, true));
    }

    /**
     * @param string $code
     */
    public function append($code) {
        array_push($this->_operations, $code);
    }

    /**
     * @param string $code
     */
    public function prepend($code) {
        array_unshift($this->_operations, $code);
    }

    /**
     * @param string $scope
     * @return string
     */
    public function compile($scope) {
        $code = implode(";\n", $this->_operations);
        return '(function () { ' . $code . ' }).call(' . $scope . ');';
    }

    /**
     * @param string $message
     */
    public function error($message) {
        $this->append('this.error(' . CM_Params::encode($message, true) . ')');
    }

    /**
     * @param string $message
     */
    public function message($message) {
        $this->append('this.message(' . CM_Params::encode($message, true) . ')');
    }

    /**
     * @param mixed $varList
     */
    public function debug($varList) {
        foreach ($varList as &$var) {
            $var = CM_Params::encode($var, true);
        }
        $this->_operations[] = 'this.message(' . implode(', ', $varList) . ')';
    }

    public function clear() {
        $this->_operations = array();
    }
}
