<?php

class CM_Form_ExampleIcon extends CM_Form_Abstract {

	public function setup() {

		$this->registerField('sizeSlider', new CM_FormField_Integer(6, 120, 0));
		$this->registerField('colorBackground', new CM_FormField_Color());
		$this->registerField('color', new CM_FormField_Color());
		$this->registerField('shadowColor', new CM_FormField_Color());
		$this->registerField('shadowX', new CM_FormField_Integer(0, 20, 0));
		$this->registerField('shadowY', new CM_FormField_Integer(0, 20, 0));
		$this->registerField('shadowBlur', new CM_FormField_Integer(0, 20, 0));
	}

	public function _renderStart(CM_Params $params) {
		$this->getField('sizeSlider')->setValue(18);
		$this->getField('shadowColor')->setValue('#333');
		$this->getField('colorBackground')->setValue('#fafafa');
	}
}
