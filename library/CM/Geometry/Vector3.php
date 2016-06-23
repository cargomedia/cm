<?php

class CM_Geometry_Vector3 extends CM_Geometry_Vector2 {

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
        return array_merge(parent::toArray(), ['z' => $this->getZ()]);
    }

    public static function getSize() {
        return 3;
    }

    public static function fromArray(array $array) {
        return new self($array['x'], $array['y'], $array['z']);
    }
}
