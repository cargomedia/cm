<?php

class CM_Response_Upload extends CM_Response_Abstract {

	/**
	 * Max file size allowed by the ser
	 * 
	 * @var int 10MB
	 */
	const MAX_FILE_SIZE = 10485760;

	public function process() {
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

				$tmpFile = CM_File_Temp::create(array('name' => $query['qqfile'], 'size' => $realSize));
				$tmpFile->writeContent($content);

			} elseif (!empty($_FILES['qqfile'])) {
				$file = $_FILES['qqfile'];

				$tmpFile = CM_File_Temp::create($file);

				if (!move_uploaded_file($file['tmp_name'], $tmpFile->getPath())) {
					throw new CM_Exception('Could not move upload file `' . $file['tmp_name'] . '`');
				}

			} else {
				throw new CM_Exception('Invalid file upload');
			}
			
			if ($tmpFile->getSize() > self::MAX_FILE_SIZE) {
				throw new CM_Exception_FormFieldValidation('File too big', true);
			}

			$preview = null;
			
			if (isset($query['field'])) {
				// Validate file with field
				$field = CM_FormField_Abstract::factory($query['field']);
				$field->validateFile($tmpFile);
				$preview = $field->getPreview($tmpFile);
			}
						
			$return['success'] = array('id' => $tmpFile->getUniqid(), 'url' => $tmpFile->getURL(), 'preview' => $preview);

		} catch (CM_Exception_FormFieldValidation $ex) {
			$return['error'] = array('type' => get_class($ex), 'msg' => $ex->getErrorKey());
		}
		
		return json_encode($return, JSON_HEX_TAG);	// JSON decoding in IE-iframe needs JSON_HEX_TAG
	}
}
