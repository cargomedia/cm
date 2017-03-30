<?php

class CM_Model_SplittestTest extends CMTest_TestCase {

    public function tearDown() {
        $splittestList = new CM_Paging_Splittest_All();
        foreach ($splittestList as $splittest) {
            /** @var CM_Model_Splittest $splittest */
            $splittest->delete();
        }
    }

    public function testCreate() {
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $this->assertInstanceOf('CM_Model_Splittest', $test);

        try {
            CM_Model_Splittest::create('foo', ['v1', 'v2']);
            $this->fail('Could create duplicate splittest');
        } catch (CM_Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testConstruct() {
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $test2 = new CM_Model_Splittest('foo');
        $this->assertEquals($test, $test2);
    }

    public function testGetId() {
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $this->assertGreaterThanOrEqual(1, $test->getId());
    }

    public function testGetCreated() {
        $time = time();
        /** @var CM_Model_Splittest $test */
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $this->assertSame($time, $test->getCreated());
    }

    public function testSetCreated() {
        $time = time();
        /** @var CM_Model_Splittest $test */
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $test->setCreated($time + 10);
        CMTest_TH::clearCache();
        CMTest_TH::reinstantiateModel($test);
        $this->assertSame($time + 10, $test->getCreated());
    }

    public function testSetOptimized() {
        /** @var CM_Model_Splittest $test */
        $test = CM_Model_Splittest::create('foo', ['v1']);
        $this->assertSame(false, $test->getOptimized());
        $test->setOptimized(true);
        CMTest_TH::clearCache();
        CMTest_TH::reinstantiateModel($test);
        $this->assertSame(true, $test->getOptimized());
    }

    public function testFlush() {
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $created = $test->getCreated();
        CMTest_TH::timeForward(1);
        $test->flush();
        $this->assertGreaterThan($created, $test->getCreated());
    }

    public function testFlushVariationCache() {
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $variation1 = new CM_Model_SplittestVariation(CM_Db_Db::select('cm_splittestVariation', 'id', ['name' => 'v1'])->fetchColumn());
        $variation2 = new CM_Model_SplittestVariation(CM_Db_Db::select('cm_splittestVariation', 'id', ['name' => 'v2'])->fetchColumn());
        $variation2->setEnabled(false);
        $fixture = $this->mockClass('CM_Splittest_Fixture')->newInstanceWithoutConstructor();
        $fixture->mockMethod('getId')->set(1);
        $fixture->mockMethod('getFixtureType')->set(1);

        CMTest_TH::timeForward(1);
        $variation = CMTest_TH::callProtectedMethod($test, '_getVariationFixture', [$fixture]);
        $this->assertSame('v1', $variation);

        $test->flush();
        $variation2->setEnabled(true);
        $variation1->setEnabled(false);

        $variation = CMTest_TH::callProtectedMethod($test, '_getVariationFixture', [$fixture]);
        $this->assertSame('v2', $variation);
    }

    public function testGetVariationListSorted() {
        /** @var CM_Model_Splittest $test */
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $variationList = $test->getVariationListSorted();
        $this->assertSame('v1', $variationList[0]->getName());
        $this->assertSame('v2', $variationList[1]->getName());

        $request = new CM_Http_Request_Get('/');
        CM_Model_Splittest_RequestClient::isVariationFixtureStatic('foo', $request, 'v1');
        CM_Model_Splittest_RequestClient::setConversionStatic('foo', $request);
        CMTest_TH::clearCache();

        $variationList = $test->getVariationListSorted();
        $this->assertTrue($variationList[0]->getConversionRate() > $variationList[1]->getConversionRate());
    }

    public function testGetVariations() {
        /** @var CM_Model_Splittest $test */
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $this->assertInstanceOf('CM_Paging_SplittestVariation_Splittest', $test->getVariations());
    }

    public function testIsVariationFixtureDisabledVariation() {
        /** @var CM_Model_Splittest_Mock $test */
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2']);
        /** @var CM_Model_SplittestVariation $variation1 */
        $variation1 = $test->getVariations()->getItem(0);
        /** @var CM_Model_SplittestVariation $variation2 */
        $variation2 = $test->getVariations()->getItem(1);

        $variation1->setEnabled(false);
        for ($i = 0; $i < 10; $i++) {
            $user = CMTest_TH::createUser();
            $this->assertTrue($test->isVariationFixture(new CM_Splittest_Fixture($user), $variation2->getName()));
        }
    }

    public function testDelete() {
        $test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
        $test->delete();
        try {
            new CM_Model_Splittest($test->getId());
            $this->fail('Splittest not deleted.');
        } catch (CM_Exception_Nonexistent $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetVariationFixtureMultiple() {
        $user = CMTest_TH::createUser();
        $fixture = new CM_Splittest_Fixture($user);

        /** @var CM_Model_Splittest_Mock $test1 */
        $test1 = CM_Model_Splittest_Mock::create('foo1', ['v1', 'v2']);
        /** @var CM_Model_Splittest_Mock $test2 */
        $test2 = CM_Model_Splittest_Mock::create('foo2', ['w1', 'w2']);

        $this->assertContains($test1->getVariationFixture($fixture), array('v1', 'v2'));
        $this->assertContains($test2->getVariationFixture($fixture), array('w1', 'w2'));
    }

    public function testIsVariationFixture() {
        $user = CMTest_TH::createUser();
        $fixture = new CM_Splittest_Fixture($user);

        /** @var CM_Model_Splittest_Mock $test */
        $test = CM_Model_Splittest_Mock::create('foo1', ['v1', 'v2']);
        $this->assertTrue($test->isVariationFixture($fixture, $test->getVariationFixture($fixture)));
        $this->assertFalse($test->isVariationFixture($fixture, 'noVariation'));
    }

    public function testGetVariationDataListFixture() {
        $user = CMTest_TH::createUser();
        $fixture = new CM_Splittest_Fixture($user);

        $test1 = CM_Model_Splittest_Mock::create('foo1', ['v1', 'v2', 'v3']);
        $test2 = CM_Model_Splittest_Mock::create('foo2', ['w1', 'w2', 'w3']);

        $variationDataList = CM_Model_Splittest::getVariationDataListFixture($fixture);
        $this->assertSame([], $variationDataList);

        $test1->getVariations()->findByName('v2')->setEnabled(false);
        $test1->getVariations()->findByName('v3')->setEnabled(false);
        $test1->getVariationFixture($fixture);
        $variationDataList = CM_Model_Splittest::getVariationDataListFixture($fixture);
        $this->assertSame([
            $test1->getId() => ['variation' => 'v1', 'splittest' => 'foo1', 'flushStamp' => time()],
        ], $variationDataList);

        $test2->getVariations()->findByName('w1')->setEnabled(false);
        $test2->getVariations()->findByName('w3')->setEnabled(false);
        $test2->getVariationFixture($fixture);
        $variationDataList = CM_Model_Splittest::getVariationDataListFixture($fixture);
        $this->assertSame([
            $test1->getId() => ['variation' => 'v1', 'splittest' => 'foo1', 'flushStamp' => time()],
            $test2->getId() => ['variation' => 'w2', 'splittest' => 'foo2', 'flushStamp' => time()],
        ], $variationDataList);
    }

    public function testTracking_RequestClient() {
        $request = $this->getMockForAbstractClass('CM_Http_Request_Abstract', array(''), '', true, true, true, array('getClientId'));
        $request->expects($this->any())->method('getClientId')->will($this->returnValue(1));
        /** @var CM_Http_Request_Abstract $request */
        $fixture = new CM_Splittest_Fixture($request);

        /** @var CM_Model_Splittest_Mock $test */
        $test = CM_Model_Splittest_Mock::create('foo1', ['v1']);
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);
        $variation->getName(); // Fill data

        $mockBuilder = $this->getMockBuilder('CMService_KissMetrics_Client');
        $mockBuilder->setMethods(['trackSplittest']);
        $mockBuilder->setConstructorArgs(['km123']);
        $kissMetricsMock = $mockBuilder->getMock();
        $kissMetricsMock->expects($this->once())->method('trackSplittest')->with($fixture, $this->equalTo($variation));

        $serviceManager = new CM_Service_Manager();
        $serviceManager->registerInstance('kissmetrics', $kissMetricsMock);
        $serviceManager->registerInstance('trackings', new CM_Service_Trackings(['kissmetrics']));
        $test->setServiceManager($serviceManager);

        $test->getVariationFixture($fixture);
    }

    public function testTracking_User() {
        $user = CMTest_TH::createUser();
        $fixture = new CM_Splittest_Fixture($user);

        /** @var CM_Model_Splittest_Mock $test */
        $test = CM_Model_Splittest_Mock::create('foo1', ['v1']);
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);
        $variation->getName(); // Fill data

        $mockBuilder = $this->getMockBuilder('CMService_KissMetrics_Client');
        $mockBuilder->setMethods(['trackSplittest']);
        $mockBuilder->setConstructorArgs(['km123']);
        $kissMetricsMock = $mockBuilder->getMock();
        $kissMetricsMock->expects($this->once())->method('trackSplittest')->with($fixture, $this->equalTo($variation));

        $serviceManager = new CM_Service_Manager();
        $serviceManager->registerInstance('tracking-kissmetrics-test', $kissMetricsMock);
        $serviceManager->unregister('trackings');
        $serviceManager->registerInstance('trackings', new CM_Service_Trackings(['tracking-kissmetrics-test']));

        $test->setServiceManager($serviceManager);

        $test->getVariationFixture($fixture);
    }

    public function testWeightedSplittest() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2']);
        $test->setVariationWeightList(array('v1' => .3, 'v2' => .7));
        $v1 = 0;
        for ($i = 0; $i < 100; $i++) {
            $mockBuilder = $this->getMockBuilder('CM_Model_User');
            $mockBuilder->setMethods(['getId']);
            $userMock = $mockBuilder->getMock();
            $userMock->expects($this->any())->method('getId')->will($this->returnValue(mt_rand()));
            /** @var CM_Model_User $userMock */
            $fixture = new CM_Splittest_Fixture($userMock);
            if ($test->isVariationFixture($fixture, 'v1')) {
                $v1++;
            }
        }
        $this->assertGreaterThan(13, $v1);
        $this->assertLessThan(47, $v1);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Empty variation weight list
     */
    public function testWeightedSplittest_empty() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2']);
        $test->setVariationWeightList(array());
    }

    public function testWeightedSplittest_variationDisabled() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2', 'v3']);
        $test->getVariations()->findByName('v1')->setEnabled(false);
        $test->setVariationWeightList(array('v1' => 3, 'v2' => 7, 'v3' => 10));
        $v1 = 0;
        for ($i = 0; $i < 10; $i++) {
            $mockBuilder = $this->getMockBuilder('CM_Model_User');
            $mockBuilder->setMethods(['getId']);
            $userMock = $mockBuilder->getMock();
            $userMock->expects($this->any())->method('getId')->will($this->returnValue(mt_rand()));
            /** @var CM_Model_User $userMock */
            $fixture = new CM_Splittest_Fixture($userMock);
            if ($test->isVariationFixture($fixture, 'v1')) {
                $v1++;
            }
        }
        $this->assertSame(0, $v1);
    }

