<?php

class CM_UtilTest extends CMTest_TestCase {

    public function testBenchmark() {
        $this->assertSame(0.0, (float) CM_Util::benchmark());
        $this->assertSame(0.0, (float) CM_Util::benchmark('CM'));

        CMTest_TH::timeForward(1);

        $this->assertGreaterThan(0, (float) CM_Util::benchmark());
        $this->assertGreaterThan(0, (float) CM_Util::benchmark('CM'));
    }

    public function testGetClasses() {
        $classPaths = array(
            'CM_Class_Abstract'         => 'CM/Class/Abstract.php',
            'CM_Paging_Abstract'        => 'CM/Paging/Abstract.php',
            'CM_Paging_Action_Abstract' => 'CM/Paging/Action/Abstract.php',
            'CM_Paging_Action_User'     => 'CM/Paging/Action/User.php',
        );
        foreach ($classPaths as $className => &$path) {
            $path = CM_Util::getModulePath(CM_Util::getNamespace($className)) . 'library/' . $path;
        }
        $paths = array_reverse($classPaths);
        $this->assertSame(array_flip($classPaths), CM_Util::getClasses($paths));
    }

    public function testGetNamespace() {
        $this->assertInternalType('string', CM_Util::getNamespace('CM_Util'));

        $this->assertNull(CM_Util::getNamespace('NoNamespace', true));

        try {
            CM_Util::getNamespace('NoNamespace', false);
            $this->fail('Namespace detected in a className without namespace.');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Could not detect namespace', $ex->getMessage());
            $this->assertSame(['className' => 'NoNamespace'], $ex->getMetaInfo());
        }
    }

    public function testTitleize() {
        $testCases = array(
            'foo'     => 'Foo',
            'Foo'     => 'Foo',
            'foo bar' => 'Foo Bar',
            'foo-bar' => 'Foo Bar',
            'foo.bar' => 'Foo.bar',
        );
        foreach ($testCases as $actual => $expected) {
            $this->assertSame($expected, CM_Util::titleize($actual));
        }
    }

    public function testGetResourceFiles() {
        $files = CM_Util::getResourceFiles('config/default.php');
        if (!count($files)) {
            $this->markTestSkipped('There are no files to test this functionality');
        }
        foreach ($files as $file) {
            $this->assertInstanceOf('CM_File', $file);
            $this->assertSame('default.php', $file->getFileName());
        }
    }

    public function testGetResourceFilesGlob() {
        $files = CM_Util::getResourceFiles('config/*.php');
        if (!count($files)) {
            $this->markTestSkipped('There are no files to test this functionality');
        }
        $defaultFile = \Functional\first($files, function ($file) {
            return 'default.php' === $file->getFileName();
        });
        $this->assertNotNull($defaultFile);
        $this->assertInstanceOf('CM_File', $defaultFile);
    }

    public function testGetArrayTree() {
        $array = array(array('id' => 1, 'type' => 1, 'amount' => 1), array('id' => 2, 'type' => 1, 'amount' => 2),
            array('id' => 3, 'type' => 1, 'amount' => 3), array('id' => 4, 'type' => 1, 'amount' => 4));

        $this->assertSame(array(1 => array('type' => 1, 'amount' => 1), 2 => array('type' => 1, 'amount' => 2),
                                3 => array('type' => 1, 'amount' => 3), 4 => array('type' => 1, 'amount' => 4)), CM_Util::getArrayTree($array));

        $this->assertSame(array(1 => array('id' => 1, 'type' => 1), 2 => array('id' => 2, 'type' => 1), 3 => array('id' => 3, 'type' => 1),
                                4 => array('id' => 4, 'type' => 1)), CM_Util::getArrayTree($array, 1, true, 'amount'));

        $this->assertSame(array(1 => array(1 => 1), 2 => array(1 => 2), 3 => array(1 => 3),
                                4 => array(1 => 4)), CM_Util::getArrayTree($array, 2, true, array('amount', 'type')));

        try {
            CM_Util::getArrayTree($array, 1, true, 'foo');
            $this->fail('Item has key `foo`.');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Item has no key.', $ex->getMessage());
            $this->assertSame(['key' => 'foo'], $ex->getMetaInfo());
        }

        try {
            CM_Util::getArrayTree(array(1, 2));
            $this->fail('Item is not an array.');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Item is not an array or has less elements elements than needed.', $ex->getMessage());
            $this->assertSame(['levelsNeeded' => 2], $ex->getMetaInfo());
        }

        try {
            CM_Util::getArrayTree(array(array(1), array(2)));
            $this->fail('Item has less than two elements.');
        } catch (CM_Exception_Invalid $ex) {
            $this->assertContains('Item is not an array or has less elements elements than needed.', $ex->getMessage());
        }
    }

