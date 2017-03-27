<?php

class CM_ModelEvents_Binding {

    /**
     * @param CM_EventHandler_EventHandlerInterface $eventHandler
     * @param string                                $className
     * @param Closure                               $callback
     * @throws CM_Exception_Invalid
     */
    public function bindModelCreated(CM_EventHandler_EventHandlerInterface $eventHandler, $className, Closure $callback) {
        $className = (string) $className;
        $this->_checkIsModelClass($className);
        $eventHandler->bind("model-{$className}-created", $callback);
    }

    /**
     * @param CM_EventHandler_EventHandlerInterface $eventHandler
     * @param string                                $className
     * @param Closure                               $callback
     * @param string|null                           $fieldName
     * @throws CM_Exception_Invalid
     */
    public function bindModelChanged(CM_EventHandler_EventHandlerInterface $eventHandler, $className, Closure $callback, $fieldName = null) {
        $className = (string) $className;
        $this->_checkIsModelClass($className);
        $event = "model-{$className}-changed";
        if (null !== $fieldName) {
            $event .= '-' . ((string) $fieldName);
        }
        $eventHandler->bind($event, $callback);
    }

    /**
     * @param CM_EventHandler_EventHandlerInterface $eventHandler
     * @param string                                $className
     * @param Closure                               $callback
     * @throws CM_Exception_Invalid
     */
    public function bindModelDeleted(CM_EventHandler_EventHandlerInterface $eventHandler, $className, Closure $callback) {
        $className = (string) $className;
        $this->_checkIsModelClass($className);
        $eventHandler->bind("model-{$className}-deleted", $callback);
    }

    /**
     * @param string $className
     * @throws CM_Exception_Invalid
     */
    private function _checkIsModelClass($className) {
        if (!is_subclass_of($className, CM_Model_Abstract::class)) {
            throw new CM_Exception_Invalid('Not a model', null, ['className' => $className]);
        }
    }
}
