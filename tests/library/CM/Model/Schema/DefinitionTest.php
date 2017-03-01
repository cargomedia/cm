<?php

class CM_Model_Schema_DefinitionTest extends CMTest_TestCase {

    public function testIsEmpty() {
        $schema = new CM_Model_Schema_Definition(array());
        $this->assertTrue($schema->isEmpty());

        $schema = new CM_Model_Schema_Definition(array('foo' => array()));
        $this->assertFalse($schema->isEmpty());
    }

    public function testIsFloat() {
        $schema = new CM_Model_Schema_Definition(array());
        $_isFloat = CMTest_TH::getProtectedMethod('CM_Model_Schema_Definition', '_isFloat');

        $this->assertTrue($_isFloat->invoke($schema, '4.1'));
        $this->assertTrue($_isFloat->invoke($schema, '4.0'));
        $this->assertTrue($_isFloat->invoke($schema, '4'));
        $this->assertFalse($_isFloat->invoke($schema, '4.0.2'));
        $this->assertTrue($_isFloat->invoke($schema, 4E21));
        $this->assertTrue($_isFloat->invoke($schema, '4E21'));
    }

    public function testIsBoolean() {
        $schema = new CM_Model_Schema_Definition(array());
        $_isBoolean = CMTest_TH::getProtectedMethod('CM_Model_Schema_Definition', '_isBoolean');

        $this->assertTrue($_isBoolean->invoke($schema, true));
        $this->assertTrue($_isBoolean->invoke($schema, false));
        $this->assertTrue($_isBoolean->invoke($schema, '1'));
        $this->assertFalse($_isBoolean->invoke($schema, 'true'));
        $this->assertFalse($_isBoolean->invoke($schema, 'false'));
        $this->assertFalse($_isBoolean->invoke($schema, 1));
    }

    public function testIsInt() {
        $schema = new CM_Model_Schema_Definition(array());
        $_isInt = CMTest_TH::getProtectedMethod('CM_Model_Schema_Definition', '_isInt');

        $this->assertTrue($_isInt->invoke($schema, 1));
        $this->assertTrue($_isInt->invoke($schema, '1'));
        $this->assertFalse($_isInt->invoke($schema, 1.2));
        $this->assertFalse($_isInt->invoke($schema, '1.0'));
        $this->assertFalse($_isInt->invoke($schema, '4E2'));
        $this->assertFalse($_isInt->invoke($schema, 4E2));
    }

    public function testHasKey() {
        $schema = new  CM_Model_Schema_Definition(array(
            'foo' => array(),
            'bar' => array(),
        ));

        $this->assertTrue($schema->hasField('foo'));
        $this->assertTrue($schema->hasField(array('foo', 'xxxx')));
        $this->assertFalse($schema->hasField('xxxxx'));
        $this->assertFalse($schema->hasField(array('xxxx', 'yyyyy')));
    }

    public function testGetFieldNames() {
        $schema = new  CM_Model_Schema_Definition(array(
            'foo' => array(),
            'bar' => array(),
        ));

        $this->assertSame(array('foo', 'bar'), $schema->getFieldNames());
    }

