<?php

class CM_Model_Splittest_UserTest extends CMTest_TestCase {

    public function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testIsVariationFixture() {
        $user = CMTest_TH::createUser();

        /** @var CM_Model_Splittest_User $test */
        $test = CM_Model_Splittest_User::create('foo', ['v1', 'v2']);

        for ($i = 0; $i < 2; $i++) {
            $isVariationUser1 = $test->isVariationFixture($user, 'v1');
            $this->assertSame($isVariationUser1, $test->isVariationFixture($user, 'v1'));
        }
    }

    public function testSetConversion() {
        $user = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();

        /** @var CM_Model_Splittest_User $test */
        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);

        $test->isVariationFixture($user, 'v1');
        $test->isVariationFixture($user2, 'v1');
        $this->assertSame(0, $variation->getConversionCount());
        $test->setConversion($user);
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getConversionCount());
        $test->setConversion($user2, 2.5);
        CMTest_TH::clearCache();
        $this->assertSame(1.75, $variation->getConversionRate());
    }

    public function testIsVariationFixtureStatic() {
        $user = CMTest_TH::createUser();
        $this->assertFalse(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'bar'));
        CM_Model_Splittest_User::create('foo', ['bar']);
        $this->assertTrue(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'bar'));
    }

    public function testSetVariationFixtureStatic() {
        $user = CMTest_TH::createUser();

        CM_Model_Splittest_User::setVariationFixtureStatic('foo', $user, 'v1');
        $this->assertFalse(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'v1'));
        $this->assertFalse(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'v2'));

        CM_Model_Splittest_User::create('foo', ['v1', 'v2']);
        CM_Model_Splittest_User::setVariationFixtureStatic('foo', $user, 'v1');
        $this->assertTrue(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'v1'));
        $this->assertFalse(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'v2'));

        CM_Model_Splittest_User::setVariationFixtureStatic('foo', $user, 'v2');
        $this->assertFalse(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'v1'));
        $this->assertTrue(CM_Model_Splittest_User::isVariationFixtureStatic('foo', $user, 'v2'));
    }

    public function testSetConversionStatic() {
        $user1 = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();

        CM_Model_Splittest_User::setConversionStatic('foo', $user1);
        $splittest = CM_Model_Splittest_User::create('foo', ['bar']);

        /** @var CM_Model_SplittestVariation $variation */
        $variation = $splittest->getVariations()->getItem(0);
        $splittest->isVariationFixture($user1, 'bar');
        $splittest->isVariationFixture($user2, 'bar');

        $this->assertSame(0, $variation->getConversionCount());
        CM_Model_Splittest_User::setConversionStatic('foo', $user1);
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getConversionCount());
        CM_Model_Splittest_User::setConversionStatic('foo', $user2, 2.5);
        CMTest_TH::clearCache();
        $this->assertSame(2, $variation->getConversionCount());
        $this->assertSame(1.75, $variation->getConversionRate());
    }
}
