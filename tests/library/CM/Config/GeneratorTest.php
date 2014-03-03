<?php

class CM_Config_GeneratorTest extends CMTest_TestCase {

  public function testGenerateOutput() {
    $source = <<< "EOD"
{
	"foo": {
		"foo": 12,
		"bar": [1,2,3,5,6,7]
	},
	"bar": "lol",
	"foobar" : {
		"foo": {
			"foo": "bar",
			"bar": "foo",
			"foobar": 1
		}
	}
}
EOD;
    $expected = <<< 'EOD'
<?php
$config->CM_Foo->foo = 12;
$config->CM_Foo->bar = array (
  0 => 1,
  1 => 2,
  2 => 3,
  3 => 5,
  4 => 6,
  5 => 7,
);
$config->bar = 'lol';
$config->CM_FooBar->foo = array (
  'foo' => 'bar',
  'bar' => 'foo',
  'foobar' => 1,
);

EOD;
    $map = array(
      'foo'    => 'CM_Foo',
      'foobar' => 'CM_FooBar'
    );
    $sourceFile = $this->getMockBuilder('CM_File')->disableOriginalConstructor()->setMethods(array('read'))->getMock();
    $sourceFile->expects($this->any())->method('read')->will($this->returnValue($source));
    $mapping = $this->getMockBuilder('CM_Config_Mapping')->setMethods(array('_getMapping'))->getMock();
    $mapping->expects($this->any())->method('_getMapping')->will($this->returnValue($map));
    $generator = $this->getMockBuilder('CM_Config_Generator')->setMethods(array('_getMapping'))->getMock();
    $generator->expects($this->any())->method('_getMapping')->will($this->returnValue($mapping));
    /** @var CM_Config_Generator $generator */
    $this->assertSame($expected, $generator->generateMappedOutput($sourceFile));
  }

  public function testGenerateConfigActionVerbs() {
    if (CM_Config::get()->CM_Action_Abstract->verbsMaxValue) {
      unset(CM_Config::get()->CM_Action_Abstract->verbsMaxValue);
    }
    if (CM_Config::get()->CM_Action_Abstract->verbs) {
      unset(CM_Config::get()->CM_Action_Abstract->verbs);
    }
    $actionVerbs = array(
      array(
        'name'      => 'CREATE',
        'value'     => 'Create',
        'className' => 'CM_Action_Abstract',
      ),
      array(
        'name'      => 'UPDATE',
        'value'     => 'Update',
        'className' => 'CM_Action_Abstract',
      )
    );
    $expected = <<<'EOD'
$config->CM_Action_Abstract->verbs = array();
$config->CM_Action_Abstract->verbs[CM_Action_Abstract::CREATE] = 1;
$config->CM_Action_Abstract->verbs[CM_Action_Abstract::UPDATE] = 2;
$config->CM_Action_Abstract->verbsMaxValue = 2;

EOD;
    $generator = $this->getMockBuilder('CM_Config_Generator')->setMethods(array('getActionVerbs'))->getMock();
    $generator->expects($this->any())->method('getActionVerbs')->will($this->returnValue($actionVerbs));

    /** @var CM_Config_Generator $generator */
    $this->assertSame($expected, $generator->generateConfigActionVerbs());
    $this->assertSame($expected, $generator->generateConfigActionVerbs());
  }

  public function testGenerateConfigActionVerbsMaxValue() {
    CM_Config::get()->CM_Action_Abstract->verbsMaxValue = 5;
    CM_Config::get()->CM_Action_Abstract->verbs = array();
    $actionVerbs = array(
      array(
        'name'      => 'CREATE',
        'value'     => 'Create',
        'className' => 'CM_Action_Abstract',
      )
    );
    $expected = <<<'EOD'
$config->CM_Action_Abstract->verbs = array();
$config->CM_Action_Abstract->verbs[CM_Action_Abstract::CREATE] = 6;
$config->CM_Action_Abstract->verbsMaxValue = 6;

EOD;
    $generator = $this->getMockBuilder('CM_Config_Generator')->setMethods(array('getActionVerbs'))->getMock();
    $generator->expects($this->any())->method('getActionVerbs')->will($this->returnValue($actionVerbs));

    /** @var CM_Config_Generator $generator */
    $this->assertSame($expected, $generator->generateConfigActionVerbs());
  }
}
