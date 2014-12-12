<?php

class CM_Config_NodeTest extends CMTest_TestCase {

    const TEST = 3;

    public function testNodeClass() {
        $reflection = new ReflectionClass('CM_Config_Node');
        $this->assertEmpty($reflection->getProperties());
    }

    public function testExtendWithConfig() {
        $base = new CM_Config_Node();
        $base->foo->bar->foo = 1;
        $base->foo->bar->bar = 1;
        $base->foo->bar->arrayAssoc = ['foo' => 3, 'bar' => 2];
        $base->foo->bar->arrayNumeric = [1, 2, 3];

        $extension = new CM_Config_Node();
        $extension->bar = 'foo';
        $extension->foo->bar->bar = 2;
        $extension->foo->bar->arrayAssoc = ['foo' => 2];
        $extension->foo->bar->arrayNumeric = [2, 3];

        $expected = new CM_Config_Node();
        $expected->foo->bar->foo = 1;
        $expected->foo->bar->bar = 2;
        $expected->bar = 'foo';
        $expected->foo->bar->arrayAssoc = ['foo' => 2, 'bar' => 2];
        $expected->foo->bar->arrayNumeric = [2, 3, 3];

        $actual = clone $base;

        $actual->extendWithConfig($extension);
        $this->assertEquals($expected, $actual);

        $actual = clone $base;
        $actual->extendWithConfig($extension->export());
        $this->assertEquals($expected, $actual);

    }

    public function testExport() {
        $node = new CM_Config_Node();
        $node->foo->bar->foo = 1;
        $node->foo->bar->bar = '1';
        $node->foo->bar->array = ['foo' => 3, 'CM_Config_NodeTest::TEST' => 2, 'CM_Config_NodeTest::NONEXISTENT' => 1, 'NonexistentClass::FOO' => 0];

        $expected = new stdClass();
        $expected->foo = new stdClass();
        $expected->foo->bar = new stdClass();
        $expected->foo->bar->foo = 1;
        $expected->foo->bar->bar = '1';
        $expected->foo->bar->array = ['foo' => 3, CM_Config_NodeTest::TEST => 2, 'CM_Config_NodeTest::NONEXISTENT' => 1, 'NonexistentClass::FOO' => 0];

        $this->assertEquals($expected, $node->export());
    }

    public function testExportAsString() {
        $node = new CM_Config_Node();
        $node->foo->bar->foo = 1;
        $node->foo->bar->bar = '1';
        $node->foo->bar->array = ['foo' => '3', 'CM_Config_NodeTest::TEST' => 2, 'CM_Config_NodeTest::NONEXISTENT' => 1, 'NonexistentClass::FOO' => 0];
        $node->foo->bar->boolean = false;

        $expected = <<<'EOD'
$config->foo->bar->foo = 1;
$config->foo->bar->bar = '1';
$config->foo->bar->array = [];
$config->foo->bar->array['foo'] = '3';
$config->foo->bar->array[CM_Config_NodeTest::TEST] = 2;
$config->foo->bar->array['CM_Config_NodeTest::NONEXISTENT'] = 1;
$config->foo->bar->array['NonexistentClass::FOO'] = 0;
$config->foo->bar->boolean = false;

EOD;
        $this->assertSame($expected, $node->exportAsString('$config'));
    }
}
