<?php

class CM_ParamsTest extends CMTest_TestCase {

    /**
     * @expectedException CM_Exception_Invalid
     * @expectedExceptionMessage Params must be declared encoded or decoded
     */
    public function testConstructMissingDecodedFlag() {
        $params = new CM_Params(['foo' => 'bar']);
    }

    public function testMerge() {
        $text = "Foo Bar, Bar Foo";
        $notText = new stdClass();

        $object1 = $this->mockInterface('CM_ArrayConvertible')->newInstance();
        $toArrayMethod = $object1->mockMethod('toArray')->set([
            'val' => 1,
        ]);
        $object2 = $this->mockInterface('CM_ArrayConvertible')->newInstance();
        $toArrayMethod = $object2->mockMethod('toArray')->set([
            'val' => 2,
        ]);

        $params1 = new CM_Params(['foo' => 1, 'bar' => 'text', 'object1' => $object1], false);
        $params2 = new CM_Params(['foz' => 2, 'bar' => 'other', 'object2' => $object2], false);

        $this->assertEquals([
            'foo'     => 1,
            'bar'     => 'other',
            'foz'     => 2,
            'object1' => [
                '_class' => get_class($object1),
                'val'    => 1,
            ],
            'object2' => [
                '_class' => get_class($object2),
                'val'    => 2,
            ],
        ], $params1->merge($params2)->getParamsEncoded());
        $this->assertEquals([
            'foo'     => 1,
            'bar'     => 'other',
            'foz'     => 2,
            'object1' => $object1,
            'object2' => $object2,
        ], $params1->merge($params2)->getParamsDecoded());
    }

    public function testHas() {
        $params = new CM_Params(array('1' => 0, '2' => 'ababa', '3' => new stdClass(), '4' => null, '5' => false), false);

        $this->assertTrue($params->has('1'));
        $this->assertTrue($params->has('2'));
        $this->assertTrue($params->has('3'));
        $this->assertFalse($params->has('4'));
        $this->assertTrue($params->has('5'));
        $this->assertFalse($params->has('6'));
    }

    public function testGetWithInvalidEncodedData() {
        $params = new CM_Params(['foo' => ['_class' => 'Some_Nonexistent_Class', '_id' => 123]], true);
        $this->assertSame(['_class' => 'Some_Nonexistent_Class', '_id' => 123], $params->get('foo'));
    }

    public function testGetString() {
        $text = "Foo Bar, Bar Foo";
        $notText = new stdClass();
        $params = new CM_Params(array('text1' => CM_Params::encode($text), 'text2' => $text, 'text3' => $notText), false);

        $this->assertEquals($text, $params->getString('text1'));
        $this->assertEquals($text, $params->getString('text2'));
        try {
            $params->getString('text3');
            $this->fail('invalid param. should not exist');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertTrue(true);
        }
        $this->assertEquals('foo', $params->getString('text4', 'foo'));
    }

