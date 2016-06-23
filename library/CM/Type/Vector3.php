<?php

class CM_Type_Vector3 extends CM_Type_Vector2 {

    /** @var float */
    protected $_z;

    /**
     * @param float $x
     * @param float $y
     * @param float $z
     */
    public function __construct($x, $y, $z) {
        parent::__construct($x, $y);
        $this->_validateArgument($z);
        $this->_z = (float) $z;
    }

    /**
     * @return float
     */
    public function getZ() {
        return $this->_z;
    }

    public function toArray() {
        return [$this->getX(), $this->getY(), $this->getZ()];
    }

    public static function getSize() {
        return 3;
    }

    public static function fromArray(array $array) {
        $size = sizeof($array);
        if (self::getSize() !== $size) {
            throw new CM_Exception_Invalid('Invalid source array size');
        }
        return new self($array[0], $array[1], $array[2]);
    }
}
