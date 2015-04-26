<?php

class CM_Jobdistribution_Job_AbstractTest extends CMTest_TestCase {

    public function testGetParams() {
        $params = [
            'integer' => '1',
            'string'  => 'foo-bar',
        ];
        $job = $this->mockObject('CM_Jobdistribution_Job_Abstract', [$params]);
        /** @var CM_Jobdistribution_Job_Abstract $job */
        $this->assertInstanceOf('CM_Params', $job->getParams());
        $this->assertSame(1, $job->getParams()->getInt('integer'));
        $this->assertSame('foo-bar', $job->getParams()->getString('string'));
    }
}
