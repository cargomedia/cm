<?php

class CM_Model_StreamChannel_Message_UserTest extends CMTest_TestCase {

    public function testOnUnsubscribe() {
        $user = CMTest_TH::createUser();
        $delayedQueue = $this->mockObject(CM_Jobdistribution_DelayedQueue::class);
        $addJobMock = $delayedQueue->mockMethod('addJob')->set(function (CM_Jobdistribution_Job_Abstract $job, $executeAt) use ($user) {
            $this->assertInstanceOf(CM_User_OfflineJob::class, $job);
            $this->assertEquals(['user' => $user], $job->getParams()->getParamsDecoded());
            $this->assertSame(CM_Model_User::OFFLINE_DELAY, $executeAt);
        });
        $this->getServiceManager()->replaceInstance('delayedJobQueue', $delayedQueue);
        /** @var CM_Model_StreamChannel_Message_User $channel */
        $channel = CM_Model_StreamChannel_Message_User::createStatic([
            'key'         => CM_Model_StreamChannel_Message_User::getKeyByUser($user),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);
        $subscribe1 = CMTest_TH::createStreamSubscribe($user, $channel);
        $subscribe2 = CMTest_TH::createStreamSubscribe($user, $channel);

        $subscribe1->delete();
        $this->assertSame(0, $addJobMock->getCallCount());
        $subscribe2->delete();
        $this->assertSame(1, $addJobMock->getCallCount());
    }

    public function testOnSubscribe() {
        $user = CMTest_TH::createUser();
        /** @var CM_Model_StreamChannel_Message_User $channel */
        $channel = CM_Model_StreamChannel_Message_User::createStatic([
            'key'         => CM_Model_StreamChannel_Message_User::getKeyByUser($user),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);

        $this->assertSame(false, $user->getOnline());
        $subscribe = CMTest_TH::createStreamSubscribe($user, $channel);
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(true, $user->getOnline());
    }
}
