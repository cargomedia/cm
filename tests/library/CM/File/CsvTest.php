<?php

class CM_File_CsvTest extends CMTest_TestCase {

    public function testParse() {
        $path = DIR_TEST_DATA . 'data.csv';

        $file = new CM_File_Csv($path);
        $fileData = $file->parse(2, null, null, null, null);

        $this->assertSame(
            [
                ['Data31', 'Data32', 'Data33', '1'],
                ['Data41', 'Data42', 'Data43', '2'],
                ['Data51', 'Data52', 'Data53', '3'],
            ],
            $fileData
        );

        $exception = $this->catchException(function () use ($file) {
            $file->parse(1, '');
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Empty linebreak', $exception->getMessage());

        $exception = $this->catchException(function () use ($file) {
            $file->parse(0, null, '');
        });
        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame('Empty delimiter', $exception->getMessage());

        /** @var CM_File_Csv|\Mocka\AbstractClassTrait $mockEmptyFile */
        $mockEmptyFile = $this->mockClass(CM_File_Csv::class)->newInstanceWithoutConstructor();
        $mockEmptyFile->mockMethod('read')->set('');

        $this->assertInstanceOf(CM_Exception_Invalid::class, $exception);
        $this->assertSame([],  $mockEmptyFile->parse(0));
    }
}
