<?php

class CM_Frontend_JsonSerializableTest extends CMTest_TestCase {

    public function testJsonSerialize() {
        $obj = new CM_Frontend_JsonSerializable();
        $this->assertEquals('[]', json_encode($obj));
        $this->assertSame(['_class' => 'CM_Frontend_JsonSerializable'], CM_Params::encode($obj));

        $obj->setData(['foo' => 1]);
        $this->assertEquals('{"foo":1}', json_encode($obj));
        $this->assertSame(['_class' => 'CM_Frontend_JsonSerializable', 'foo' => 1], CM_Params::encode($obj));

        $obj->setData([
            'bar' => new CM_Frontend_JsonSerializable(['foo' => 1])
        ]);
        $this->assertEquals('{"bar":{"foo":1}}', json_encode($obj));
        $this->assertSame([
            '_class' => 'CM_Frontend_JsonSerializable',
            'bar'    => [
                '_class' => 'CM_Frontend_JsonSerializable',
                'foo'    => 1
            ]
        ], CM_Params::encode($obj));
    }
}
