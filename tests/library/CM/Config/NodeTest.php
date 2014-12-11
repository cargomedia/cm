<?php

class CM_Config_NodeTest extends CMTest_TestCase {

    const TEST = 3;

    public function testNodeClass() {
        $reflection = new ReflectionClass('CM_Config_Node');
        $this->assertEmpty($reflection->getProperties());
    }

    public function testExport() {
        $node = new CM_Config_Node();
        $node->foo->bar->foo = 1;
        $node->foo->bar->bar = '1';
        $node->foo->bar->array = ['foo' => 3, 'CM_Config_NodeTest::TEST' => 2];

        $expected = new stdClass();
        $expected->foo = new stdClass();
        $expected->foo->bar = new stdClass();
        $expected->foo->bar->foo = 1;
        $expected->foo->bar->bar = '1';
        $expected->foo->bar->array = ['foo' => 3, CM_Config_NodeTest::TEST => 2];

        $this->assertEquals($expected, $node->export());
    }

    public function testExportAsString() {
        $node = new CM_Config_Node();
        $node->foo->bar->foo = 1;
        $node->foo->bar->bar = '1';
        $node->foo->bar->array = ['foo' => '3', 'CM_Config_NodeTest::TEST' => 2];
        $node->foo->bar->boolean = false;

        $expected = <<<'EOD'
$config->foo->bar->foo = 1;
$config->foo->bar->bar = '1';
$config->foo->bar->array = [];
$config->foo->bar->array['foo'] = '3';
$config->foo->bar->array[CM_Config_NodeTest::TEST] = 2;
$config->foo->bar->boolean = false;

EOD;
        $this->assertSame($expected, $node->exportAsString('$config'));
    }
}
