<?php

class CM_Model_SplittestVariationTest extends CMTest_TestCase {

    /** @var CM_Model_Splittest */
    private $_test;

    public function setUp() {
        $this->_test = CM_Model_Splittest::create('foo', ['v1', 'v2']);
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
            $this->assertContains($variation->getName(), ['v1', 'v2']);
        }
    }

    public function testGetSplittest() {
        /** @var CM_Model_SplittestVariation $variation */
        foreach ($this->_test->getVariations() as $variation) {
            $this->assertEquals($this->_test, $variation->getSplittest());
        }
    }

    public function testGetSetEnabled() {
        $variation1 = $this->_test->getVariations()->getByName('v1');
        $variation2 = $this->_test->getVariations()->getByName('v2');

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

    public function testGetSetFrequency() {
        $variation1 = $this->_test->getVariations()->getByName('v1');
        $variation2 = $this->_test->getVariations()->getByName('v2');

        $this->assertSame(1., $variation1->getFrequency());
        $this->assertSame(1., $variation2->getFrequency());

        $variation1->setFrequency(1 / 3);
        $this->assertSame(0.33, $variation1->getFrequency());
        $variation1->setFrequency(66);
        $this->assertSame(66., $variation1->getFrequency());

        $exception = $this->catchException(function () use ($variation1) {
            $variation1->setFrequency(0);
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Frequency must be positive', $exception->getMessage());

        $exception = $this->catchException(function () use ($variation1) {
            $variation1->setFrequency(-1);
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Frequency must be positive', $exception->getMessage());
    }

    public function testGetConversionCount() {
        $user = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $test->isVariationFixture($user, 'v1');
        $this->assertSame(0, $variation->getConversionCount());
        $test->setConversion($user);
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getConversionCount());

        $test->delete();
    }

    public function testGetConversionWeight() {
        $user = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();
        $user3 = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $test->isVariationFixture($user, 'v1');
        $test->isVariationFixture($user2, 'v1');
        $test->isVariationFixture($user3, 'v1');
        $this->assertSame(0, $variation->getConversionCount());
        $this->assertSame(0.0, $variation->getConversionWeight());
        $test->setConversion($user, 3.75);
        $test->setConversion($user2, 3.29);
        CMTest_TH::clearCache();
        $this->assertSame(2, $variation->getConversionCount());
        $this->assertSame(7.04, $variation->getConversionWeight());
        $this->assertSame(2.3466666666667, $variation->getConversionRate());
        $test->setConversion($user, -2);
        CMTest_TH::clearCache();
        $this->assertSame(2, $variation->getConversionCount());
        $this->assertSame(5.04, $variation->getConversionWeight());
        $this->assertSame(1.68, $variation->getConversionRate());

        $test->delete();
    }

    public function testGetConversionWeight_SingleConversion() {
        $user = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $test->isVariationFixture($user, 'v1');
        $this->assertSame(0, $variation->getConversionCount());
        $this->assertSame(0.0, $variation->getConversionWeight());
        $test->setConversion($user);
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getConversionCount());
        $this->assertSame(1., $variation->getConversionWeight());
        $this->assertSame(1., $variation->getConversionRate());
        $test->setConversion($user);
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getConversionCount());
        $this->assertSame(1., $variation->getConversionWeight());
        $this->assertSame(1., $variation->getConversionRate());

        $test->delete();
    }

    public function testGetConversionWeight_MultipleConversions() {
        $user = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $test->isVariationFixture($user, 'v1');
        $this->assertSame(0, $variation->getConversionCount());
        $this->assertSame(0.0, $variation->getConversionWeight());
        $test->setConversion($user, 1.);
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getConversionCount());
        $this->assertSame(1., $variation->getConversionWeight());
        $this->assertSame(1., $variation->getConversionRate());
        $test->setConversion($user, 1.);
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getConversionCount());
        $this->assertSame(2., $variation->getConversionWeight());
        $this->assertSame(2., $variation->getConversionRate());

        $test->delete();
    }

    public function testGetConversionWeightSquared() {
        $user1 = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $this->assertSame(0., $variation->getConversionWeightSquared());

        $test->isVariationFixture($user1, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(0., $variation->getConversionWeightSquared());

        $test->setConversion($user1, 10.);
        CMTest_TH::clearCache();
        $this->assertSame(100., $variation->getConversionWeightSquared());

        $test->isVariationFixture($user2, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(100., $variation->getConversionWeightSquared());

        $test->setConversion($user2, 2.);
        CMTest_TH::clearCache();
        $this->assertSame(104., $variation->getConversionWeightSquared());

        $test->delete();
    }

    public function testGetStandardDeviation() {
        $user1 = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $this->assertSame(0., $variation->getStandardDeviation());

        $test->isVariationFixture($user1, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(0., $variation->getStandardDeviation());

        $test->setConversion($user1, 10.);
        CMTest_TH::clearCache();
        $this->assertSame(0., $variation->getStandardDeviation());

        $test->isVariationFixture($user2, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(5., $variation->getStandardDeviation());

        $test->setConversion($user2, 2.);
        CMTest_TH::clearCache();
        $this->assertSame(4., $variation->getStandardDeviation());

        $test->delete();
    }

    public function testGetUpperConfidenceBound() {
        $user1 = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $this->assertSame(0., $variation->getUpperConfidenceBound());

        $test->isVariationFixture($user1, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(0., $variation->getUpperConfidenceBound());

        $test->setConversion($user1, 10.);
        CMTest_TH::clearCache();
        $this->assertSame(10., $variation->getUpperConfidenceBound());

        $test->isVariationFixture($user2, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(7.9435250562887, $variation->getUpperConfidenceBound());

        $test->setConversion($user2, 2.);
        CMTest_TH::clearCache();
        $this->assertSame(8.3548200450309, $variation->getUpperConfidenceBound());

        $test->delete();
    }

    public function testGetFixtureCount() {
        $user1 = CMTest_TH::createUser();
        $user2 = CMTest_TH::createUser();

        $test = CM_Model_Splittest_User::create('bar', ['v1']);
        $variation = $test->getVariations()->getByName('v1');

        $this->assertSame(0, $variation->getFixtureCount());

        $test->isVariationFixture($user1, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getFixtureCount());
        $test->isVariationFixture($user1, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(1, $variation->getFixtureCount());
        $test->isVariationFixture($user2, 'v1');
        CMTest_TH::clearCache();
        $this->assertSame(2, $variation->getFixtureCount());

        $test->delete();
    }

    public function testOptimized() {
        $test = CM_Model_Splittest_User::create('testOptimized', ['v1', 'v2'], true);

        foreach ([
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 0, 'v2' => 0]],
                     ['variation' => 'v1', 'conversionWeight' => 0, 'UCB' => ['v1' => 0, 'v2' => 0]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 0, 'v2' => 0]],
                     ['variation' => 'v1', 'conversionWeight' => 10, 'UCB' => ['v1' => 0, 'v2' => 9.5763354702607]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 9.1627730557885, 'v2' => 10.079050361367]],
                     ['variation' => 'v1', 'conversionWeight' => 0, 'UCB' => ['v1' => 9.4853064449853, 'v2' => 7.4647362290016]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 6.9764490828879, 'v2' => 7.6740939911766]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.1299303954748, 'v2' => 9.3361367939935]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.2580334083849, 'v2' => 7.8752548790091]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.3676533020391, 'v2' => 8.8283139741822]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.4632468764998, 'v2' => 7.8363661837493]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.547854326983, 'v2' => 8.5111528523589]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.6236382087886, 'v2' => 7.7609849274971]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.6921892463093, 'v2' => 8.2854930993131]],
                     ['variation' => 'v1', 'conversionWeight' => 10, 'UCB' => ['v1' => 7.7547101338294, 'v2' => 7.6828024677134]],
                     ['variation' => 'v1', 'conversionWeight' => 0, 'UCB' => ['v1' => 9.1140386187892, 'v2' => 7.7176444685704]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.6480715270881, 'v2' => 7.7498373781205]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.6877396941039, 'v2' => 8.1724630283957]],
                     ['variation' => 'v1', 'conversionWeight' => 10, 'UCB' => ['v1' => 7.7247529364914, 'v2' => 7.6626291437821]],
                     ['variation' => 'v1', 'conversionWeight' => 0, 'UCB' => ['v1' => 8.5026412529014, 'v2' => 7.6867011689518]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.5231045928555, 'v2' => 7.7093347077617]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.5493610883994, 'v2' => 8.0648280886575]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.5742006691383, 'v2' => 7.6244971942451]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.5977618494541, 'v2' => 7.9347574318587]],
                     ['variation' => 'v1', 'conversionWeight' => 10, 'UCB' => ['v1' => 7.6201641165989, 'v2' => 7.5503911157322]],
                     ['variation' => 'v1', 'conversionWeight' => 0, 'UCB' => ['v1' => 8.1715906029488, 'v2' => 7.565588932245]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.4341829707168, 'v2' => 7.5801000881577]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.4514490087814, 'v2' => 7.8534733810622]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.4679937673471, 'v2' => 7.5106403210843]],
                     ['variation' => 'v2', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.483872514059, 'v2' => 7.7567753416888]],
                     ['variation' => 'v1', 'conversionWeight' => 10, 'UCB' => ['v1' => 7.4991345044317, 'v2' => 7.4490303850766]],
                     ['variation' => 'v1', 'conversionWeight' => 0, 'UCB' => ['v1' => 7.930011605986, 'v2' => 7.4596622703966]],
                     ['variation' => 'v2', 'conversionWeight' => 11, 'UCB' => ['v1' => 7.3403742293396, 'v2' => 7.4699083170757]],
                 ] as $fixtureData) {
            CMTest_TH::clearCache();
            $upperConfidenceBoundList = [];
            /** @var CM_Model_SplittestVariation $variation */
            foreach ($test->getVariations() as $variation) {
                $upperConfidenceBoundList[$variation->getName()] = $variation->getUpperConfidenceBound();
            }
            $this->assertEquals($fixtureData['UCB'], $upperConfidenceBoundList);
            $user = CMTest_TH::createUser();
            $this->assertTrue($test->isVariationFixture($user, $fixtureData['variation']));
            $test->setConversion($user, $fixtureData['conversionWeight']);
        }

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
        /** @var CM_Model_SplittestVariation $variation */
        return $variation;
    }
}
