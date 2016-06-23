<?php

class CM_Type_Vector2 implements CM_Type_Vector {

    /** @var float */
    protected $_x;
    /** @var float */
    protected $_y;

    /**
     * @param float $x
     * @param float $y
     */
    public function __construct($x, $y) {
        $this->_validateArgument($x);
        $this->_validateArgument($y);

        $this->_x = (float) $x;
        $this->_y = (float) $y;
    }

    public function getX() {
        return $this->_x;
    }

    public function getY() {
        return $this->_y;
    }

    public function toArray() {
        return [$this->getX(), $this->getY()];
    }

    /**
     * @param mixed $value
     * @throws CM_Exception_Invalid
     */
    protected function _validateArgument($value) {
        if (!is_numeric($value)) {
            throw new CM_Exception_Invalid('Non numeric value `' . $value . '`');
        }
    }

    /**
     * @return int
     */
    public static function getSize() {
        return 2;
    }

    public static function fromArray(array $array) {
        $size = sizeof($array);
        if (self::getSize() !== $size) {
            throw new CM_Exception_Invalid('Invalid source array size');
        }
        return new self($array[0], $array[1]);
    }
}
