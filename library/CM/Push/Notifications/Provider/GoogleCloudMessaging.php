<?php

class CM_Push_Notification_Provider_GoogleCloudMessaging extends CM_Push_Notification_Provider_Abstract {

    public function matches($endpoint) {
        $endpoint = (string) $endpoint;
        return $this->_hasSender() && (0 === strpos($endpoint, 'https://android.googleapis.com/gcm/send/'));
    }

    public function sendNotifications(array $subscriptionList, array $messageData, DateTime $expireAt) {
        $chunkList = array_chunk($subscriptionList, 1000);
        foreach ($chunkList as $subscriptionList) {
            $this->_sendNotifications($subscriptionList, $messageData, $expireAt);
        }
    }

    /**
     * @param CM_Push_Subscription[] $subscriptionList
     * @param array                  $messageData
     * @param DateTime               $expireAt
     */
    protected function _sendNotifications(array $subscriptionList, array $messageData, DateTime $expireAt) {
        /** @var CM_Push_Subscription[] $subscriptionMap */
        $subscriptionMap = [];
        foreach ($subscriptionList as $subscription) {
            $subscriptionMap[$this->_extractRegistrationId($subscription)] = $subscription;
        }

        $ttl = max(0, $expireAt->getTimestamp() - (new DateTime())->getTimestamp());
        $gcmMessage = new \CodeMonkeysRu\GCM\Message(array_keys($subscriptionMap), $messageData);
        $gcmMessage->setTtl($ttl);
        $response = $this->_getSender()->send($gcmMessage);

        if ($response->getFailureCount() > 0) {
            foreach ($response->getInvalidRegistrationIds() as $invalidRegistrationId) {
                $subscription = $subscriptionMap[$invalidRegistrationId];
                $subscription->delete();
            }
        }
    }

    /**
     * @param CM_Push_Subscription $subscription
     * @return string
     */
    protected function _extractRegistrationId(CM_Push_Subscription $subscription) {
        $endpointParts = explode('/', $subscription->getEndpoint());
        return (string) end($endpointParts);
    }

    /**
     * @return \CodeMonkeysRu\GCM\Sender
     */
    protected function _getSender() {
        return $this->getServiceManager()->get('google-cloud-messaging', '\CodeMonkeysRu\GCM\Sender');
    }

    /**
     * @return bool
     */
    protected function _hasSender() {
        return $this->getServiceManager()->has('google-cloud-messaging');
    }
}
