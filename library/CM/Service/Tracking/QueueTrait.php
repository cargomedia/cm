<?php

trait CM_Service_Tracking_QueueTrait {

    /** @var int */
    protected $_trackingQueueTtl = 86400;

    /**
     * @param CM_Model_User $user
     * @return CM_Queue
     */
    protected function _getTrackingQueue(CM_Model_User $user) {
        return new CM_Queue(__METHOD__ . ':' . $user->getId());
    }

    /**
     * @param int|null $ttl
     */
    protected function _setTrackingQueueTtl($ttl = null) {
        if (null !== $ttl) {
            $this->_trackingQueueTtl = (int) $ttl;
        }
    }

    /**
     * @param CM_Model_User $user
     * @return array|null
     */
    protected function _popTrackingData(CM_Model_User $user) {
        $trackingQueue = $this->_getTrackingQueue($user);
        return $trackingQueue->pop();
    }

    /**
     * @param CM_Model_User $user
     * @param array         $trackingData
     */
    protected function _pushTrackingData(CM_Model_User $user, array $trackingData) {
        $trackingQueue = $this->_getTrackingQueue($user);
        $trackingQueue->push($trackingData);
        $trackingQueue->setTtl($this->_trackingQueueTtl);
    }
}
