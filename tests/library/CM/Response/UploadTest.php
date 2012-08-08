<?php
require_once __DIR__ . '/../../../TestCase.php';

class CM_Response_UploadTest extends TestCase {

	public static function setUpBeforeClass() {
	}

	public static function tearDownAfterClass() {
		TH::clearEnv();
	}

	public function testUpload() {
		$filename = 'test.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		$request = new CM_Request_Post('/upload/test?qqfile=' . $filename, array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);
		$data = json_decode($upload->process());

		$this->assertNotEmpty($data->success);

		$tmpFile = new CM_File_UserContent_Temp($data->success->id);

		$this->assertEquals($content, $tmpFile->read());
	}

	public function testUploadStream() {
		$filename = 'test.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		// No field test
		$request = new CM_Request_Post('/upload/test?qqfile=' . $filename . '&field=CM_FormField_FileImage', array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);
		$data = json_decode($upload->process());

		$this->assertNotEmpty($data->success);
		$this->assertEquals(32, strlen($data->success->id));
		$this->assertGreaterThan(0, strpos($data->success->preview, $data->success->id));
	}

	public function testUploadImageField() {
		$filename = 'test.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		// Has to be overwritten, is normally set by a request
		$_SERVER['CONTENT_LENGTH'] = strlen($content);

		// No field test
		$request = new CM_Request_Post('/upload/test?field=CM_FormField_FileImage&qqfile=' . $filename, array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);
		$data = json_decode($upload->process());

		$this->assertNotEmpty($data->success);
		$this->assertEquals(32, strlen($data->success->id));
		$this->assertGreaterThan(0, strpos($data->success->preview, $data->success->id));
	}

	public function testUploadImageFieldNoImage() {
		$filename = 'test.jpg.zip';
		$content = file_get_contents(DIR_TEST_DATA . $filename);

		// No field test
		$request = new CM_Request_Post('/upload/test?field=CM_FormField_FileImage&qqfile=' . $filename, array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);
		$data = json_decode($upload->process());

		$this->assertEquals('CM_Exception_FormFieldValidation', $data->error->type);
	}

	public function testUploadFileFieldNoImage() {
		$filename = 'test.jpg.zip';
		$content = file_get_contents(DIR_TEST_DATA . $filename);

		// No field test
		$request = new CM_Request_Post('/upload/test?field=CM_FormField_File&qqfile=' . $filename, array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);
		$data = json_decode($upload->process());

		$this->assertNotEmpty($data->success);
	}

	public function testUploadImageFieldCorruptImage() {
		$filename = 'corrupt-header.jpg';

		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		// No field test
		$request = new CM_Request_Post('/upload/test?field=CM_FormField_FileImage&qqfile=' . $filename, array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);
		$data = json_decode($upload->process());

		$this->assertEquals('CM_Exception_FormFieldValidation', $data->error->type);
	}

	public function testUploadFileFieldCorruptImage() {
		$filename = 'corrupt-header.jpg';

		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		// Has to be overwritten, is normally set by a request
		$_SERVER['CONTENT_LENGTH'] = strlen($content);

		// No field test
		$request = new CM_Request_Post('/upload/test?field=CM_FormField_File&qqfile=' . $filename, array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);
		$data = json_decode($upload->process());

		$this->assertNotEmpty($data->success);
	}

	public function testUploadInvalidField() {
		$filename = 'test.jpg';
		$content = file_get_contents(DIR_TEST_DATA . 'img/' . $filename);

		$field = 'doesnotexist';

		// No field test
		$request = new CM_Request_Post('/upload/test?field=' . $field . '&qqfile=' . $filename, array('Content-Length' => strlen($content)), $content);
		$request->setBodyEncoding(false);
		$upload = new CM_Response_Upload($request);

		try {
			$upload->process();
			$this->fail('Should throw invalid exception');
		} catch(CM_Exception_Invalid $e) {
			$this->assertTrue(true);
		}
	}
}
