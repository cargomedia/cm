<?php

class CM_EmoticonTest extends CMTest_TestCase {

    public function testFilesystemScanning() {
        $emoticon1 = new CM_Emoticon('smiley');
        $emoticon2 = CM_Emoticon::findByCode(':)');
        $this->assertEquals($emoticon1, $emoticon2);
    }

    public function testConstructorNonexistentEmoticon() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        try {
            $emoticonClass->newInstance(['nonexistentEmoticon']);
            $this->fail('Instantiated nonexistent emoticon');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertSame('Nonexistent Emoticon', $ex->getMessage());
            $this->assertSame(['name' => 'nonexistentEmoticon'], $ex->getMetaInfo());
        }
    }

    public function testConstructorDataInjection() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticonRegular */
        $emoticonRegular = $emoticonClass->newInstance(['foo']);
        $emoticonInjected = $emoticonClass->newInstance(['foo', ['name'     => 'foo',
                                                                 'fileName' => 'foo.png',
                                                                 'codes'    => [':foo:', '-(', '-()']]
        ]);
        $this->assertEquals($emoticonInjected, $emoticonRegular);
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

    public function testFindByCode() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['foo']);

        /** @var CM_Emoticon $className */
        $className = $emoticonClass->getClassName();
        $this->assertEquals($emoticon, $className::findbyCode(':foo:'));
        $this->assertEquals($emoticon, $className::findbyCode('-('));
        $this->assertEquals($emoticon, $className::findbyCode('-()'));
        $this->assertNull($className::findbyCode('nonexistentCode'));
    }

    public function testFindByName() {
        $emoticonClass = $this->_getEmoticonMock();
        /** @var CM_Emoticon $emoticon */
        $emoticon = $emoticonClass->newInstance(['bar']);

        /** @var CM_Emoticon $className */
        $className = $emoticonClass->getClassName();
        $this->assertEquals($emoticon, $className::findByName('bar'));
        $this->assertNull($className::findByName('nonexistentEmoticon'));
    }

    /**
     * @return \Mocka\Classes\ClassMock
     */
    private function _getEmoticonMock() {
        $dataList =
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
        $emoticonClass->mockMethod('getEmoticonData')->set($dataList);
        return $emoticonClass;
    }
}
