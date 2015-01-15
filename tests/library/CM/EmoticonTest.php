<?php

class CM_EmoticonTest extends CMTest_TestCase {

    public function testFilesystemScanning() {
        $emoticon1 = new CM_Emoticon('smiley');
        $emoticon2 = CM_Emoticon::findCode(':)');
        $this->assertEquals($emoticon1, $emoticon2);
    }

    public function testConstructorNonexistentEmoticon() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        try {
            $emoticonClass->newInstance(['nonexistentEmoticon']);
            $this->fail('Instantiated nonexistent emoticon');
        } catch(CM_Exception_Nonexistent $ex) {
            $this->assertSame('Nonexistent Emoticon', $ex->getMessage());
            $this->assertSame(['name' => 'nonexistentEmoticon'], $ex->getMetaInfo(true));
        }
    }

    public function testGetCodes() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['foo']);
        $this->assertSame([':foo:', '-(', '-()'], $emoticon->getCodes());
    }

    public function testGetDefaultCode() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['bar']);
        $this->assertSame(':bar:', $emoticon->getDefaultCode());
    }

    public function testGetFileName() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['bar']);
        $this->assertSame('bar.png', $emoticon->getFileName());
    }

    public function testGetName() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['foo']);
        $this->assertSame('foo', $emoticon->getName());
    }

    public function testFindCode() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['foo']);

        $className = $emoticonClass->getClassName();
        $this->assertEquals($emoticon, $className::findCode(':foo:'));
        $this->assertEquals($emoticon, $className::findCode('-('));
        $this->assertEquals($emoticon, $className::findCode('-()'));
        $this->assertNull($className::findCode('nonexistentCode'));
    }

    public function testFindName() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['bar']);

        $className = $emoticonClass->getClassName();
        $this->assertEquals($emoticon, $className::findName('bar'));
        $this->assertNull($className::findName('nonexistentEmoticon'));
    }

    /**
     * @return \Mocka\ClassMock
     */
    private function _getEmoticonMock() {
        $emoticonData =
            [
                'foo' =>
                    ['name'     => 'foo',
                     'fileName' => 'foo.png',
                     'codes'    => [':foo:', '-(', '-()']
                    ],
                'bar' =>
                    ['name'     => 'bar',
                     'fileName' => 'bar.png',
                     'codes'    => [':bar:', '-)']
                    ]
            ];
        $emoticonClass = $this->mockClass('CM_Emoticon');
        $emoticonClass->mockStaticMethod('getEmoticonData')->set($emoticonData);
        return $emoticonClass;
    }
}
