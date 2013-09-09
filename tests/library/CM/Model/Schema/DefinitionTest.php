<?php

class CM_Model_Schema_DefinitionTest extends CMTest_TestCase {

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

			// type model
			array(
				'value'    => '{"id": 3}',
				'schema'   => array('type' => 'CM_Model_Mock_Validation'),
				'expected' => true,
			),
			array(
				'value'    => '{"id": "foo"}',
				'schema'   => array('type' => 'CM_Model_Mock_Validation'),
				'expected' => 'CM_Model_Exception_Validation',
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
		CM_Config::get()->CM_Model_Abstract->types[CM_Model_Mock_Validation::TYPE] = 'CM_Model_Mock_Validation';
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
		);
		foreach ($testDataList as $testData) {
			$schema = new CM_Model_Schema_Definition(array('foo' => $testData['schema']));
			$value = $schema->decodeField('foo', $testData['value']);
			if ($testData['returnValue'] instanceof CM_Model_Abstract) {
				$this->assertEquals($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
			} else {
				$this->assertSame($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
			}
		}
	}

	public function testEncode() {
		CM_Config::get()->CM_Model_Abstract->types[CM_Model_Mock_Validation::TYPE] = 'CM_Model_Mock_Validation';
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

			// type model
			array(
				'value'       => new CM_Model_Mock_Validation(2),
				'schema'      => array('type' => 'CM_Model_Mock_Validation'),
				'returnValue' => '2',
			),
			array(
				'value'       => new CM_Model_Mock_Validation(4, 'bar'),
				'schema'      => array('type' => 'CM_Model_Mock_Validation'),
				'returnValue' => '{"id":4,"foo":"bar"}',
			),
		);
		foreach ($testDataList as $testData) {
			$schema = new CM_Model_Schema_Definition(array('foo' => $testData['schema']));
			$value = $schema->encodeField('foo', $testData['value']);
			if ($testData['returnValue'] instanceof CM_Model_Abstract) {
				$this->assertEquals($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
			} else {
				$this->assertSame($testData['returnValue'], $value, 'Unexpected return value (' . CM_Util::var_line($testData) . ')');
			}
		}
	}
}

class CM_Model_Mock_Validation extends CM_Model_Abstract {

	const TYPE = 1;

	public function __construct($id, $foo = null) {
		$id = array('id' => (int) $id);
		if (null !== $foo) {
			$id['foo'] = $foo;
		}
		$this->_construct($id);
	}

	protected function _loadData() {
		return array('id' => $this->getId());
	}
}
