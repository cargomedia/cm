<?php

class CM_Clockwork_Persistence {

    /** @var CM_Clockwork_PersistenceAdapter_Abstract */
    private $_adapter;

    /** @var array */
    private $_data = null;

    /**
     * @param CM_Clockwork_PersistenceAdapter_Abstract $adapter
     */
    public function __construct(CM_Clockwork_PersistenceAdapter_Abstract $adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * @param CM_Clockwork_Event $event
     * @return DateTime|null
     */
    public function getLastRunTime(CM_Clockwork_Event $event) {
        $this->_load();
        if (empty($this->_data[$event->getName()])) {
            return null;
        }
        return $this->_data[$event->getName()];
    }

    /**
     * @param CM_Clockwork_Event $event
     * @param DateTime           $runTime
     */
    public function setRuntime(CM_Clockwork_Event $event, DateTime $runTime) {
        $this->_load();
        $this->_data[$event->getName()] = $runTime;
        $this->_adapter->save($this->_data);
    }

    protected function _load() {
        if (null === $this->_data) {
            $this->_data = $this->_adapter->load();
        }
    }
}
