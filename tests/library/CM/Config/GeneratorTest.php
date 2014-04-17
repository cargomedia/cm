<?php

class CM_Config_GeneratorTest extends CMTest_TestCase {

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
