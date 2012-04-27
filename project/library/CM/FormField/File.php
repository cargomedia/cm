<?php

class CM_FormField_File extends CM_FormField_Abstract {

	/**
	 * @param string $name
	 * @param int $cardinality
	 */
	public function __construct($name = 'file', $cardinality = 1) {
		parent::__construct($name);
		$this->_options['cardinality'] = (int) $cardinality;
		$this->_options['allowedExtensions'] = $this->_getAllowedExtensions();
	}

	/**
	 * @return array List of allowed extension (empty = all)
	 */
	protected function _getAllowedExtensions() {
		return array();
	}

	/**
	 * @param CM_File $file Uploaded file
	 * @throws CM_Exception If invalid file
	 */
	public function validateFile(CM_File $file) {
	}

	/**
	 * @param CM_File_UserContent_Temp $file
	 * @return string HTML
	 */
	public function getPreview(CM_File_UserContent_Temp $file, CM_Render $render) {
		$html = '';
		$html .= '<a href="javascript:;" class="icon delete hover"></a>';
		return $html;
	}

	public function validate($userInput, CM_Response_Abstract $response) {
		$userInput = array_filter($userInput, function ($value) {
			return !empty($value);
		});

		if ($this->_options['cardinality'] > 0 && sizeof($userInput) > $this->_options['cardinality']) {
			throw new CM_Exception_Invalid('Too many files uploaded');
		}

		$files = array();
		foreach ($userInput as $file) {
			$files[] = new CM_File_UserContent_Temp($file);
		}

		return (array) $files;
	}

	public function prepare(array $params) {
		$this->setTplParam('text', isset($params['text']) ? (string) $params['text'] : 'Upload File');
		$this->setTplParam('textDropArea', isset($params['textDropArea']) ? (string) $params['textDropArea'] : 'Drop files here to upload');
	}
}
