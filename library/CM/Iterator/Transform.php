<?php

class CM_Iterator_Transform extends IteratorIterator {

    /** @var Closure */
    private $_transformer;

    /**
     * @param Traversable|array $traversable
     * @param Closure           $transformer
     */
    public function __construct($traversable, Closure $transformer) {
        if (is_array($traversable)) {
            $traversable = new ArrayIterator($traversable);
        }
        $this->_transformer = $transformer;
        parent::__construct($traversable);
    }

    public function current() {
        return call_user_func($this->_transformer, parent::current());
    }
}