    public function testValidateField() {
        $testDataList = array(
            // nothing
            array(
                'value'    => 12,
                'schema'   => array(),
                'expected' => true,
            ),
            array(
                'value'    => null,
                'schema'   => array(),
                'expected' => 'CM_Model_Exception_Validation',
            ),

            // optional
            array(
                'value'    => null,
                'schema'   => array('optional' => true),
                'expected' => true,
            ),

            // type integer
            array(
                'value'    => -12,
                'schema'   => array('type' => 'integer'),
                'expected' => true,
            ),
            array(
                'value'    => '-12',
                'schema'   => array('type' => 'integer'),
                'expected' => true,
            ),
            array(
                'value'    => 12.01,
                'schema'   => array('type' => 'integer'),
                'expected' => 'CM_Model_Exception_Validation',
            ),
            array(
                'value'    => '12abc',
                'schema'   => array('type' => 'integer'),
                'expected' => 'CM_Model_Exception_Validation',
            ),
            array(
                'value'    => 14,
                'schema'   => array('type' => 'int'),
                'expected' => true,
            ),

            // type string
            array(
                'value'    => 'foo bar',
                'schema'   => array('type' => 'string'),
                'expected' => true,
            ),
            array(
                'value'    => 'foo 繁體字 bar',
                'schema'   => array('type' => 'string'),
                'expected' => true,
            ),
            array(
                'value'    => '',
                'schema'   => array('type' => 'string'),
                'expected' => true,
            ),
            array(
                'value'    => 12,
                'schema'   => array('type' => 'string'),
                'expected' => 'CM_Model_Exception_Validation',
            ),

            // type float
            array(
                'value'    => -12,
                'schema'   => array('type' => 'float'),
                'expected' => true,
            ),
            array(
                'value'    => '-123',
                'schema'   => array('type' => 'float'),
                'expected' => true,
            ),
            array(
                'value'    => 12.01,
                'schema'   => array('type' => 'float'),
                'expected' => true,
            ),
            array(
                'value'    => '12.01',
                'schema'   => array('type' => 'float'),
                'expected' => true,
            ),
            array(
                'value'    => '12abc',
                'schema'   => array('type' => 'float'),
                'expected' => 'CM_Model_Exception_Validation',
            ),

            // type boolean
            array(
                'value'    => true,
                'schema'   => array('type' => 'boolean'),
                'expected' => true,
            ),
            array(
                'value'    => false,
                'schema'   => array('type' => 'boolean'),
                'expected' => true,
            ),
            array(
                'value'    => '1',
                'schema'   => array('type' => 'boolean'),
                'expected' => true,
            ),
            array(
                'value'    => '0',
                'schema'   => array('type' => 'boolean'),
                'expected' => true,
            ),
            array(
                'value'    => 1,
                'schema'   => array('type' => 'boolean'),
                'expected' => 'CM_Model_Exception_Validation',
            ),
            array(
                'value'    => 'true',
                'schema'   => array('type' => 'boolean'),
                'expected' => 'CM_Model_Exception_Validation',
            ),
            array(
                'value'    => '00',
                'schema'   => array('type' => 'boolean'),
                'expected' => 'CM_Model_Exception_Validation',
            ),
            array(
                'value'    => true,
                'schema'   => array('type' => 'bool'),
                'expected' => true,
            ),

            // type array
            array(
                'value'    => array('foo' => 'bar'),
                'schema'   => array('type' => 'array'),
                'expected' => true,
            ),
            array(
                'value'    => '123',
                'schema'   => array('type' => 'array'),
                'expected' => 'CM_Model_Exception_Validation',
            ),

            // type DateTime
            array(
                'value'    => 1378904141,
                'schema'   => array('type' => 'DateTime'),
                'expected' => true,
            ),

            // type model
            array(
                'value'    => '{"id": 3}',
                'schema'   => array('type' => 'CM_Model_Mock_Validation'),
                'expected' => true,
            ),
            array(
                'value'    => '3',
                'schema'   => array('type' => 'CM_Model_Mock_Validation'),
                'expected' => true,
            ),
            array(
                'value'    => '{"id": 4, "foo": "bar"}',
                'schema'   => array('type' => 'CM_Model_Mock_Validation'),
                'expected' => true,
            ),
            array(
                'value'    => '{"bar": 4, "foo": "bar"}',
                'schema'   => array('type' => 'CM_Model_Mock_Validation'),
                'expected' => 'CM_Model_Exception_Validation',
            ),
            array(
                'value'    => '1',
                'schema'   => array('type' => 'CM_Class_Abstract'),
                'expected' => 'CM_Exception_Invalid',
            ),
            array(
                'value'    => 'mongo123mixed321id',
                'schema'   => array('type' => 'CM_Model_Mock_Validation'),
                'expected' => true,
            ),

            array(
                'value'    => '{"foo":"bar"}',
                'schema'   => array('type' => $this->mockInterface('CM_ArrayConvertible')->getClassName()),
                'expected' => true,
            ),

            array(
                'value'    => 'invalid-json',
                'schema'   => array('type' => $this->mockInterface('CM_ArrayConvertible')->getClassName()),
                'expected' => 'CM_Model_Exception_Validation',
            ),

            // type invalid
            array(
                'value'    => -12,
                'schema'   => array('type' => 'invalid987628436'),
                'expected' => 'CM_Exception_Invalid',
            ),
        );
        foreach ($testDataList as $testData) {
            $schema = new CM_Model_Schema_Definition(array('foo' => $testData['schema']));
            try {
                $schema->validateField('foo', $testData['value']);
                $this->assertSame($testData['expected'], true, 'Validation failure (' . CM_Util::var_line($testData) . ')');
            } catch (CM_Exception $e) {
                $this->assertSame($testData['expected'], get_class($e), 'Validation failure (' . CM_Util::var_line($testData) . ')');
            }
        }
    }