    public function testGetStringArray() {
        $params = new CM_Params(array('k1' => 9, 'k2' => array(99, '121', '72', 0x3f), 'k3' => array('4', '8' . '3', '43', 'pong')), false);

        $this->assertSame(array('4', '83', '43', 'pong'), $params->getStringArray('k3'));

        try {
            $params->getStringArray('k1');
            $this->fail('Is not an array of strings!');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertTrue(true);
        }

        try {
            $params->getStringArray('k2');
            $this->fail('Is not an array of strings!');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetInt() {
        $number1 = 12345678;
        $number2 = '12345678';
        $number3 = 'foo';
        $params = new CM_Params(array('number1' => $number1, 'number2' => CM_Params::encode($number2), 'number3' => $number3), false);

        $this->assertEquals($number1, $params->getInt('number1'));
        $this->assertEquals($number2, $params->getInt('number2'));
        try {
            $params->getInt('number3');
            $this->fail('invalid param. should not exist');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertTrue(true);
        }
        $this->assertEquals(4, $params->getInt('number4', 4));
    }

    public function testGetIntArray() {
        $params = new CM_Params(array('k1' => '7', 'k2' => array('99', '121', 72, 0x3f), 'k3' => array(4, 88, '43', 'pong')), false);

        $this->assertSame(array(99, 121, 72, 63), $params->getIntArray('k2'));

        try {
            $params->getIntArray('k1');
            $this->fail('Is not an array of integers!');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertTrue(true);
        }

        try {
            $params->getIntArray('k3');
            $this->fail('Is not an array of integers!');
        } catch (CM_Exception_InvalidParam $e) {
            $this->assertTrue(true);
        }
    }

    public function testGetFloat() {
        $testDataList = array(
            array(34.28, 34.28),
            array(-34.28, -34.28),
            array(0., 0.),
            array(-34., -34),
            array(34., 34),
            array(0., 0),
            array(34.28, '34.28'),
            array(-34.28, '-34.28'),
            array(34.2, '34.2'),
            array(-34.2, '-34.2'),
            array(34., '34.'),
            array(-34., '-34.'),
            array(4.28, '4.28'),
            array(-4.28, '-4.28'),
            array(.28, '.28'),
            array(-.28, '-.28'),
            array(.28, '0.28'),
            array(-.28, '-0.28'),
            array(0., '0.'),
            array(0., '-0.'),
            array(0., '.0'),
            array(0., '-.0'),
            array(34., '34'),
            array(-34., '-34'),
            array(0., '0'),
            array(0., '-0'),
        );
        foreach ($testDataList as $testData) {
            $expected = $testData[0];
            $userInput = $testData[1];
            $params = new CM_Params(array('userInput' => $userInput), false);
            $this->assertSame($expected, $params->getFloat('userInput'));
        }
        $userInputInvalidList = array('', '-', '.', '-.', '1.2.3', '12 ', ' 12', '12,345', false, true, array('1'), new stdClass(),
            fopen(__FILE__, 'r'));
        foreach ($userInputInvalidList as $userInputInvalid) {
            $params = new CM_Params(array('userInput' => $userInputInvalid), false);
            try {
                $params->getFloat('userInput');
                $this->fail('User input is not a float');
            } catch (CM_Exception_InvalidParam $e) {
                $this->assertTrue(true);
            }
        }
    }

    public function testGetLanguage() {
        $language = CM_Model_Language::create('English', 'en', true);
        $params = new CM_Params(array('language' => $language, 'languageId' => $language->getId(), 'no-object-param' => 'xyz'), false);
        $this->assertEquals($language, $params->getLanguage('language'));
        $this->assertEquals($language, $params->getLanguage('languageId'));
        try {
            $params->getLanguage('no-object-param');
            $this->fail('getObject should fail and throw exception');
        } catch (CM_Exception $e) {
            $this->assertSame('Model has no data', $e->getMessage());
            $this->assertContains(get_class($language), $e->getMetaInfo());
        }
    }

    public function testGetFile() {
        $file = new CM_File(CM_Bootloader::getInstance()->getDirTmp() . 'foo');
        $params = new CM_Params(array('file' => $file, 'filename' => $file->getPath()), false);
        $this->assertEquals($file, $params->getFile('file'));
        $this->assertEquals($file, $params->getFile('filename'));
    }

    public function testGetFileNonexistent() {
        $fileNonexistent = new CM_File('foo/bar');
        $params = new CM_Params(array('nonexistent' => $fileNonexistent->getPath()), false);
        $this->assertEquals($fileNonexistent, $params->getFile('nonexistent'));
    }

    public function testGetFileGeoPoint() {
        $point = new CM_Geo_Point(1, 2);
        $params = new CM_Params(array('point' => $point), false);
        $value = $params->getGeoPoint('point');
        $this->assertInstanceOf('CM_Geo_Point', $value);
        $this->assertSame(1.0, $value->getLatitude());
        $this->assertSame(2.0, $value->getLongitude());
    }

    /**
     * @expectedException CM_Exception_InvalidParam
     * @expectedExceptionMessage Not enough parameters
     */
    public function testGetGeoPointException() {
        $params = new CM_Params(array('point' => 'foo'), false);
        $params->getGeoPoint('point');
    }

    public function testDecodeEncode() {
        $this->assertSame('foo', CM_Params::decode('foo'));
        $this->assertSame(array(), CM_Params::decode(array()));
        $this->assertSame(array('foo' => 'bar', 'foo1' => true), CM_Params::decode(array('foo' => 'bar', 'foo1' => true)));
    }

    public function testDecodeArrayConvertible() {
        $object = $this->mockInterface('CM_ArrayConvertible');
        $fromArrayMethod = $object->mockStaticMethod('fromArray')->set(function ($encoded) {
            $this->assertSame(['foo' => 1], $encoded);
            return $encoded['foo'];
        });
        $encodedArrayConvertible = [
            'foo'    => 1,
            '_class' => get_class($object->newInstance()),
        ];
        $this->assertEquals(1, CM_Params::decode($encodedArrayConvertible));
        $this->assertSame(1, $fromArrayMethod->getCallCount());
    }

    public function testDecodeArrayConvertibleRecursive() {
        $objectOuter = $this->mockInterface('CM_ArrayConvertible');
        $fromArrayMethodOuter = $objectOuter->mockStaticMethod('fromArray')->set(function ($encoded) {
            $this->assertSame(['foo' => 1, 'object' => 2], $encoded);
            return (int) $encoded['foo'] . $encoded['object'];
        });
        $objectInner = $this->mockInterface('CM_ArrayConvertible');
        $fromArrayMethodInner = $objectInner->mockStaticMethod('fromArray')->set(function ($encoded) {
            $this->assertSame(['bar' => 2], $encoded);
            return $encoded['bar'];
        });

        $encodedArrayConvertible = [
            'foo'    => 1,
            '_class' => get_class($objectOuter->newInstance()),
            'object' => [
                'bar'    => 2,
                '_class' => get_class($objectInner->newInstance()),
            ]
        ];
        $this->assertEquals(12, CM_Params::decode($encodedArrayConvertible));
        $this->assertSame(1, $fromArrayMethodInner->getCallCount());
        $this->assertSame(1, $fromArrayMethodOuter->getCallCount());
    }

    public function testEncodeArrayConvertible() {
        $object = $this->mockInterface('CM_ArrayConvertible')->newInstance();
        $toArrayMethod = $object->mockMethod('toArray')->set([
            'myId' => 1
        ]);
        $expectedEncoded = array(
            'myId'   => 1,
            '_class' => get_class($object),
        );
        $this->assertEquals($expectedEncoded, CM_Params::encode($object));
        $this->assertSame(1, $toArrayMethod->getCallCount());
    }

    public function testEncodeObjectId() {
        /** @var CM_ArrayConvertible|\Mocka\AbstractClassTrait $object */
        $object = $this->mockClass(null, ['CM_ArrayConvertible', 'JsonSerializable'])->newInstance();
        $toArrayMethod = $object->mockMethod('toArray')->set([
            'myId' => 1
        ]);
        $jsonSerializeMethod = $object->mockMethod('jsonSerialize')->set([
            'myData' => 1
        ]);
        $expectedEncoded = array(
            'myId'   => 1,
            '_class' => get_class($object),
        );
        $this->assertEquals(json_encode($expectedEncoded), CM_Params::encodeObjectId($object));
        $this->assertSame(1, $toArrayMethod->getCallCount());
        $this->assertSame(0, $jsonSerializeMethod->getCallCount());
    }

    public function testEncodeJsonSerializable() {
        $object = $this->mockInterface('JsonSerializable')->newInstance();
        $toArrayMethod = $object->mockMethod('jsonSerialize')->set([
            'foo' => 1
        ]);
        $expectedEncoded = array(
            'foo'    => 1,
            '_class' => get_class($object),
        );
        $this->assertEquals($expectedEncoded, CM_Params::encode($object));
        $this->assertSame(1, $toArrayMethod->getCallCount());
    }

    public function testDecodeJsonSerializable() {
        $object = $this->mockInterface('JsonSerializable');
        $encodedJsonSerializable = [
            'foo'    => 1,
            '_class' => get_class($object->newInstance()),
        ];
        $this->assertEquals($encodedJsonSerializable, CM_Params::decode($encodedJsonSerializable));
    }

    public function testEncodeArrayConvertibleAndJsonSerializable() {
        $object = $this->mockClass(null, ['CM_ArrayConvertible', 'JsonSerializable'])->newInstance();
        $toArrayMethod = $object->mockMethod('toArray')->set([
            'foo' => 1
        ]);
        $jsonSerializeMethod = $object->mockMethod('jsonSerialize')->set([
            'bar' => 1
        ]);
        $expectedEncoded = array(
            'foo'    => 1,
            'bar'    => 1,
            '_class' => get_class($object),
        );
        $this->assertEquals($expectedEncoded, CM_Params::encode($object));
        $this->assertSame(1, $toArrayMethod->getCallCount());
        $this->assertSame(1, $jsonSerializeMethod->getCallCount());
    }

    public function testEncodeRecursive() {
        $object = $this->mockClass(null, ['CM_ArrayConvertible', 'JsonSerializable'])->newInstance();
        $nestedObject1 = $this->mockClass(null, ['CM_ArrayConvertible'])->newInstance();
        $nestedObject2 = $this->mockClass(null, ['JsonSerializable'])->newInstance();
        $object->mockMethod('toArray')->set([
            'foo'     => 1,
            'nested1' => $nestedObject1,
        ]);
        $object->mockMethod('jsonSerialize')->set([
            'bar'     => 1,
            'nested2' => $nestedObject2
        ]);
        $nestedObject1->mockMethod('toArray')->set(['foo' => 2]);
        $nestedObject2->mockMethod('jsonSerialize')->set(['bar' => 2]);
        $expected = [
            '_class'  => get_class($object),
            'foo'     => 1,
            'nested1' => [
                '_class' => get_class($nestedObject1),
                'foo'    => 2,
            ],
            'bar'     => 1,
            'nested2' => [
                '_class' => get_class($nestedObject2),
                'bar'    => 2,
            ]
        ];
        $this->assertSame($expected, CM_Params::encode($object));
    }

    public function testDecodeRecursive() {
        $object = $this->mockClass(null, ['CM_ArrayConvertible', 'JsonSerializable']);
        $nestedArrayConvertible = $this->mockClass(null, ['CM_ArrayConvertible']);
        $nestedJsonSerializable = $this->mockClass(null, ['JsonSerializable']);
        $encodedArrayConvertible = [
            '_class' => $nestedArrayConvertible->getClassName(),
            'foo'    => 2,
        ];
        $encodedJsonSerializable = [
            '_class' => $nestedJsonSerializable->getClassName(),
            'bar'    => 2,
        ];
        $encodedObject = [
            '_class'  => $object->getClassName(),
            'foo'     => 1,
            'nested1' => $encodedArrayConvertible,
            'bar'     => 1,
            'nested2' => $encodedJsonSerializable
        ];
        $fromArrayMethodObject = $object->mockStaticMethod('fromArray')->set(function ($encoded) use ($encodedJsonSerializable) {
            $this->assertSame(['foo' => 1, 'nested1' => 2, 'bar' => 1, 'nested2' => $encodedJsonSerializable], $encoded);
            return [$encoded['foo'], $encoded['nested1']];
        });
        $fromArrayMethodNestedObject = $nestedArrayConvertible->mockStaticMethod('fromArray')->set(function ($encoded) {
            $this->assertSame(['foo' => 2], $encoded);
            return $encoded['foo'];
        });
        $this->assertEquals([1, 2], CM_Params::decode($encodedObject));
        $this->assertSame(1, $fromArrayMethodNestedObject->getCallCount());
        $this->assertSame(1, $fromArrayMethodObject->getCallCount());
    }

    public function testGetDateTime() {
        $dateTimeList = array(
            new DateTime('2012-12-12 13:00:00 +0300'),
            new DateTime('2012-12-12 13:00:00 -0200'),
            new DateTime('2012-12-12 13:00:00 -0212'),
            new DateTime('2012-12-12 13:00:00 GMT'),
            new DateTime('2012-12-12 13:00:00 GMT+2'),
            new DateTime('2012-12-12 13:00:00 Europe/Zurich'),
        );
        foreach ($dateTimeList as $dateTime) {
            $paramsArray = json_decode(json_encode(array('date' => $dateTime)), true);
            $params = new CM_Params($paramsArray, false);
            $this->assertEquals($params->getDateTime('date'), $dateTime);
        }
    }

    public function testGetLocation() {
        $location = CMTest_TH::createLocation();
        $params = new CM_Params([
            'location'               => $location,
            'locationParameters'     => ['id' => $location->getId(), 'level' => $location->getLevel()],
            'insufficientParameters' => 1,
            'invalidLevel'           => ['id' => $location->getId(), 'level' => 9999],
        ], false);
        $this->assertEquals($location, $params->getLocation('location'));
        $this->assertEquals($location, $params->getLocation('locationParameters'));

        /** @var CM_Exception_InvalidParam $exception */
        $exception = $this->catchException(function () use ($params) {
            $params->getLocation('insufficientParameters');
            $this->fail('Instantiating location with insufficient parameters');
        });
        $this->assertInstanceOf(CM_Exception_InvalidParam::class, $exception);
        $this->assertSame('Not enough parameters', $exception->getMessage());
        $this->assertSame(['parameters' => 1, 'className' => 'CM_Model_Location'], $exception->getMetaInfo());

        /** @var CM_Exception_InvalidParam $exception */
        $exception = $this->catchException(function () use ($params) {
            $params->getLocation('invalidLevel');
        });
        $this->assertInstanceOf(CM_Exception_InvalidParam::class, $exception);
        $this->assertSame('Invalid location level', $exception->getMessage());
        $this->assertSame(['level' => 9999], $exception->getMetaInfo());
    }

    public function testGetParamsDecoded() {
        $paramsArray = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];
        $paramsClass = $this->mockClass(CM_Params::class);
        $decodeMethod = $paramsClass->mockStaticMethod('decode')
            ->at(0, function ($value) {
                $this->assertSame('foo', $value);
                return $value . '-decoded';
            })
            ->at(1, function ($value) {
                $this->assertSame('bar', $value);
                return $value . '-decoded';
            });
        $params = $paramsClass->newInstance([$paramsArray, true]);
        /** @var CM_Params $params */

        $expected = [
            'foo' => 'foo-decoded',
            'bar' => 'bar-decoded'
        ];
        $this->assertSame(0, $decodeMethod->getCallCount());

        $this->assertSame($expected, $params->getParamsDecoded());
        $this->assertSame(2, $decodeMethod->getCallCount());

        $this->assertSame($expected, $params->getParamsDecoded());
        $this->assertSame(2, $decodeMethod->getCallCount());
    }

    public function testGetParamsEncoded() {
        $paramsArray = [
            'foo' => 'foo',
            'bar' => 'bar',
        ];
        $paramsClass = $this->mockClass('CM_Params');
        $encodeMethod = $paramsClass->mockStaticMethod('encode')
            ->at(0, function ($value) {
                $this->assertSame('foo', $value);
                return $value . '-encoded';
            })
            ->at(1, function ($value) {
                $this->assertSame('bar', $value);
                return $value . '-encoded';
            });
        $params = $paramsClass->newInstance([$paramsArray, false]);
        /** @var CM_Params $params */

        $expected = [
            'foo' => 'foo-encoded',
            'bar' => 'bar-encoded'
        ];
        $this->assertSame(0, $encodeMethod->getCallCount());

        $this->assertSame($expected, $params->getParamsEncoded());
        $this->assertSame(count($paramsArray), $encodeMethod->getCallCount());

        $this->assertSame($expected, $params->getParamsEncoded());
        $this->assertSame(count($paramsArray), $encodeMethod->getCallCount());
    }

    public function testGetParamNames() {
        $params = new CM_Params(['foo' => 'bar'], false);
        $this->assertSame(['foo'], $params->getParamNames());
        $params->set('bar', 'foo');
        $this->assertSame(['foo', 'bar'], $params->getParamNames());
    }

    /**
     * @expectedException CM_Exception_InvalidParam
     */
    public function testGetParamsInvalidObject() {
        $params = new CM_Params(array('foo' => new stdClass()), false);
        $params->getParams('foo');
    }

    public function testGetParamsInvalidInt() {
        $params = new CM_Params(array('foo' => 12), false);
        $exception = $this->catchException(function () use ($params) {
            $params->getParams('foo');
        });

        $this->assertInstanceOf('CM_Exception_InvalidParam', $exception);
        /** @var CM_Exception_InvalidParam $exception */
        $this->assertSame('Unexpected type of arguments', $exception->getMessage());
        $this->assertSame(['type' => 'integer'], $exception->getMetaInfo());
    }

    /**
     * @expectedException CM_Exception_InvalidParam
     * @expectedExceptionMessage Cannot decode input
     */
    public function testGetParamsInvalidString() {
        $params = new CM_Params(array('foo' => 'hello'), false);
        $params->getParams('foo');
    }

    public function testDebugInfo() {
        $params = new CM_Params(['foo' => 12, 'bar' => [1, 2]], false);
        $this->assertSame("['foo' => 12, 'bar' => [0 => 1, 1 => 2]]", $params->getDebugInfo());
    }

    public function testDebugInfoWithException() {
        /** @var CM_Params|\Mocka\AbstractClassTrait $params */
        $params = $this->mockClass(CM_Params::class)->newInstanceWithoutConstructor();
        $params->mockMethod('getParamsDecoded')->set(function () {
            throw new Exception('foo');
        });
        $this->assertSame('[Cannot dump params: `foo`]', $params->getDebugInfo());
    }

    public function testGetStreamChannel() {
        $streamChannel = CMTest_TH::createStreamChannel();
        $params = new CM_Params(['channel' => $streamChannel], false);
        $this->assertEquals($streamChannel, $params->getStreamChannel('channel'));
    }

    public function testGetStreamChannelMedia() {
        $streamChannel = CMTest_TH::createStreamChannel(CM_Model_StreamChannel_Media::getTypeStatic());
        $params = new CM_Params(['channel' => $streamChannel], false);
        $this->assertEquals($streamChannel, $params->getStreamChannelMedia('channel'));
    }

    public function testGetStreamChannelJanus() {
        $streamChannel = CMTest_TH::createStreamChannel(CM_Janus_StreamChannel::getTypeStatic());
        $params = new CM_Params(['channel' => $streamChannel], false);
        $this->assertEquals($streamChannel, $params->getStreamChannelJanus('channel'));
    }

    public function testGetStreamChannelDefinition() {
        $definition = new CM_StreamChannel_Definition('foo', 12);
        $params = new CM_Params(['def' => $definition], false);
        $this->assertEquals($definition, $params->getStreamChannelDefinition('def'));
    }

    public function testGetGeometryVector2() {
        $vector2 = new CM_Geometry_Vector2(1.1, 2.2);
        $params = new CM_Params(array('vector2' => $vector2), false);
        $value = $params->getGeometryVector2('vector2');
        $this->assertInstanceOf('CM_Geometry_Vector2', $value);
        $this->assertSame(1.1, $value->getX());
        $this->assertSame(2.2, $value->getY());

        $exception = $this->catchException(function () {
            $params = new CM_Params(array('vector2' => 'foo'), false);
            $params->getGeometryVector2('vector2');
        });
        $this->assertInstanceOf('CM_Exception_InvalidParam', $exception);
        $this->assertSame('Not enough parameters', $exception->getMessage());
    }

    public function testGetGeometryVector3() {
        $vector3 = new CM_Geometry_Vector3(1.1, 2.2, 3.3);
        $params = new CM_Params(array('vector3' => $vector3), false);
        $value = $params->getGeometryVector3('vector3');
        $this->assertInstanceOf('CM_Geometry_Vector3', $value);
        $this->assertSame(1.1, $value->getX());
        $this->assertSame(2.2, $value->getY());
        $this->assertSame(3.3, $value->getZ());

        $exception = $this->catchException(function () {
            $params = new CM_Params(array('vector3' => 'foo'), false);
            $params->getGeometryVector3('vector3');
        });
        $this->assertInstanceOf('CM_Exception_InvalidParam', $exception);
        $this->assertSame('Not enough parameters', $exception->getMessage());
    }

    public function testGetSession() {
        $session1 = CMTest_TH::createSession();
        $sessionId1 = $session1->getId();
        $session2 = CMTest_TH::createSession();
        $sessionId2 = $session2->getId();

        $params = new CM_Params(['foo' => $sessionId1, 'bar' => 'baz', 'baz' => $session2, 'quux' => 5], false);

        $session1 = $params->getSession('foo');
        $this->assertInstanceOf('CM_Session', $session1);
        $this->assertSame($sessionId1, $session1->getId());

        $exception = $this->catchException(function () use ($params) {
            $params->getSession('bar');
        });
        $this->assertInstanceOf('CM_Exception_InvalidParam', $exception);
        $this->assertSame('Session is not found', $exception->getMessage());

        $session2 = $params->getSession('baz');
        $this->assertInstanceOf('CM_Session', $session2);
        $this->assertSame($sessionId2, $session2->getId());

        $exception = $this->catchException(function () use ($params) {
            $params->getSession('quux');
        });
        $this->assertInstanceOf('CM_Exception_InvalidParam', $exception);
        $this->assertSame('Invalid param type for session', $exception->getMessage());
    }
}
