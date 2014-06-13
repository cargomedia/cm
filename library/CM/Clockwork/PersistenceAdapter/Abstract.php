<?php

abstract class CM_Clockwork_PersistenceAdapter_Abstract {

    /** @var string  */
    private $_context;

    /**
     * @param $context
     */
    public function __construct($context) {
        $this->_context = (string) $context;
    }

    /**
     * @return DateTime[]
     */
    abstract public function load();

    /**
     * @param DateTime[] $data
     */
    abstract public function save(array $data);
}
