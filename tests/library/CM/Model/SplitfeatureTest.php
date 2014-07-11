<?php

class CM_Model_SplitfeatureTest extends CMTest_TestCase {

    public function testCreate() {
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));
        $this->assertInstanceOf('CM_Model_Splitfeature', $splitfeature);

        $splitfeature->delete();
    }

    public function testCreateDuplicate() {
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));

        try {
            CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));
            $this->fail('Could create duplicate splitfeature');
        } catch (CM_Exception $e) {
            $this->assertContains("Duplicate entry 'foo' for key 'name'", $e->getMessage());
        }

        $splitfeature->delete();
    }

    public function testCreateNegativePercentage() {
        try {
            CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => -1));
            $this->fail('Could create splitfeature with negativ percentage');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertSame('Percentage must be between 0 and 100 -1 was given', $e->getMessage());
        }
    }

    public function testCreatePercentageOutOfRange() {
        try {
            CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 110));
            $this->fail('Could create splitfeature with more then 100%');
        } catch (CM_Exception $e) {
            $this->assertSame('Percentage must be between 0 and 100 110 was given', $e->getMessage());
        }
    }

    public function testConstruct() {
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));
        $splitfeature2 = new CM_Model_Splitfeature('foo');
        $this->assertEquals($splitfeature, $splitfeature2);

        $splitfeature->delete();
    }

    public function testGetId() {
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));
        $this->assertGreaterThanOrEqual(1, $splitfeature->getId());

        $splitfeature->delete();
    }

    public function testGetName() {
        /** @var CM_Model_Splitfeature $splitFeature */
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));
        $this->assertSame('foo', $splitfeature->getName());

        $splitfeature->delete();
    }

    public function testGetPercentage() {
        /** @var CM_Model_Splitfeature $splitfeature */
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));
        $this->assertSame(50, $splitfeature->getPercentage());

        $splitfeature->delete();
    }

    public function testSetPercentage() {
        /** @var CM_Model_Splitfeature $splitfeature */
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));

        $splitfeature->setPercentage(80);
        $this->assertSame(80, $splitfeature->getPercentage());

        try {
            $splitfeature->setPercentage(110);
            $this->fail('Could set percentage > 100%');
        } catch (CM_Exception $e) {
            $this->assertTrue(true);
        }

        $splitfeature->delete();
    }

    public function testGetEnabled() {
        /** @var CM_Model_Splitfeature $splitfeature */
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 50));

        /** @var CM_Model_Splitfeature $splitfeature2 */
        $splitfeature2 = CM_Model_Splitfeature::createStatic(array('name' => 'bar', 'percentage' => 10));

        $i = 0;
        $userArray = array();
        while ($i < 200) {
            $user = CMTest_TH::createUser();
            $splitfeature->getEnabled($user);
            $splitfeature2->getEnabled($user);
            $userArray[] = $user;
            $i++;
        }

        CMTest_TH::clearCache();
        $this->_checkEnabledFlag($userArray, $splitfeature);
        $this->_checkEnabledFlag($userArray, $splitfeature2);

        $splitfeature->setPercentage(99);
        $this->_checkEnabledFlag($userArray, $splitfeature);

        $splitfeature2->getPercentage(2);
        $this->_checkEnabledFlag($userArray, $splitfeature2);

        $splitfeature->setPercentage(14);
        $this->_checkEnabledFlag($userArray, $splitfeature);

        $splitfeature->setPercentage(66);
        $this->_checkEnabledFlag($userArray, $splitfeature2);

        $splitfeature->delete();
        $splitfeature2->delete();
    }

    public function testFind() {
        $this->assertNull(CM_Model_Splitfeature::find('foo'));

        CM_Cache_Local::getInstance()->flush();
        $splitfeature = CM_Model_Splitfeature::createStatic(array('name' => 'foo', 'percentage' => 0));
        $this->assertEquals($splitfeature, CM_Model_Splitfeature::find('foo'));
    }

    public function testFindChildClass() {
        CM_Model_Splitfeature::createStatic(array('name' => 'bar', 'percentage' => 0));
        $mockClass = $this->mockClass('CM_Model_Splitfeature');
        /** @var CM_Model_Splitfeature $className */
        $className = $mockClass->getClassName();

        $this->assertInstanceOf($className, $className::find('bar'));
    }

    /**
     * @param CM_Model_User[]       $userList
     * @param CM_Model_Splitfeature $splitfeature
     */
    private function _checkEnabledFlag($userList, CM_Model_Splitfeature $splitfeature) {
        foreach ($userList as $key => $user) {
            if ($key % 100 < $splitfeature->getPercentage()) {
                $this->assertTrue($splitfeature->getEnabled($user));
            } else {
                $this->assertFalse($splitfeature->getEnabled($user));
            }
        }
    }
}

