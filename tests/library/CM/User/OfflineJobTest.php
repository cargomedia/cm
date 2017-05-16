<?php

class CM_User_OfflineJobTest extends CMTest_TestCase {

    public function testExecute() {
        $this->_setupQueueMock();
        $user = CMTest_TH::createUser();
        $user->setOnline();

        $userChannel = CM_Model_StreamChannel_Message_User::createStatic([
            'key'         => CM_Model_StreamChannel_Message_User::getKeyByUser($user),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);

        $queueService = $this->getServiceManager()->getJobQueue();
        $job = new CM_User_OfflineJob(CM_Params::factory(['user' => $user], false));
        $this->assertSame(true, $user->getOnline());
        $queueService->runSync($job);
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(true, $user->getOnline());

        $userChannel->delete();
        $queueService->runSync($job);

        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(false, $user->getOnline());
    }
}
