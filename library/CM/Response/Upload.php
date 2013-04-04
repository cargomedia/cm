<?php

class CM_Response_Upload extends CM_Response_Abstract {

	/**
	 * Max file size allowed by the ser
	 *
	 * @var int 10MB
	 */
	const MAX_FILE_SIZE = 10485760;

	private static $_uploadErrors = array(
		UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
		UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
		UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
		UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
		UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
		UPLOAD_ERR_EXTENSION => 'File upload stopped by extension.',
	);

	public function __construct(CM_Request_Post $request) {
		$request->setBodyEncoding(CM_Request_Post::ENCODING_NONE);
		parent::__construct($request);
	}

	protected function _process() {
		$query = $this->_request->getQuery();

		$return = array();
		try {
			// Loads file
			if (!empty($query['qqfile'])) {
				$content = $this->_request->getBody();
				$realSize = strlen($content);

				if ($realSize != $this->_request->getHeader('Content-Length')) {
					throw new CM_Exception('Different file content length in request and headers');
				}

				$tmpFile = CM_File_UserContent_Temp::create($query['qqfile'], $content);
			} elseif (!empty($_FILES['qqfile'])) {
				$file = $_FILES['qqfile'];

				if (isset($file['error']) && $file['error'] !== UPLOAD_ERR_OK) {
					throw new CM_Exception('File upload error: ' . self::$_uploadErrors[$file['error']]);
				}

				$tmpFile = CM_File_UserContent_Temp::create($file['name']);
				if (!move_uploaded_file($file['tmp_name'], $tmpFile->getPath())) {
					throw new CM_Exception('Could not move upload file `' . $file['tmp_name'] . '`');
				}
			} else {
				throw new CM_Exception('Invalid file upload');
			}

			if ($tmpFile->getSize() > self::MAX_FILE_SIZE) {
				throw new CM_Exception_FormFieldValidation('File too big');
			}

			$preview = null;

			if (isset($query['field'])) {
				// Validate file with field
				$field = CM_FormField_Abstract::factory($query['field']);
				$field->validateFile($tmpFile);
				$preview = $field->getPreview($tmpFile, $this->getRender());
			}

			$return['success'] = array('id' => $tmpFile->getUniqid(), 'preview' => $preview);

		} catch (CM_Exception_FormFieldValidation $ex) {
			$return['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic($this->getRender()));
		}

		$this->_setContent(json_encode($return, JSON_HEX_TAG));	// JSON decoding in IE-iframe needs JSON_HEX_TAG
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'upload';
	}
}
