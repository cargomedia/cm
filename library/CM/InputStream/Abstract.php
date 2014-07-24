<?php

abstract class CM_InputStream_Abstract implements CM_InputStream_Interface {

    /** @var CM_OutputStream_Interface */
    protected $_streamOutput;

    /**
     * @param string|null $hint
     * @return string
     */
    abstract protected function _read($hint = null);

    public function __construct() {
        if (null === $this->_streamOutput) {
            $this->_streamOutput = new CM_OutputStream_Null();
        }
    }

    public function confirm($hint = null, $default = null) {
        $allowedValues = array('y' => true, 'n' => false);
        $options = array();
        foreach ($allowedValues as $label => $value) {
            if ($label === $default) {
                $label = strtoupper($label);
            }
            $options[] = $label;
        }
        do {
            $label = $this->read($hint . ' (' . implode('/', $options) . ')', $default);
        } while (!array_key_exists($label, $allowedValues));
        return $allowedValues[$label];
    }

    public function read($hint = null, $default = null, Closure $validateCallback = null) {
        if (null !== $hint) {
            $hint .= ' ';
        }
        $value = $this->_read($hint);
        if (!$value && null !== $default) {
            $value = $default;
        }
        if (null !== $validateCallback) {
            try {
                $validateCallback($value);
            } catch (CM_InputStream_InvalidValueException $e) {
                $this->_getStreamOutput()->writeln($e->getMessage());
                $value = $this->read($hint, $default, $validateCallback);
            }
        }
        return $value;
    }

    /**
     * @return CM_OutputStream_Interface
     */
    protected function _getStreamOutput() {
        return $this->_streamOutput;
    }
}
