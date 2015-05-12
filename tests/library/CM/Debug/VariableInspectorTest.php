<?php

class CM_Debug_VariableInspectorTest extends CMTest_TestCase {

    /**
     * @dataProvider debugInfoProvider
     *
     * @param string $expected
     * @param mixed  $argument
     */
    public function testGetDebugInfo($expected, $argument) {
        $variableInspector = new CM_Debug_VariableInspector();
        $this->assertSame($expected, $variableInspector->getDebugInfo($argument));
    }

    public function debugInfoProvider() {
        return [
            ['[]', []],
            ['[]', ['foo' => 12]],
            ['true', true],
            ['12', 12],
            ['-12', -12],
            ["'foo'", 'foo'],
            ['object', new stdClass()],
            ['SplFixedArray', new SplFixedArray()],
        ];
    }

    public function testGetDebugInfoLengthMax() {
        $variableInspector = new CM_Debug_VariableInspector();
        $this->assertSame("'foo...'", $variableInspector->getDebugInfo('fooo', ['lengthMax' => 3]));
        $this->assertSame("'fooo'", $variableInspector->getDebugInfo('fooo', ['lengthMax' => 4]));
        $this->assertSame("'fooo'", $variableInspector->getDebugInfo('fooo', ['lengthMax' => null]));
    }

    /**
     * @dataProvider debugInfoProviderRecursive
     *
     * @param string $expected
     * @param mixed  $argument
     */
    public function testGetDebugInfoRecursive($expected, $argument) {
        $variableInspector = new CM_Debug_VariableInspector();
        $this->assertSame($expected, $variableInspector->getDebugInfo($argument, ['recursive' => true]));
    }

    public function debugInfoProviderRecursive() {
        return [
            ['[]', []],
            ["['foo' => 12]", ['foo' => 12]],
            ["['foo' => SplFixedArray]", ['foo' => new SplFixedArray()]],
            ["['foo' => ['bar' => 12, 0 => 13]]", ['foo' => ['bar' => 12, 13]]],
        ];
    }
}
