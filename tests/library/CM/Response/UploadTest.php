<?php

class CM_Response_UploadTest extends CMTest_TestCase {

	public function testUpload() {
		$filename = 'test.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		$fileTmp = CM_File::createTmp(null, $content);
		$_FILES = array('file' => array('name' => $filename, 'tmp_name' => $fileTmp->getPath()));

		$request = new CM_Request_Post('/upload/null');
		$upload = new CM_Response_Upload($request);
		$upload->process();
		$data = json_decode($upload->getContent(), true);

		$this->assertNotEmpty($data['success']);
		$this->assertEquals(32, strlen($data['success']['id']));

		$file = new CM_File_UserContent_Temp($data['success']['id']);
		$this->assertEquals($content, $file->read());
		$this->assertFalse($fileTmp->getExists());
	}

	public function testUploadImageField() {
		$filename = 'test.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		$fileTmp = CM_File::createTmp(null, $content);
		$_FILES = array('file' => array('name' => $filename, 'tmp_name' => $fileTmp->getPath()));

		$request = new CM_Request_Post('/upload/null?field=CM_FormField_FileImage');
		$upload = new CM_Response_Upload($request);
		$upload->process();
		$data = json_decode($upload->getContent(), true);

		$this->assertNotEmpty($data['success']);
		$this->assertEquals(32, strlen($data['success']['id']));
		$this->assertContains($data['success']['id'], $data['success']['preview']);
	}

	public function testUploadImageFieldNoImage() {
		$filename = 'test.jpg.zip';
		$content = file_get_contents(DIR_TEST_DATA . $filename);

		$fileTmp = CM_File::createTmp(null, $content);
		$_FILES = array('file' => array('name' => $filename, 'tmp_name' => $fileTmp->getPath()));

		$request = new CM_Request_Post('/upload/null?field=CM_FormField_FileImage');
		$upload = new CM_Response_Upload($request);
		$upload->process();
		$data = json_decode($upload->getContent(), true);

		$this->assertEquals('CM_Exception_FormFieldValidation', $data['error']['type']);
	}

	public function testUploadFileFieldNoImage() {
		$filename = 'test.jpg.zip';
		$content = file_get_contents(DIR_TEST_DATA . $filename);

		$fileTmp = CM_File::createTmp(null, $content);
		$_FILES = array('file' => array('name' => $filename, 'tmp_name' => $fileTmp->getPath()));

		$request = new CM_Request_Post('/upload/null?field=CM_FormField_File');
		$upload = new CM_Response_Upload($request);
		$upload->process();
		$data = json_decode($upload->getContent(), true);

		$this->assertNotEmpty($data['success']);
	}

	public function testUploadImageFieldCorruptImage() {
		$filename = 'corrupt-header.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		$fileTmp = CM_File::createTmp(null, $content);
		$_FILES = array('file' => array('name' => $filename, 'tmp_name' => $fileTmp->getPath()));

		$request = new CM_Request_Post('/upload/null?field=CM_FormField_FileImage');
		$upload = new CM_Response_Upload($request);
		$upload->process();
		$data = json_decode($upload->getContent(), true);

		$this->assertEquals('CM_Exception_FormFieldValidation', $data['error']['type']);
	}

	public function testUploadFileFieldCorruptImage() {
		$filename = 'corrupt-header.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		$fileTmp = CM_File::createTmp(null, $content);
		$_FILES = array('file' => array('name' => $filename, 'tmp_name' => $fileTmp->getPath()));

		$request = new CM_Request_Post('/upload/null?field=CM_FormField_File');
		$upload = new CM_Response_Upload($request);
		$upload->process();
		$data = json_decode($upload->getContent(), true);

		$this->assertNotEmpty($data['success']);
	}

	public function testUploadInvalidField() {
		$filename = 'test.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		$fileTmp = CM_File::createTmp(null, $content);
		$_FILES = array('file' => array('name' => $filename, 'tmp_name' => $fileTmp->getPath()));

		$request = new CM_Request_Post('/upload/null?field=nonexistent');
		$upload = new CM_Response_Upload($request);

		try {
			$upload->process();
			$this->fail('Should throw invalid exception');
		} catch (CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}
}
