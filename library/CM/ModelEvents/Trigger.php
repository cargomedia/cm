<?php

class CM_ModelEvents_Trigger implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_EventHandler_EventHandlerInterface $eventHandler
     * @param CM_Model_Abstract                     $model
     */
    public function triggerModelCreated(CM_EventHandler_EventHandlerInterface $eventHandler, CM_Model_Abstract $model) {
        $eventHandler->trigger($this->_getEventName($model, 'created'), $model, []);
    }

    /**
     * @param CM_EventHandler_EventHandlerInterface $eventHandler
     * @param CM_Model_Abstract                     $model
     * @param string|null                           $field
     * @param mixed|null                            $valueNew
     * @param mixed|null                            $valueOld
     */
    public function triggerModelChanged(CM_EventHandler_EventHandlerInterface $eventHandler, CM_Model_Abstract $model, $field = null, $valueNew = null, $valueOld = null) {
        $event = $this->_getEventName($model, 'changed');
        $data = [];
        if (null !== $field) {
            $field = (string) $field;
            $event .= '-' . $field;
            $data = [
                'valueOld' => $valueOld,
                'valueNew' => $valueNew,
            ];
        }
        $eventHandler->trigger($event, $model, $data);
    }

    /**
     * @param CM_EventHandler_EventHandlerInterface $eventHandler
     * @param CM_Model_Abstract                     $model
     */
    public function triggerModelDeleted(CM_EventHandler_EventHandlerInterface $eventHandler, CM_Model_Abstract $model) {
        $eventHandler->trigger($this->_getEventName($model, 'deleted'), $model, []);
    }

    private function _getEventName(CM_Model_Abstract $model, $event) {
        return 'model-' . get_class($model) . '-' . $event;
    }

}
