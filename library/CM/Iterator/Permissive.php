<?php

class CM_Iterator_Permissive extends IteratorIterator {

    /** @var Closure */
    private $_transformer;

    /** @var mixed */
    private $_current;

    /**
     * @param Traversable|array $traversable
     * @param Closure           $transformer
     */
    public function __construct($traversable, Closure $transformer) {
        if (is_array($traversable)) {
            $traversable = new ArrayIterator($traversable);
        }
        $this->_current = null;
        $this->_transformer = $transformer;
        parent::__construct($traversable);
    }

    public function valid() {
        $this->_current = $this->_processCurrent();
        return parent::valid() && null !== $this->_current;
    }

    public function rewind() {
        parent::rewind();
        $this->_current = $this->_processCurrent();
    }

    public function next() {
        parent::next();
        $this->_current = $this->_processCurrent();
    }

    public function current() {
        return $this->_current;
    }

    /**
     * @return mixed|null
     */
    protected function _processCurrent() {
        $current = null;
        while (null === $current && parent::valid()) {
            $currentRaw = parent::current();
            try {
                $current = call_user_func($this->_transformer, $currentRaw);
            } catch (Exception $e) {
                $this->_handleError($currentRaw, $e);
                $this->next();
            }
        }
        return $current;
    }

    /**
     * @param mixed     $currentRaw
     * @param Exception $exception
     */
    protected function _handleError($currentRaw, $exception) {
    }
}
