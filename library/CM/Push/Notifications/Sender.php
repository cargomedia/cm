<?php

class CM_Push_Notification_Sender implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /**
     * @param CM_Push_Subscription[] $subscriptionList
     * @param array                  $messageData
     * @param DateTime               $expireAt
     */
    public function sendNotifications(array $subscriptionList, array $messageData, DateTime $expireAt) {
        $providerSubscriptionMap = [];
        foreach ($subscriptionList as $subscription) {
            $provider = $this->_findProvider($subscription);
            if (!$provider) {
                continue;
            }
            $providerClass = get_class($provider);
            if (!isset($providerSubscriptionMap[$providerClass])) {
                $providerSubscriptionMap[$providerClass] = ['provider' => $provider, 'subscriptionList' => []];
            }
            $providerSubscriptionMap[$providerClass]['subscriptionList'][] = $subscription;
        }

        foreach ($providerSubscriptionMap as $providerSubscriptionData) {
            /** @var CM_Push_Notification_Provider_Abstract $provider */
            $provider = $providerSubscriptionData['provider'];
            /** @var CM_Push_Subscription[] $subscriptionList */
            $subscriptionList = $providerSubscriptionData['subscriptionList'];

            $provider->sendNotifications($subscriptionList, $messageData, $expireAt);
        }
    }

    /**
     * @param CM_Push_Subscription $subscription
     * @return CM_Push_Notification_Provider_Abstract|null
     */
    protected function _findProvider(CM_Push_Subscription $subscription) {
        return CM_Push_Notification_Provider_Abstract::findByEndpoint($this->getServiceManager(), $subscription->getEndpoint());
    }
}
