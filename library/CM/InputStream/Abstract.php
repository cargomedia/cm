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
     * @param string[]    $values
     * @param string|null $default
     * @return mixed
     * @throws CM_Exception_Invalid
     */
    public function select($hint, array $values, $default = null) {
        if (null !== $default && !in_array($default, $values, true)) {
            throw new CM_Exception_Invalid('Invalid default value');
        }
        if (!$values) {
            throw new CM_Exception_Invalid('Not enough values');
        }

        $labels = array_map(function ($value) use ($default) {
            if ($value === $default) {
                $value = strtoupper($value);
            }
            return $value;
        }, $values);
        $hint .= ' (' . join('/', $labels) . ')';

        return $this->read($hint, $default, function ($label) use ($values) {
            if (!in_array($label, $values, true)) {
                throw new CM_InputStream_InvalidValueException();
            }
        });
    }

    public function confirm($hint = null, $default = null) {
        $options = [
            'y' => true,
            'n' => false,
        ];
        $label = $this->select($hint, array_keys($options), $default);
        return $options[$label];
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
