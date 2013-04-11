<?php

class CM_Response_Upload extends CM_Response_Abstract {

	/**
	 * Max file size allowed by the ser
	 *
	 * @var int 10MB
	 */
	const MAX_FILE_SIZE = 10485760;

	private static $_uploadErrors = array(
		UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
		UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
		UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
		UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
		UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
		UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
		UPLOAD_ERR_EXTENSION  => 'File upload stopped by extension.',
	);

	public function __construct(CM_Request_Post $request) {
		$request->setBodyEncoding(CM_Request_Post::ENCODING_NONE);
		parent::__construct($request);
	}

	protected function _process() {
		$return = array();

		try {
			$fileInfo = reset($_FILES);
			if (empty($fileInfo)) {
				throw new CM_Exception('Invalid file upload');
			}

			if (isset($fileInfo['error']) && $fileInfo['error'] !== UPLOAD_ERR_OK) {
				throw new CM_Exception('File upload error: ' . self::$_uploadErrors[$fileInfo['error']]);
			}

			$fileTmp = new CM_File($fileInfo['tmp_name']);
			if ($fileTmp->getSize() > self::MAX_FILE_SIZE) {
				throw new CM_Exception_FormFieldValidation('File too big');
			}

			$file = CM_File_UserContent_Temp::create($fileInfo['name']);
			$fileTmp->move($file->getPath());

			$query = $this->_request->getQuery();
			$preview = null;
			if (isset($query['field'])) {
				/** @var $field CM_FormField_File */
				$field = CM_FormField_File::factory($query['field']);
				$field->validateFile($file);
				$preview = $field->getPreview($file, $this->getRender());
			}
			$return['success'] = array('id' => $file->getUniqid(), 'preview' => $preview);
		} catch (CM_Exception_FormFieldValidation $ex) {
			$return['error'] = array('type' => get_class($ex), 'msg' => $ex->getMessagePublic($this->getRender()));
		}

		$this->_setContent(json_encode($return, JSON_HEX_TAG)); // JSON decoding in IE-iframe needs JSON_HEX_TAG
	}

	public static function match(CM_Request_Abstract $request) {
		return $request->getPathPart(0) === 'upload';
	}
}