    public function testParseXml() {
        $xml = CM_Util::parseXml('<?xml version="1.0" encoding="utf-8"?><document><foo>bar</foo></document>');
        $this->assertInstanceOf('SimpleXMLElement', $xml);
        $this->assertSame('bar', (string) $xml->foo);

        try {
            CM_Util::parseXml('invalid xml');
            $this->fail('No exception for invalid xml');
        } catch (CM_Exception_Invalid $e) {
            $this->assertTrue(true);
        }
    }

    public function testJsonEncode() {
        $actual = CM_Util::jsonEncode(['foo' => 'bar']);
        $this->assertSame('{"foo":"bar"}', $actual);
    }

    public function testJsonEncodePrettyPrint() {
        $actual = CM_Util::jsonEncode(['foo' => 'bar'], true);
        $this->assertSame('{' . "\n" . '    "foo": "bar"' . "\n" . '}', $actual);
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testJsonEncodeInvalid() {
        $resource = fopen(sys_get_temp_dir(), 'r');
        CM_Util::jsonEncode(['foo' => $resource]);
    }

    /**
     * @expectedException CM_Exception_Invalid
     */
    public function testJsonDecodeInvalid() {
        CM_Util::jsonDecode('{[foo:bar)}');
    }

    public function testJsonDecode() {
        $this->assertSame([
            'foo' => 'bar',
            'baz' => 1,
        ], CM_Util::jsonDecode('{"foo" : "bar", "baz" : 1}'));

        $this->assertSame([
            'foo' => 'bar',
            'baz' => 1,
        ], CM_Util::jsonDecode('{"foo" : "bar", "baz" : 1}', false));

        $this->assertEquals((object) [
            'foo' => 'bar',
            'baz' => 1,
        ], CM_Util::jsonDecode('{"foo" : "bar", "baz" : 1}', true));
    }

    public function testSanitizeUtf() {
        $string = pack("H*", 'c32e');
        $this->assertSame('?.', CM_Util::sanitizeUtf($string));
    }

    public function testSanitizeUtf2() {
        $string = pack('H*', 'e4bfa1e65faf');
        $this->assertSame('信?_?', CM_Util::sanitizeUtf($string));
    }

    public function testApplyOffset() {
        $this->assertSame(5, CM_Util::applyOffset(7, 5, 0));
        $this->assertSame(0, CM_Util::applyOffset(7, 0, 0));
        $this->assertSame(4, CM_Util::applyOffset(7, 2, 2));
        $this->assertSame(5, CM_Util::applyOffset(7, 6, -1));
        $this->assertSame(0, CM_Util::applyOffset(7, 1, 20));
        $this->assertSame(2, CM_Util::applyOffset(7, 5, -31));
        $exception = $this->catchException(function () {
            CM_Util::applyOffset(7, 7, 2);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Initial position is invalid', $exception->getMessage());
        $exception = $this->catchException(function () {
            CM_Util::applyOffset(5, -1, 3);
        });
        $this->assertInstanceOf('CM_Exception_Invalid', $exception);
        $this->assertSame('Initial position is invalid', $exception->getMessage());
    }

    public function testCreateDateTimeWithMillis() {
        $dateTime = CM_Util::createDateTimeWithMillis();
        $this->assertInstanceOf('DateTime', $dateTime);
    }
}
