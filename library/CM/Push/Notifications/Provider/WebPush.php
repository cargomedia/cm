<?php

class CM_Push_Notification_Provider_WebPush extends CM_Push_Notification_Provider_Abstract {

    public function matches($endpoint) {
        $endpoint = (string) $endpoint;
        return (0 === strpos($endpoint, 'https://updates.push.services.mozilla.com/'));
    }

    public function sendNotifications(array $subscriptionList, array $messageData, DateTime $expireAt) {
        $ttl = max(0, $expireAt->getTimestamp() - (new DateTime())->getTimestamp());

        $requests = Functional\map($subscriptionList, function (CM_Push_Subscription $subscription) use ($ttl) {
            return new \GuzzleHttp\Psr7\Request('POST', $subscription->getEndpoint(), ['TTL' => $ttl]);
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
     * @param \GuzzleHttp\Psr7\Request[] $requests
     * @throws CM_Exception
     */
    protected function _sendRequests(array $requests) {
        $guzzle = $this->_getGuzzleClient();
        $errorList = [];
        $pool = new \GuzzleHttp\Pool($guzzle, $requests, [
            'concurrency' => 100,
            'rejected'    => function ($reason, $index) use (&$errorList, $requests) {
                $errorList[] = [
                    'url'     => $requests[$index]->getUri(),
                    'message' => $reason,
                ];
            },
        ]);
        $pool->promise()->wait();
        if (!empty($errorList)) {
            $firstError = Functional\first($errorList);
            throw new CM_Exception(count($errorList) . '/' . count($requests) . ' requests failed.', null, $firstError);
        }
    }
}
