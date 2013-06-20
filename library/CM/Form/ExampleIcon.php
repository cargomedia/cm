<?php

class CM_Form_ExampleIcon extends CM_Form_Abstract {

	public function setup() {

		$this->registerField(new CM_FormField_Integer('sizeSlider', 6, 120, 0));
		$this->registerField(new CM_FormField_Color('colorBackground'));
		$this->registerField(new CM_FormField_Color('color'));
		$this->registerField(new CM_FormField_Color('shadowColor'));
		$this->registerField(new CM_FormField_Integer('shadowX', 0, 20, 0));
		$this->registerField(new CM_FormField_Integer('shadowY', 0, 20, 0));
		$this->registerField(new CM_FormField_Integer('shadowBlur', 0, 20, 0));
	}

	public function _renderStart(CM_Params $params) {
		$this->getField('sizeSlider')->setValue(18);
		$this->getField('shadowColor')->setValue('#333');
		$this->getField('colorBackground')->setValue('#fafafa');
	}
}
