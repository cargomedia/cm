<?php

class CM_Frontend_JavascriptContainer_View  extends CM_Frontend_JavascriptContainer {

    /**
     * @param string $name
     * @param mixed  $value
     */
    public function setProperty($name, $value) {
        $this->append("this.${name} = " . CM_Params::encode($value, true) . ';');
    }

    /**
     * @param string $message
     */
    public function error($message) {
        $this->append('this.error(' . CM_Params::encode($message, true) . ');');
    }

    /**
     * @param string $message
     */
    public function message($message) {
        $this->append('this.message(' . CM_Params::encode($message, true) . ');');
    }

    /**
     * @param mixed $varList
     */
    public function debug($varList) {
        foreach ($varList as &$var) {
            $var = CM_Params::encode($var, true);
        }
        $this->_operations[] = 'this.message(' . implode(', ', $varList) . ');';
    }
}
