<?php

class CM_Jobdistribution_Job_AbstractTest extends CMTest_TestCase {

    public function testConstructor() {
        $params = CM_Params::factory(['foo' => 'bar', 'baz' => 1], false);
        /** @var CM_Jobdistribution_Job_Abstract|\Mocka\AbstractClassTrait $jobMock */
        $jobMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class)->newInstance([$params]);

        $this->assertInstanceOf(CM_Jobdistribution_Job_Abstract::class, $jobMock);
        $this->assertSame($params, $jobMock->getParams());
    }

}
