<?php

class CM_File_CsvTest extends CMTest_TestCase {

    public function testParse() {
        $path = DIR_TEST_DATA . 'data.csv';

        $file = new CM_File_Csv($path);
        $fileData = $file->parse(null, null, null, null, 2);

        $this->assertSame(
            [
                ['Data31', 'Data32', 'Data33', '1'],
                ['Data41', 'Data42', 'Data43', '2'],
                ['Data51', 'Data52', 'Data53', '3'],
            ],
            $fileData
        );

        $exception = $this->catchException(function () use ($file) {
            $file->parse('');
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Empty linebreak', $exception->getMessage());

        $exception = $this->catchException(function () use ($file) {
            $file->parse(null, '');
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Empty delimeter', $exception->getMessage());

        $mockEmptyFile = $this->mockClass(CM_File_Csv::class)->newInstanceWithoutConstructor();
        $mockEmptyFile->mockMethod('read')->set('');

        $exception = $this->catchException(function () use ($mockEmptyFile) {
            $mockEmptyFile->parse();
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Empty or bad CSV content', $exception->getMessage());
    }
}
