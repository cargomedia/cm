<?php

abstract class CM_Clockwork_PersistenceAdapter_Abstract {

    /**
     * @param string $context
     * @return DateTime[]
     */
    abstract public function load($context);

    /**
     * @param string     $context
     * @param DateTime[] $data
     */
    abstract public function save($context, array $data);
}
