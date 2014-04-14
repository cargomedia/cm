<?php

class CM_Form_ExampleIcon extends CM_Form_Abstract {

    public function setup() {
        $this->registerField('sizeSlider', new CM_FormField_Integer(['min' => 6, 'max' => 120, 'step' => 0]));
        $this->registerField('colorBackground', new CM_FormField_Color());
        $this->registerField('color', new CM_FormField_Color());
        $this->registerField('shadowColor', new CM_FormField_Color());
        $this->registerField('shadowX', new CM_FormField_Integer(['min' => 0, 'max' => 20, 'step' => 0]));
        $this->registerField('shadowY', new CM_FormField_Integer(['min' => 0, 'max' => 20, 'step' => 0]));
        $this->registerField('shadowBlur', new CM_FormField_Integer(['min' => 0, 'max' => 20, 'step' => 0]));
    }

    public function _renderStart(CM_Params $params) {
        $this->getField('sizeSlider')->setValue(18);
        $this->getField('shadowColor')->setValue('#333');
        $this->getField('colorBackground')->setValue('#fafafa');
    }
}
