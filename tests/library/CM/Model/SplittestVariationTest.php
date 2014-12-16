<?php

class CM_Model_SplittestVariationTest extends CMTest_TestCase {

    /** @var CM_Model_Splittest */
    private $_test;

    public function setUp() {
        $this->_test = CM_Model_Splittest::createStatic(array('name' => 'foo', 'variations' => array('v1', 'v2')));
    }

    public function tearDown() {
        $this->_test->delete();
    }

    public function testConstruct() {
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->_test->getVariations() as $variation) {
            new CM_Model_SplittestVariation($variation->getId());
            $this->assertTrue(true);
        }
    }

    public function testGetId() {
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->_test->getVariations() as $variation) {
            $this->assertInternalType('int', $variation->getId());
            $this->assertGreaterThanOrEqual(1, $variation->getId());
        }
    }

    public function testGetName() {
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->_test->getVariations() as $variation) {
            $this->assertInternalType('string', $variation->getName());
            $this->assertContains($variation->getName(), array('v1', 'v2'));
        }
    }

    public function testGetSplittest() {
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->_test->getVariations() as $variation) {
            $this->assertEquals($this->_test, $variation->getSplittest());
        }
    }

    public function testGetSetEnabled() {
        /** @var CM_Model_SplittestVariation $variation1 */
        $variation1 = $this->_test->getVariations()->getItem(0);
        /** @var CM_Model_SplittestVariation $variation2 */
        $variation2 = $this->_test->getVariations()->getItem(0);

        $this->assertTrue($variation1->getEnabled());
        $this->assertTrue($variation2->getEnabled());

        $variation1->setEnabled(false);
        $this->assertFalse($variation1->getEnabled());
        $variation1->setEnabled(true);
        $this->assertTrue($variation1->getEnabled());

        $variation1->setEnabled(false);
        try {
            $variation2->setEnabled(false);
            $this->fail('Could disable all variations');
        } catch (CM_Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetConversionCount() {
        $user = CMTest_TH::createUser();

        /** @var CM_Model_Splittest_User $test */
        $test = CM_Model_Splittest_User::createStatic(array('name' => 'bar', 'variations' => array('v1')));
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);

        $test->isVariationFixture($user, 'v1');
        $this->assertSame(0, $variation->getConversionCount(true));
        $test->setConversion($user);
        $this->assertSame(1, $variation->getConversionCount(true));

        $test->delete();
    }

    public function testGetConversionWeight() {
        $user = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();
        $user3 = CMTest_TH::createUser();

        /** @var CM_Model_Splittest_User $test */
        $test = CM_Model_Splittest_User::createStatic(array('name' => 'bar', 'variations' => array('v1')));
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);

        $test->isVariationFixture($user, 'v1');
        $test->isVariationFixture($user2, 'v1');
        $test->isVariationFixture($user3, 'v1');
        $this->assertSame(0, $variation->getConversionCount(true));
        $this->assertSame(0.0, $variation->getConversionWeight(true));
        $test->setConversion($user, 3.75);
        $test->setConversion($user2, 3.29);
        $this->assertSame(2, $variation->getConversionCount(true));
        $this->assertSame(7.04, $variation->getConversionWeight(true));
        $this->assertSame(2.3466666666667, $variation->getConversionRate(true));
        $test->setConversion($user, -2);
        $this->assertSame(2, $variation->getConversionCount(true));
        $this->assertSame(5.04, $variation->getConversionWeight(true));
        $this->assertSame(1.68, $variation->getConversionRate(true));

        $test->delete();
    }

    public function testGetConversionWeight_SingleConversion() {
        $user = CMTest_TH::createUser();

        /** @var CM_Model_Splittest_User $test */
        $test = CM_Model_Splittest_User::createStatic(array('name' => 'bar', 'variations' => array('v1')));
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);

        $test->isVariationFixture($user, 'v1');
        $this->assertSame(0, $variation->getConversionCount(true));
        $this->assertSame(0.0, $variation->getConversionWeight(true));
        $test->setConversion($user);
        $this->assertSame(1, $variation->getConversionCount(true));
        $this->assertSame(1., $variation->getConversionWeight(true));
        $this->assertSame(1., $variation->getConversionRate(true));
        $test->setConversion($user);
        $this->assertSame(1, $variation->getConversionCount(true));
        $this->assertSame(1., $variation->getConversionWeight(true));
        $this->assertSame(1., $variation->getConversionRate(true));

        $test->delete();
    }

    public function testGetConversionWeight_MultipleConversions() {
        $user = CMTest_TH::createUser();

        /** @var CM_Model_Splittest_User $test */
        $test = CM_Model_Splittest_User::createStatic(array('name' => 'bar', 'variations' => array('v1')));
        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);

        $test->isVariationFixture($user, 'v1');
        $this->assertSame(0, $variation->getConversionCount(true));
        $this->assertSame(0.0, $variation->getConversionWeight(true));
        $test->setConversion($user, 1.);
        $this->assertSame(1, $variation->getConversionCount(true));
        $this->assertSame(1., $variation->getConversionWeight(true));
        $this->assertSame(1., $variation->getConversionRate(true));
        $test->setConversion($user, 1.);
        $this->assertSame(1, $variation->getConversionCount(true));
        $this->assertSame(2., $variation->getConversionWeight(true));
        $this->assertSame(2., $variation->getConversionRate(true));

        $test->delete();
    }

    public function testGetFixtureCount() {
        $user1 = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();

        /** @var CM_Model_Splittest_User $test */
        $test = CM_Model_Splittest_User::createStatic(array('name' => 'bar', 'variations' => array('v1')));

        /** @var CM_Model_SplittestVariation $variation */
        $variation = $test->getVariations()->getItem(0);

        $this->assertSame(0, $variation->getFixtureCount(true));

        $test->isVariationFixture($user1, 'v1');
        $this->assertSame(1, $variation->getFixtureCount(true));
        $test->isVariationFixture($user1, 'v1');
        $this->assertSame(1, $variation->getFixtureCount(true));
        $test->isVariationFixture($user2, 'v1');
        $this->assertSame(2, $variation->getFixtureCount(true));

        $test->delete();
    }

    /**
     * @param array      $variationDataList
     * @param bool       $significant
     * @param float|null $significance
     *
     * @dataProvider dataProviderTestGetSignificance
     */
    public function testGetSignificance($variationDataList, $significant, $significance) {
        /** @var CM_Model_SplittestVariation[] $variationList */
        $variationList = array_map(function ($variationData) {
            list($fixtureCount, $conversionCount, $weight) = $variationData;
            return $this->_getVariationMock($fixtureCount, $conversionCount, $weight);
        }, $variationDataList);
        $this->createSplittestMock($variationList);
        $variationA = $variationList[0];
        $variationB = $variationList[1];
        $this->assertSame($significance, $variationA->getSignificance($variationB));
        $this->assertSame($significance, $variationB->getSignificance($variationA));
        $this->assertSame($significant, $variationA->isDeviationSignificant($variationB));
        $this->assertSame($significant, $variationB->isDeviationSignificant($variationA));
        /** @var CM_Model_SplittestVariation[] $variationList */
        $variationList = array_map(function ($variationData) {
            list($fixtureCount, $conversionCount, $weight) = $variationData;
            return $this->_getVariationMock($fixtureCount, $conversionCount, $weight * 1000);
        }, $variationDataList);
        $this->createSplittestMock($variationList);
        $variationA = $variationList[0];
        $variationB = $variationList[1];
        $this->assertSame($significance, $variationA->getSignificance($variationB));
        $this->assertSame($significance, $variationB->getSignificance($variationA));
        $this->assertSame($significant, $variationA->isDeviationSignificant($variationB));
        $this->assertSame($significant, $variationB->isDeviationSignificant($variationA));
    }

    public function dataProviderTestGetSignificance() {
        return [
            [[[0, 0, 0], [0, 0, 0]], false, null],
            [[[1, 0, 0], [0, 0, 0]], false, null],
            [[[1, 1, 1], [0, 0, 0]], false, null],
            [[[1, 1, 1], [1, 0, 0]], false, null],
            [[[1, 1, 1], [1, 1, 1]], false, null],

            [[[1000, 0, 0], [1000, 0, 0]], false, null],
            [[[1000, 1, 1], [1000, 0, 0]], false, null],
            [[[1000, 1, 1], [1000, 1, 1]], false, null],
            [[[1000, 1, 1], [1000, 2, 2]], false, null],
            [[[1000, 9, 9], [1000, 8, 8]], false, null],
            [[[1000, 9, 9], [1000, 9, 9]], false, null],
            [[[1000, 9, 9], [1000, 10, 10]], false, 0.817692941581],
            [[[1000, 10, 10], [1000, 10, 10]], false, 1.0],
            [[[1000, 10, 10], [1000, 11, 11]], false, 0.82635978436207],

            [[[1000, 200, 200], [1000, 250, 250]], true, 0.0074196492610257],

            [[[1000, 250, 250], [1000, 250, 250]], false, 1.0],
            [[[1000, 249, 249], [1000, 251, 251]], false, 0.91774110086988],
            [[[1000, 245, 245], [1000, 255, 255]], false, 0.60557661633535],
            [[[1000, 240, 240], [1000, 260, 260]], false, 0.30169958247835],
            [[[1000, 230, 230], [1000, 270, 270]], false, 0.038867103812417],
            [[[1000, 220, 220], [1000, 280, 280]], true, 0.0019457736937391],
            [[[1000, 210, 210], [1000, 290, 290]], true, 0.000036090232367484],

            [[[1000, 500, 250], [1000, 250, 250]], false, 1.0],
            [[[1000, 498, 249], [1000, 251, 251]], false, 0.89934318856137],
            [[[1000, 490, 245], [1000, 255, 255]], false, 0.52708925686554],
            [[[1000, 480, 240], [1000, 260, 260]], false, 0.20590321073207],
            [[[1000, 460, 230], [1000, 270, 270]], false, 0.011412036386002],
            [[[1000, 440, 220], [1000, 280, 280]], true, 0.00014780231033445],
            [[[1000, 420, 210], [1000, 290, 290]], true, 0.0000004200393976022],

            [[[1000, 500, 250], [1000, 250, 250], [1000, 125, 250]], false, 1.0],
            [[[1000, 498, 249], [1000, 251, 251], [1000, 126, 252]], false, 0.98986820631101],
            [[[1000, 490, 245], [1000, 255, 255], [1000, 130, 260]], false, 0.77635542902801],
            [[[1000, 480, 240], [1000, 260, 260], [1000, 135, 270]], false, 0.36941028927436],
            [[[1000, 460, 230], [1000, 270, 270], [1000, 145, 290]], false, 0.022693838197528],
            [[[1000, 440, 220], [1000, 280, 280], [1000, 155, 310]], true, 0.00029558277514585],
            [[[1000, 420, 210], [1000, 290, 290], [1000, 165, 330]], true, 0.0000008400786187801],
        ];
    }

    /**
     * @param PHPUnit_Framework_MockObject_MockObject[] $variationList
     */
    protected function createSplittestMock(array $variationList) {
        $splittest = $this->getMockBuilder('CM_Model_Splittest')->disableOriginalConstructor()->setMethods(array('getVariations'))->getMock();
        $variationPagingMock = new CM_PagingSource_Array($variationList);
        $splittest->expects($this->any())->method('getVariations')->will($this->returnValue($variationPagingMock));
        foreach ($variationList as $variation) {
            $variation->expects($this->any())->method('getSplittest')->will($this->returnValue($splittest));
        }
    }

    /**
     * @param int   $fixture
     * @param int   $conversion
     * @param float $weight
     * @return CM_Model_SplittestVariation
     */
    protected function _getVariationMock($fixture, $conversion, $weight) {
        $variation = $this->getMockBuilder('CM_Model_SplittestVariation')->disableOriginalConstructor()
            ->setMethods(array('getFixtureCount', 'getConversionCount', 'getConversionWeight', 'getSplittest'))->getMock();
        $variation->expects($this->any())->method('getFixtureCount')->will($this->returnValue($fixture));
        $variation->expects($this->any())->method('getConversionCount')->will($this->returnValue($conversion));
        $variation->expects($this->any())->method('getConversionWeight')->will($this->returnValue($weight));
        return $variation;
    }
}
