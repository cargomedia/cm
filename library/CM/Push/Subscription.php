<?php

class CM_Push_Subscription extends CM_Model_Abstract {

    /**
     * @return string
     */
    public function getEndpoint() {
        return $this->_get('endpoint');
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint($endpoint) {
        $this->_set('endpoint', $endpoint);
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return CM_Site_Abstract::factory($this->_get('site'));
    }

    /**
     * @param CM_Site_Abstract $site
     */
    public function setSite(CM_Site_Abstract $site) {
        $this->_set('site', $site->getType());
    }

    /**
     * @return DateTime
     */
    public function getUpdated() {
        return $this->_get('updated');
    }

    /**
     * @param DateTime $updated
     */
    public function setUpdated(DateTime $updated) {
        $this->_set('updated', $updated);
    }

    /**
     * @return CM_Model_User|null
     */
    public function getUser() {
        return $this->_get('user');
    }

    /**
     * @param CM_Model_User|null $user
     */
    public function setUser(CM_Model_User $user = null) {
        $this->_set('user', $user);
    }

    /**
     * @return CM_Model_Schema_Definition
     */
    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'endpoint' => ['type' => 'string'],
            'site'     => ['type' => 'integer'],
            'updated'  => ['type' => 'DateTime'],
            'user'     => ['type' => 'CM_Model_User', 'optional' => true],
        ]);
    }

    protected function _getContainingCacheables() {
        return [
            new CM_Push_SubscriptionList_All(),
            new CM_Push_SubscriptionList_Site($this->getSite()),
        ];
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }

    /**
     * @param string             $endpoint
     * @param CM_Site_Abstract   $site
     * @param CM_Model_User|null $user
     * @return CM_Push_Subscription
     */
    public static function create($endpoint, CM_Site_Abstract $site, CM_Model_User $user = null) {
        $pushSubscription = new CM_Push_Subscription();
        $pushSubscription->setEndpoint($endpoint);
        $pushSubscription->setSite($site);
        $pushSubscription->setUpdated(new DateTime());
        $pushSubscription->setUser($user);
        $pushSubscription->commit();

        return $pushSubscription;
    }

    /**
     * @param string $endpoint
     * @return CM_Push_Subscription|null
     */
    public static function findByEndpoint($endpoint) {
        $endpoint = (string) $endpoint;
        /** @var CM_Model_StorageAdapter_Database $persistence */
        $persistence = self::_getStorageAdapter(self::getPersistenceClass());
        $subscriptionData = $persistence->findByData(self::getTypeStatic(), [
            'endpoint' => $endpoint,
        ]);
        if (null === $subscriptionData) {
            return null;
        }
        return new self($subscriptionData['id']);
    }
}
