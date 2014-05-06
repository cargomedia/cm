<?php

class CM_Frontend_JavascriptContainer {

    /** @var string[] */
    protected $_operations = array();

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
     * @param string|null $scope
     * @return string
     */
    public function compile($scope = null) {
        if (!$this->_operations) {
            return '';
        }
        $operations = array_filter($this->_operations);
        $code = implode(";\n", $operations);
        if (null === $scope) {
            return $code;
        }
        return '(function () { ' . $code . ' }).call(' . $scope . ');';
    }

    public function clear() {
        $this->_operations = array();
    }
}
