<?php

class CM_JobDistribution_JobSerializerTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSerialize() {
        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        $jobMock = $jobClassMock->newInstance([CM_Params::factory(['foo' => 'bar', 'bar' => 'baz'], false)]);
        $jobMock->mockMethod('getJobName')->set('CM_Job_Mock');

        $mockSerializer = $this->mockInterface(CM_Serializer_SerializerInterface::class)->newInstanceWithoutConstructor();
        $serializeMock = $mockSerializer->mockMethod('serialize')->set(function ($workload) {
            $this->assertSame([
                'jobClassName' => 'CM_Job_Mock',
                'jobParams'    => ['foo' => 'bar', 'bar' => 'baz'],
            ], $workload
            );
        });
        $jobSerializer = new CM_Jobdistribution_JobSerializer($mockSerializer);
        $jobSerializer->serializeJob($jobMock);
        $this->assertSame(1, $serializeMock->getCallCount());
    }

    public function testUnserialize() {
        $jobSerializer = new CM_Jobdistribution_JobSerializer(new CM_Serializer_ArrayConvertible());

        $jobClassMock = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);
        $jobMock = $jobClassMock->newInstance([CM_Params::factory(['foo' => 'bar'], false)]);

        $serializedJob = $jobSerializer->serializeJob($jobMock);
        $result = $jobSerializer->unserializeJob($serializedJob);
        $this->assertInstanceOf(CM_Jobdistribution_Job_Abstract::class, $result);
        $this->assertSame(['foo' => 'bar'], $result->getParams()->getParamsDecoded());
    }

    public function testUnserializeThrows() {
        $mockSerializer = $this->mockInterface(CM_Serializer_SerializerInterface::class)->newInstanceWithoutConstructor();
        $unserializeMock = $mockSerializer->mockMethod('unserialize')
            ->at(0, function ($workload) {
                throw new CM_Exception_Nonexistent('Not exists');
            })
            ->at(1, function ($workload) {
                return [
                    'jobClassName' => CM_Params::class,
                    'jobParams'    => ['bar' => 'baz'],
                ];
            });

        $jobSerializer = new CM_Jobdistribution_JobSerializer($mockSerializer);
        $exception = $this->catchException(function () use ($jobSerializer) {
            $jobSerializer->unserializeJob(['foo' => 'bar']);
        });
        /** @var CM_Exception_Nonexistent $exception */
        $this->assertInstanceOf(CM_Exception_Nonexistent::class, $exception);
        $this->assertSame('Cannot decode workload', $exception->getMessage());
        $this->assertSame([
            'workload'                 => ['foo' => 'bar'],
            'originalExceptionMessage' => 'Not exists',
        ], $exception->getMetaInfo());

        $exception = $this->catchException(function () use ($jobSerializer) {
            $jobSerializer->unserializeJob(['foo' => 'bar']);
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Not valid job class', $exception->getMessage());
        $this->assertSame([
            'className' => CM_Params::class,
        ], $exception->getMetaInfo());

        $this->assertSame(2, $unserializeMock->getCallCount());
    }

    public function testVerifyParamsThrows() {
        /** @var CM_Jobdistribution_JobSerializer|\Mocka\AbstractClassTrait $serializerMock */
        $serializerMock = $this->mockClass(CM_Jobdistribution_JobSerializer::class)->newInstanceWithoutConstructor();

        $jobMockClass = $this->mockClass(CM_Jobdistribution_Job_Abstract::class);

        $exception = $this->catchException(function () use ($serializerMock, $jobMockClass) {
            $serializerMock->serializeJob($jobMockClass->newInstance([CM_Params::factory(['foo' => 'foo', 'bar' => new stdClass()], false)]));
        });
        /** @var CM_Exception $exception */
        $this->assertInstanceOf(CM_Exception_InvalidParam::class, $exception);
        $this->assertSame('Object is not an instance of CM_ArrayConvertible', $exception->getMessage());
        $this->assertSame(['className' => 'stdClass'], $exception->getMetaInfo());

        $exception = $this->catchException(function () use ($serializerMock, $jobMockClass) {
            $serializerMock->serializeJob($jobMockClass->newInstance([CM_Params::factory([
                'foo' => 'foo',
                'bar' => ['bar' => new stdClass()]
            ], false)]));
        });
        /** @var CM_Exception $exception */
        $this->assertInstanceOf(CM_Exception_InvalidParam::class, $exception);
        $this->assertSame('Object is not an instance of CM_ArrayConvertible', $exception->getMessage());
        $this->assertSame(['className' => 'stdClass'], $exception->getMetaInfo());
    }

}
