<?php

class CM_File_UserContentTest extends CMTest_TestCase {

    public function testGetPathRelative() {
        $file = new CM_File_UserContent('foo', 'my-file.txt', null);
        $this->assertSame('foo/my-file.txt', $file->getPathRelative());

        $file = new CM_File_UserContent('foo', 'my-file.txt', 1);
        $this->assertSame('foo/1/my-file.txt', $file->getPathRelative());

        $file = new CM_File_UserContent('foo', 'my-file.txt', CM_File_UserContent::BUCKETS_COUNT + 2);
        $this->assertSame('foo/2/my-file.txt', $file->getPathRelative());
    }
}
