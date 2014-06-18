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
        $test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $this->assertInstanceOf('CM_Model_Splittest', $test);

        try {
            $test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
            $this->fail('Could create duplicate splittest');
        } catch (CM_Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testConstruct() {
        $test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $test2 = new CM_Model_Splittest('foo');
        $this->assertEquals($test, $test2);
    }

    public function testGetId() {
        $test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $this->assertGreaterThanOrEqual(1, $test->getId());
    }

    public function testGetCreated() {
        $time = time();
        /** @var CM_Model_Splittest $test */
        $test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $this->assertGreaterThanOrEqual($time, $test->getCreated());
    }

    public function testGetVariations() {
        /** @var CM_Model_Splittest $test */
        $test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $this->assertInstanceOf('CM_Paging_SplittestVariation_Splittest', $test->getVariations());
    }

    public function testIsVariationFixtureDisabledVariation() {
        /** @var CM_Model_Splittest_Mock $test */
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
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
        $test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
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
        $test1 = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo1', 'variations' => array('v1', 'v2')));
        /** @var CM_Model_Splittest_Mock $test2 */
        $test2 = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo2', 'variations' => array('w1', 'w2')));

        $this->assertContains($test1->getVariationFixture($fixture), array('v1', 'v2'));
        $this->assertContains($test2->getVariationFixture($fixture), array('w1', 'w2'));
    }

    public function testIsVariationFixture() {
        $user = CMTest_TH::createUser();
        $fixture = new CM_Splittest_Fixture($user);

        /** @var CM_Model_Splittest_Mock $test */
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo1', 'variations' => array('v1', 'v2')));
        $this->assertTrue($test->isVariationFixture($fixture, $test->getVariationFixture($fixture)));
        $this->assertFalse($test->isVariationFixture($fixture, 'noVariation'));
    }

    public function testWeightedSplittest() {
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $test->setVariationWeightList(array('v1' => .3, 'v2' => .7));
        $v1 = 0;
        for ($i = 0; $i < 100; $i++) {
            $userMock = $this->getMock('CM_Model_User', array('getId'));
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
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $test->setVariationWeightList(array());
    }

    public function testWeightedSplittest_variationDisabled() {
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2', 'v3')));
        $test->getVariations()->findByName('v1')->setEnabled(false);
        $test->setVariationWeightList(array('v1' => 3, 'v2' => 7, 'v3' => 10));
        $v1 = 0;
        for ($i = 0; $i < 10; $i++) {
            $userMock = $this->getMock('CM_Model_User', array('getId'));
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
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2', 'v3')));
        $test->setVariationWeightList(array('v2' => 3, 'v3' => 7));
        $v1 = 0;
        for ($i = 0; $i < 10; $i++) {
            $userMock = $this->getMock('CM_Model_User', array('getId'));
            $userMock->expects($this->any())->method('getId')->will($this->returnValue(mt_rand()));
            /** @var CM_Model_User $userMock */
            $fixture = new CM_Splittest_Fixture($userMock);
            if ($test->isVariationFixture($fixture, 'v1')) {
                $v1++;
            }
        }
        $this->assertSame(0, $v1);
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage `v3`
     */
    public function testWeightedSplittest_variationNonExistent() {
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $test->setVariationWeightList(array('v1' => 1, 'v2' => 2, 'v3' => 3));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage At least one enabled split test variation should have a positive weight
     */
    public function testWeightedSplittest_variationsAllDisabled() {
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2', 'v3')));
        $test->getVariations()->findByName('v1')->setEnabled(false);
        $test->getVariations()->findByName('v2')->setEnabled(false);
        $test->setVariationWeightList(array('v1' => 3, 'v2' => 7));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage At least one enabled split test variation should have a positive weight
     */
    public function testWeightedSplittest_variationsAllZeroWeight() {
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $test->setVariationWeightList(array('v1' => 0, 'v2' => 0.));
    }

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage `-2`
     */
    public function testWeightedSplittest_weightNegative() {
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $test->setVariationWeightList(array('v1' => 1, 'v2' => -2, 'v3' => 3));
    }

    public function testWeightedSplittest_weightZero() {
        $test = CM_Model_Splittest_Mock::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
        $test->setVariationWeightList(array('v1' => 0, 'v2' => 1));
        $v1 = 0;
        for ($i = 0; $i < 10; $i++) {
            $userMock = $this->getMock('CM_Model_User', array('getId'));
            $userMock->expects($this->any())->method('getId')->will($this->returnValue(mt_rand()));
            /** @var CM_Model_User $userMock */
            $fixture = new CM_Splittest_Fixture($userMock);
            if ($test->isVariationFixture($fixture, 'v1')) {
                $v1++;
            }
        }
        $this->assertSame(0, $v1);
    }

    public function testWithoutPersistence() {
        $user = CMTest_TH::createUser();
        $fixture = new CM_Splittest_Fixture($user);

        CM_Config::get()->CM_Model_Splittest->withoutPersistence = true;
        $test = new CM_Model_Splittest_Mock('notExisting');

        $this->assertFalse($test->isVariationFixture($fixture, 'bar'));
        $this->assertSame('', $test->getVariationFixture($fixture));
        $test->setConversion($fixture);

        CMTest_TH::clearConfig();
    }

    public function testWithoutPersistenceDelete() {
        CM_Config::get()->CM_Model_Splittest->withoutPersistence = true;
        $test = new CM_Model_Splittest_Mock('foo');
        $test->delete();

        CMTest_TH::clearConfig();
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
