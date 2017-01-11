<?php

class CM_Push_Notification_Provider_WebPush extends CM_Push_Notification_Provider_Abstract {

    public function matches($endpoint) {
        $endpoint = (string) $endpoint;
        return (0 === strpos($endpoint, 'https://updates.push.services.mozilla.com/'));
    }

    public function sendNotifications(array $subscriptionList, array $messageData, DateTime $expireAt) {
        $ttl = max(0, $expireAt->getTimestamp() - (new DateTime())->getTimestamp());

        $messageFactory = new \GuzzleHttp\Message\MessageFactory();
        $requests = Functional\map($subscriptionList, function (CM_Push_Subscription $subscription) use ($messageFactory, $ttl) {
            $headers = [
                'TTL' => $ttl,
            ];
            return $messageFactory->createRequest('POST', $subscription->getEndpoint(), [
                'headers'    => $headers,
                'exceptions' => true,
            ]);
        });
        $this->_sendRequests($requests);
    }

    /**
     * @return \GuzzleHttp\Client
     */
    protected function _getGuzzleClient() {
        return new \GuzzleHttp\Client();
    }

    /**
     * @param \GuzzleHttp\Message\Request[] $requests
     * @throws CM_Exception
     */
    protected function _sendRequests(array $requests) {
        $guzzle = $this->_getGuzzleClient();
        /** @var \GuzzleHttp\Event\ErrorEvent[] $errorEvents */
        $errorEvents = [];
        $pool = new \GuzzleHttp\Pool($guzzle, $requests, [
            'pool_size' => 100,
            'error'     => function (\GuzzleHttp\Event\ErrorEvent $errorEvent) use (&$errorEvents) {
                $errorEvents[] = $errorEvent;
            },
        ]);
        $pool->wait();
        if (!empty($errorEvents)) {
            /** @var \GuzzleHttp\Event\ErrorEvent $firstError */
            $firstError = Functional\first($errorEvents);
            throw new CM_Exception(count($errorEvents) . '/' . count($requests) . ' requests failed.', null, [
                'url'     => $firstError->getRequest()->getUrl(),
                'message' => $firstError->getException()->getMessage(),
            ]);
        }
    }
}
