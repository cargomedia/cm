<?php

class CM_FormField_FileImage extends CM_FormField_File {

	public function __construct($name = 'file', $cardinality = 1) {
		parent::__construct($name, $cardinality);
	}

	protected function _getAllowedExtensions() {
		return array('jpg', 'jpeg', 'gif', 'png');
	}

	public function validateFile(CM_File $file) {
		parent::validateFile($file);

		try {
			new CM_File_Image($file);
		} catch (CM_Exception $e) {
			throw new CM_Exception_FormFieldValidation('Invalid image');
		}
	}

	public function getPreview(CM_File_UserContent_Temp $file, CM_Render $render) {
		$html = '';
		$html .= '<img src="' . $render->getUrlUserContent($file) . '" />';
		$html .= '<div class="actions"><a href="javascript:;" class="icon-delete deleteFile"></a></div>';
		return $html;
	}

}
