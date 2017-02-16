<?php

class CM_User_OfflineJobTest extends CMTest_TestCase {

    public function testExecute() {
        $user = CMTest_TH::createUser();
        $user->setOnline();

        $userChannel = CM_Model_StreamChannel_Message_User::createStatic([
            'key'         => CM_Model_StreamChannel_Message_User::getKeyByUser($user),
            'adapterType' => CM_MessageStream_Adapter_SocketRedis::getTypeStatic(),
        ]);

        $job = new CM_User_OfflineJob();
        $this->assertSame(true, $user->getOnline());
        $job->run(CM_Params::factory(['user' => $user], false));
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(true, $user->getOnline());

        $userChannel->delete();
        $job->run(CM_Params::factory(['user' => $user], false));
        CMTest_TH::reinstantiateModel($user);
        $this->assertSame(false, $user->getOnline());
    }
}
