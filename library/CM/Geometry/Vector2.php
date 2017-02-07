<?php

class CM_Geometry_Vector2 implements CM_ArrayConvertible {

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
        return [
            'x' => $this->getX(),
            'y' => $this->getY(),
        ];
    }

    /**
     * @param mixed $value
     * @throws CM_Exception_Invalid
     */
    protected function _validateArgument($value) {
        if (!is_numeric($value)) {
            throw new CM_Exception_Invalid('Non numeric value', null, ['value' => $value]);
        }
    }

    public static function fromArray(array $array) {
        return new self($array['x'], $array['y']);
    }
}
