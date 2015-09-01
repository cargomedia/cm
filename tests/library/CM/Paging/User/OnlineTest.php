<?php

class CM_Paging_User_OnlineTest extends CMTest_TestCase {

    public function testGetItems() {
        $user1 = CMTest_TH::createUser();
        $user1->setOnline(true);
        $user2 = CMTest_TH::createUser();
        $user2->setOnline(false);
        $this->assertEquals([$user1], (new CM_Paging_User_Online())->getItems());

        $user2->setOnline(true);
        $this->assertEquals([$user1, $user2], (new CM_Paging_User_Online())->getItems());
    }

    public function testContains() {
        $user = CMTest_TH::createUser();
        $this->assertFalse((new CM_Paging_User_Online())->contains($user));

        $user->setOnline(true);
        $this->assertTrue((new CM_Paging_User_Online())->contains($user));

        $user->setOnline(false);
        $this->assertFalse((new CM_Paging_User_Online())->contains($user));
    }
}
