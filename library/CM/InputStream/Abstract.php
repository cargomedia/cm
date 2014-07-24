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

    /**
     * @param string      $hint
     * @param array       $values
     * @param string|null $default
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public function select($hint, array $values, $default = null) {
        if (null !== $default && !array_key_exists($default, $values)) {
            throw new CM_Exception_Invalid('Invalid default value');
        }

        if (!$values) {
            throw new CM_Exception_Invalid('Not enough values');
        }

        $labels = array();
        foreach ($values as $label => $value) {
            if ($label === $default) {
                $label = strtoupper($label);
            }
            $labels[] = $label;
        }
        $hint .= ' (' . join('/', $labels) . ')';

        $label = $this->read($hint, $default, function ($label) use ($values) {
            if (!array_key_exists($label, $values)) {
                throw new CM_InputStream_InvalidValueException();
            }
        });
        return $values[$label];
    }

    public function confirm($hint = null, $default = null) {
        return $this->select($hint, ['y' => true, 'n' => false], $default);
    }

    public function read($hint = null, $default = null, Closure $validateCallback = null) {
        $originalHint = $hint;
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
                $value = $this->read($originalHint, $default, $validateCallback);
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
