<?php

class CM_Model_StreamChannel_Message_UserTest extends CMTest_TestCase {

    public function testOnUnsubscribe() {
        $user = CMTest_TH::createUser();
        /** @var CM_Model_StreamChannel_Message_User $channel */
        $channel = CM_Model_StreamChannel_Message_User::createStatic([
            'key'         => CM_Model_StreamChannel_Message_User::getKeyByUser($user),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);
        $subscribe = CMTest_TH::createStreamSubscribe($user, $channel);
        $delayedQueue = new CM_Jobdistribution_DelayedQueue($this->getServiceManager());

        $user->setOnline(true);

        $subscribe->delete();
        $channel->delete();
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(true, $user->getOnline());

        $delayedQueue->queueOutstanding();
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(true, $user->getOnline());

        CMTest_TH::timeForward(CM_Model_User::OFFLINE_DELAY);
        $delayedQueue->queueOutstanding();
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(false, $user->getOnline());
    }

    public function testOnSubscribe() {
        $user = CMTest_TH::createUser();
        /** @var CM_Model_StreamChannel_Message_User $channel */
        $channel = CM_Model_StreamChannel_Message_User::createStatic([
            'key'         => CM_Model_StreamChannel_Message_User::getKeyByUser($user),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);
        $subscribe = CMTest_TH::createStreamSubscribe($user, $channel);
        $delayedQueue = new CM_Jobdistribution_DelayedQueue($this->getServiceManager());

        $subscribe->delete();
        $channel->delete();
        $user->setOnline(true);

        CMTest_TH::timeForward(CM_Model_User::OFFLINE_DELAY);
        $delayedQueue->queueOutstanding();
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(false, $user->getOnline());

        /** @var CM_Model_StreamChannel_Message_User $channel */
        $channel = CM_Model_StreamChannel_Message_User::createStatic([
            'key'         => CM_Model_StreamChannel_Message_User::getKeyByUser($user),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);
        $subscribe = CMTest_TH::createStreamSubscribe($user, $channel);
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(true, $user->getOnline());
    }
}
