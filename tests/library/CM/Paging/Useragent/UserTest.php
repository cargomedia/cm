<?php

class CM_Paging_Useragent_UserTest extends CMTest_TestCase {

    public function testAdd() {
        $user = CMTest_TH::createUser();
        $useragentList = new CM_Paging_Useragent_User($user);
        $time = time();

        $this->assertSame([], (new CM_Paging_Useragent_User($user))->getItems());

        $useragentList->add('Mozilla');

        $this->assertSame([
            ['useragent' => 'Mozilla', 'createStamp' => $time],
        ], (new CM_Paging_Useragent_User($user))->getItems());

        CMTest_TH::timeForward(1);
        $useragentList->add('Internet Explorer');

        $this->assertSame([
            ['useragent' => 'Internet Explorer', 'createStamp' => $time + 1],
            ['useragent' => 'Mozilla', 'createStamp' => $time],
        ], (new CM_Paging_Useragent_User($user))->getItems());

        CMTest_TH::timeForward(1);
        $useragentList->add('Mozilla');

        $this->assertSame([
            ['useragent' => 'Mozilla', 'createStamp' => $time + 2],
            ['useragent' => 'Internet Explorer', 'createStamp' => $time + 1],
        ], (new CM_Paging_Useragent_User($user))->getItems());
    }

    public function testAddFromRequest() {
        $user = CMTest_TH::createUser();
        $useragentList = new CM_Paging_Useragent_User($user);
        $time = time();

        $this->assertSame([], (new CM_Paging_Useragent_User($user))->getItems());

        $request = new CM_Http_Request_Get('/foo');
        $useragentList->addFromRequest($request);

        $this->assertSame([], (new CM_Paging_Useragent_User($user))->getItems());

        $request = new CM_Http_Request_Get('/foo', ['user-agent' => 'Mozilla']);
        $useragentList->addFromRequest($request);

        $this->assertSame([
            ['useragent' => 'Mozilla', 'createStamp' => $time],
        ], (new CM_Paging_Useragent_User($user))->getItems());
    }
}
