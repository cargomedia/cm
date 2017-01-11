<?php

abstract class CM_Push_Notification_Provider_Abstract extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Service_Manager $serviceManager
     */
    public function __construct(CM_Service_Manager $serviceManager) {
        $this->setServiceManager($serviceManager);
    }

    /**
     * @param string $endpoint
     * @return bool
     */
    abstract public function matches($endpoint);

    /**
     * @param CM_Push_Subscription[] $subscriptionList
     * @param array                  $messageData
     * @param DateTime               $expireAt
     */
    abstract public function sendNotifications(array $subscriptionList, array $messageData, DateTime $expireAt);

    /**
     * @param CM_Service_Manager $serviceManager
     * @param string             $endpoint
     * @throws CM_Exception
     * @return CM_Push_Notification_Provider_Abstract
     */
    public static function factoryByEndpoint(CM_Service_Manager $serviceManager, $endpoint) {
        $provider = self::findByEndpoint($serviceManager, $endpoint);
        if (!$provider) {
            throw new CM_Exception_Invalid('Provider not supported for this endpoint', null, ['endpoint' => $endpoint]);
        }
        return $provider;
    }

    /**
     * @param CM_Service_Manager $serviceManager
     * @param string             $endpoint
     * @return CM_Push_Notification_Provider_Abstract|null
     */
    public static function findByEndpoint(CM_Service_Manager $serviceManager, $endpoint) {
        /** @var CM_Push_Notification_Provider_Abstract $provider */
        foreach (self::getClassChildren() as $provider) {
            if ($provider->matches($endpoint)) {
                return $provider;
            }
        }
        return null;
    }
}