    public function testWeightedSplittest_variationMissing() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2', 'v3']);
        $test->setVariationWeightList(array('v2' => 3, 'v3' => 7));
        $v1 = 0;
        for ($i = 0; $i < 10; $i++) {
            $mockBuilder = $this->getMockBuilder('CM_Model_User');
            $mockBuilder->setMethods(['getId']);
            $userMock = $mockBuilder->getMock();
            $userMock->expects($this->any())->method('getId')->will($this->returnValue(mt_rand()));
            /** @var CM_Model_User $userMock */
            $fixture = new CM_Splittest_Fixture($userMock);
            if ($test->isVariationFixture($fixture, 'v1')) {
                $v1++;
            }
        }
        $this->assertSame(0, $v1);
    }

    public function testWeightedSplittest_variationNonExistent() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2']);
        $exception = $this->catchException(function () use ($test) {
            $test->setVariationWeightList(array('v1' => 1, 'v2' => 2, 'v3' => 3));
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('There is no variation in split test', $exception->getMessage());
        $this->assertSame(
            [
                'variation' => 'v3',
                'splitTest' => 'foo',
            ],
            $exception->getMetaInfo()
        );
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage At least one enabled split test variation should have a positive weight
     */
    public function testWeightedSplittest_variationsAllDisabled() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2', 'v3']);
        $test->getVariations()->findByName('v1')->setEnabled(false);
        $test->getVariations()->findByName('v2')->setEnabled(false);
        $test->setVariationWeightList(array('v1' => 3, 'v2' => 7));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage At least one enabled split test variation should have a positive weight
     */
    public function testWeightedSplittest_variationsAllZeroWeight() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2']);
        $test->setVariationWeightList(array('v1' => 0, 'v2' => 0.));
    }

    public function testWeightedSplittest_weightNegative() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2']);
        $exception = $this->catchException(function () use ($test) {
            $test->setVariationWeightList(array('v1' => 1, 'v2' => -2, 'v3' => 3));
        });

        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        /** @var CM_Exception_Invalid $exception */
        $this->assertSame('Split test variation weight. It should be positive', $exception->getMessage());
        $this->assertSame(['variationWeight' => -2.0], $exception->getMetaInfo());
    }

    public function testWeightedSplittest_weightZero() {
        $test = CM_Model_Splittest_Mock::create('foo', ['v1', 'v2']);
        $test->setVariationWeightList(array('v1' => 0, 'v2' => 1));
        $v1 = 0;
        for ($i = 0; $i < 10; $i++) {
            $mockBuilder = $this->getMockBuilder('CM_Model_User');
            $mockBuilder->setMethods(['getId']);
            $userMock = $mockBuilder->getMock();
            $userMock->expects($this->any())->method('getId')->will($this->returnValue(mt_rand()));
            /** @var CM_Model_User $userMock */
            $fixture = new CM_Splittest_Fixture($userMock);
            if ($test->isVariationFixture($fixture, 'v1')) {
                $v1++;
            }
        }
        $this->assertSame(0, $v1);
    }

    public function testExists() {
        $this->assertFalse(CM_Model_Splittest::exists('foo'));
        $splittest = CM_Model_Splittest::create('foo', ['bar']);
        $this->assertTrue(CM_Model_Splittest::exists('foo'));
        $splittest->delete();
        $this->assertFalse(CM_Model_Splittest::exists('foo'));
    }

    public function testOutdatedLocalCache() {
        $test1 = CM_Model_Splittest_Mock::create('foo', range(1, 10));
        $test2 = CM_Model_Splittest_Mock::create('bar', range(1, 10));
        $mockBuilder = $this->getMockBuilder('CM_Model_User');
        $mockBuilder->setMethods(['getId']);
        $userMock = $mockBuilder->getMock();
        $userMock->expects($this->any())->method('getId')->will($this->returnValue(mt_rand()));
        /** @var CM_Model_User $userMock */
        $fixture = new CM_Splittest_Fixture($userMock);
        CM_Db_Db::insert('cm_splittestVariation_fixture', array(
            'splittestId'           => $test1->getId(),
            $fixture->getColumnId() => $fixture->getId(),
            'variationId'           => $test1->getVariations()->findByName(1)->getId(),
            'createStamp'           => time(),
        ));
        $this->assertTrue($test1->isVariationFixture($fixture, 1));
        CM_Db_Db::insert('cm_splittestVariation_fixture', array(
            'splittestId'           => $test2->getId(),
            $fixture->getColumnId() => $fixture->getId(),
            'variationId'           => $test2->getVariations()->findByName(10)->getId(),
            'createStamp'           => time(),
        ));
        $this->assertTrue($test2->isVariationFixture($fixture, 10));
    }
}

class CM_Model_Splittest_Mock extends CM_Model_Splittest {

    /**
     * @param CM_Splittest_Fixture $fixture
     * @param string               $variationName
     * @return bool
     */
    public function isVariationFixture(CM_Splittest_Fixture $fixture, $variationName) {
        return $this->_isVariationFixture($fixture, $variationName);
    }

    /**
     * @param  CM_Splittest_Fixture $fixture
     * @return string
     */
    public function getVariationFixture(CM_Splittest_Fixture $fixture) {
        return $this->_getVariationFixture($fixture);
    }

    /**
     * @param CM_Splittest_Fixture $fixture
     */
    public function setConversion(CM_Splittest_Fixture $fixture) {
        $this->_setConversion($fixture);
    }
}
