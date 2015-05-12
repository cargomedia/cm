<?php

class CM_Debug_VariableInspectorTest extends CMTest_TestCase {

    /**
     * @dataProvider debugInfoProvider
     *
     * @param string $expected
     * @param mixed  $argument
     */
    public function testGetDebugInfo($expected, $argument) {
        $this->assertSame($expected, CM_Util::varDump($argument));
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
        $this->assertSame("'foo...'", CM_Util::varDump('fooo', ['lengthMax' => 3]));
        $this->assertSame("'fooo'", CM_Util::varDump('fooo', ['lengthMax' => 4]));
        $this->assertSame("'fooo'", CM_Util::varDump('fooo', ['lengthMax' => null]));
    }

    /**
     * @dataProvider debugInfoProviderRecursive
     *
     * @param string $expected
     * @param mixed  $argument
     */
    public function testGetDebugInfoRecursive($expected, $argument) {
        $this->assertSame($expected, CM_Util::varDump($argument, ['recursive' => true]));
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