    public function testDecode() {
        CM_Config::get()->CM_Model_Abstract->types[CM_Model_Mock_Validation::getTypeStatic()] = 'CM_Model_Mock_Validation';
        CM_Config::get()->CM_Model_Abstract->types[CM_Model_Mock_Validation2::getTypeStatic()] = 'CM_Model_Mock_Validation2';
        $testDataList = array(
            // nothing
            array(
                'value'       => 12,
                'schema'      => array(),
                'returnValue' => 12,
            ),

            // optional
            array(
                'value'       => null,
                'schema'      => array('optional' => true),
                'returnValue' => null,
            ),

            // type integer
            array(
                'value'       => -12,
                'schema'      => array('type' => 'integer'),
                'returnValue' => -12,
            ),
            array(
                'value'       => '-12',
                'schema'      => array('type' => 'integer'),
                'returnValue' => -12,
            ),
            array(
                'value'       => 14,
                'schema'      => array('type' => 'int'),
                'returnValue' => 14,
            ),

            // type string
            array(
                'value'       => 'foo bar',
                'schema'      => array('type' => 'string'),
                'returnValue' => 'foo bar',
            ),
            array(
                'value'       => 'foo 繁體字 bar',
                'schema'      => array('type' => 'string'),
                'returnValue' => 'foo 繁體字 bar',
            ),
            array(
                'value'       => '',
                'schema'      => array('type' => 'string'),
                'returnValue' => '',
            ),
            array(
                'value'       => 123,
                'schema'      => array('type' => 'string'),
                'returnValue' => '123',
            ),

            // type float
            array(
                'value'       => -12,
                'schema'      => array('type' => 'float'),
                'returnValue' => -12.0,
            ),
            array(
                'value'       => '-123',
                'schema'      => array('type' => 'float'),
                'returnValue' => -123.0,
            ),
            array(
                'value'       => 12.01,
                'schema'      => array('type' => 'float'),
                'returnValue' => 12.01,
            ),
            array(
                'value'       => '12.01',
                'schema'      => array('type' => 'float'),
                'returnValue' => 12.01,
            ),

            // type boolean
            array(
                'value'       => true,
                'schema'      => array('type' => 'boolean'),
                'returnValue' => true,
            ),
            array(
                'value'       => false,
                'schema'      => array('type' => 'boolean'),
                'returnValue' => false,
            ),
            array(
                'value'       => '1',
                'schema'      => array('type' => 'boolean'),
                'returnValue' => true,
            ),
            array(
                'value'       => '0',
                'schema'      => array('type' => 'boolean'),
                'returnValue' => false,
            ),
            array(
                'value'       => true,
                'schema'      => array('type' => 'bool'),
                'returnValue' => true,
            ),

            // type array
            array(
                'value'       => array('foo' => 'bar'),
                'schema'      => array('type' => 'array'),
                'returnValue' => array('foo' => 'bar'),
            ),

            // type DateTime
            array(
                'value'       => 1378904141,
                'schema'      => array('type' => 'DateTime'),
                'returnValue' => DateTime::createFromFormat('U', 1378904141),
            ),

            // type model
            array(
                'value'       => '{"id": 3}',
                'schema'      => array('type' => 'CM_Model_Mock_Validation'),
                'returnValue' => new CM_Model_Mock_Validation(3),
            ),
            array(
                'value'       => '2',
                'schema'      => array('type' => 'CM_Model_Mock_Validation'),
                'returnValue' => new CM_Model_Mock_Validation(2),
            ),
            array(
                'value'       => '{"id": 4, "foo": "bar"}',
                'schema'      => array('type' => 'CM_Model_Mock_Validation'),
                'returnValue' => new CM_Model_Mock_Validation(4, 'bar'),
            ),
            array(
                'value'       => '{"id": "4", "foo": "bar"}',
                'schema'      => array('type' => 'CM_Model_Mock_Validation2'),
                'returnValue' => new CM_Model_Mock_Validation2('4', 'bar'),
            ),
            array(
                'value'       => 'mongo123mixed321id',
                'schema'      => array('type' => 'CM_Model_Mock_Validation'),
                'returnValue' => new CM_Model_Mock_Validation('mongo123mixed321id'),
            ),
        );
        foreach ($testDataList as $testData) {
            $schema = new CM_Model_Schema_Definition(array('foo' => $testData['schema']));
            $value = $schema->decodeField('foo', $testData['value']);
            if (is_object($testData['returnValue'])) {
                $this->assertEquals($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
            } else {
                $this->assertSame($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
            }
        }
    }

    public function testEncode() {
        CM_Config::get()->CM_Model_Abstract->types[CM_Model_Mock_Validation::getTypeStatic()] = 'CM_Model_Mock_Validation';
        CM_Config::get()->CM_Model_Abstract->types[CM_Model_Mock_Validation2::getTypeStatic()] = 'CM_Model_Mock_Validation2';
        $testDataList = array(
            // nothing
            array(
                'value'       => 12,
                'schema'      => array(),
                'returnValue' => 12,
            ),

            // optional
            array(
                'value'       => null,
                'schema'      => array('optional' => true),
                'returnValue' => null,
            ),

            // type integer
            array(
                'value'       => -12,
                'schema'      => array('type' => 'integer'),
                'returnValue' => -12,
            ),
            array(
                'value'       => '-12',
                'schema'      => array('type' => 'integer'),
                'returnValue' => -12,
            ),
            array(
                'value'       => 14,
                'schema'      => array('type' => 'int'),
                'returnValue' => 14,
            ),

            // type string
            array(
                'value'       => 'foo bar',
                'schema'      => array('type' => 'string'),
                'returnValue' => 'foo bar',
            ),
            array(
                'value'       => 'foo 繁體字 bar',
                'schema'      => array('type' => 'string'),
                'returnValue' => 'foo 繁體字 bar',
            ),
            array(
                'value'       => '',
                'schema'      => array('type' => 'string'),
                'returnValue' => '',
            ),
            array(
                'value'       => 123,
                'schema'      => array('type' => 'string'),
                'returnValue' => '123',
            ),

            // type float
            array(
                'value'       => -12,
                'schema'      => array('type' => 'float'),
                'returnValue' => -12.0,
            ),
            array(
                'value'       => '-123',
                'schema'      => array('type' => 'float'),
                'returnValue' => -123.0,
            ),
            array(
                'value'       => 12.01,
                'schema'      => array('type' => 'float'),
                'returnValue' => 12.01,
            ),
            array(
                'value'       => '12.01',
                'schema'      => array('type' => 'float'),
                'returnValue' => 12.01,
            ),

            // type boolean
            array(
                'value'       => true,
                'schema'      => array('type' => 'boolean'),
                'returnValue' => true,
            ),
            array(
                'value'       => false,
                'schema'      => array('type' => 'boolean'),
                'returnValue' => false,
            ),
            array(
                'value'       => '1',
                'schema'      => array('type' => 'boolean'),
                'returnValue' => true,
            ),
            array(
                'value'       => '0',
                'schema'      => array('type' => 'boolean'),
                'returnValue' => false,
            ),
            array(
                'value'       => true,
                'schema'      => array('type' => 'bool'),
                'returnValue' => true,
            ),

            // type array
            array(
                'value'       => array('foo' => 'bar'),
                'schema'      => array('type' => 'array'),
                'returnValue' => array('foo' => 'bar'),
            ),

            // type DateTime
            array(
                'value'       => DateTime::createFromFormat('U', 1378904141),
                'schema'      => array('type' => 'DateTime'),
                'returnValue' => 1378904141,
            ),

            // type model
            array(
                'value'       => new CM_Model_Mock_Validation(2),
                'schema'      => array('type' => 'CM_Model_Mock_Validation'),
                'returnValue' => 2,
            ),
            array(
                'value'       => new CM_Model_Mock_Validation(4, 'bar'),
                'schema'      => array('type' => 'CM_Model_Mock_Validation'),
                'returnValue' => '{"id":"4","foo":"bar"}',
            ),
            array(
                'value'       => new CM_Model_Mock_Validation2(2),
                'schema'      => array('type' => 'CM_Model_Mock_Validation2'),
                'returnValue' => '2',
            ),
        );
        foreach ($testDataList as $testData) {
            $schema = new CM_Model_Schema_Definition(array('foo' => $testData['schema']));
            $value = $schema->encodeField('foo', $testData['value']);
            if (is_object($testData['returnValue'])) {
                $this->assertEquals($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
            } else {
                $this->assertSame($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
            }
        }
    }

    public function testEncodeArrayConvertible() {
        $class = $this->mockInterface('CM_ArrayConvertible');
        $className = $class->getClassName();
        $arrayConvertible = $class->newInstanceWithoutConstructor();
        $toArray = ['key' => 'value'];
        $arrayConvertible->mockMethod('toArray')->set($toArray);
        $schema = new CM_Model_Schema_Definition([
            'arrayConvertible' => ['type' => $className]
        ]);
        $value = $schema->encodeField('arrayConvertible', $arrayConvertible);
        $this->assertSame('{"key":"value"}', $value);
    }

    public function testDecodeArrayConvertible() {
        $class = $this->mockInterface('CM_ArrayConvertible');
        $className = $class->getClassName();
        $arrayConvertible = $class->newInstanceWithoutConstructor();
        $fromArray = $class->mockMethod('fromArray')->set($arrayConvertible);
        $schema = new CM_Model_Schema_Definition([
            'arrayConvertible' => ['type' => $className]
        ]);
        $jsonData = '{"key":"value"}';
        $value = $schema->decodeField('arrayConvertible', $jsonData);

        $this->assertSame(['key' => 'value'], $fromArray->getCalls()->getLast()->getArgument(0));
        $this->assertSame($arrayConvertible, $value);
    }

    public function testEncodeInvalidModel() {
        $schema = new CM_Model_Schema_Definition(array('foo' => array('type' => 'CM_Model_Mock_Validation2')));
        $exception = $this->catchException(function () use ($schema) {
            $schema->encodeField('foo', 'bar');
        });

        $this->assertInstanceOf('CM_Model_Exception_Validation', $exception);
        /** @var CM_Model_Exception_Validation $exception */
        $this->assertSame('Value is not an instance of the class', $exception->getMessage());
        $this->assertSame(
            [
                'value'     => 'bar',
                'className' => 'CM_Model_Mock_Validation2',
            ],
            $exception->getMetaInfo()
        );
    }

    public function testEncodeInvalidClass() {
        $schema = new CM_Model_Schema_Definition(array('foo' => array('type' => 'CM_Class_Abstract')));
        $exception = $this->catchException(function () use ($schema) {
            $schema->encodeField('foo', 'bar');
        });
        $this->assertInstanceOf('CM_Model_Exception_Validation', $exception);
        /** @var CM_Model_Exception_Validation $exception */
        $this->assertSame('Value is not an instance of the class', $exception->getMessage());
        $this->assertSame(
            [
                'value'     => 'bar',
                'className' => 'CM_Class_Abstract',
            ],
            $exception->getMetaInfo()
        );
    }
}

class CM_Model_Mock_Validation extends CM_Model_Abstract {

    public function __construct($id, $foo = null) {
        $id = array('id' => $id);
        if (null !== $foo) {
            $id['foo'] = $foo;
        }
        $this->_construct($id);
    }

    protected function _loadData() {
        return array('id' => $this->getId());
    }

    public static function getTypeStatic() {
        return 1;
    }
}

class CM_Model_Mock_Validation2 extends CM_Model_Mock_Validation {

    /**
     * @return string
     */
    public function getId() {
        return (string) $this->_getIdKey('id');
    }

    public static function getTypeStatic() {
        return 2;
    }
}
