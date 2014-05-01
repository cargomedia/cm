<?php

class CM_Form_ExampleIcon extends CM_Form_Abstract {

    public function setup() {
        $this->registerField('sizeSlider', new CM_FormField_Integer(['name' => 'sizeSlider', 'min' => 6, 'max' => 120]));
        $this->registerField('colorBackground', new CM_FormField_Color(['name' => 'colorBackground']));
        $this->registerField('color', new CM_FormField_Color(['name' => 'color']));
        $this->registerField('shadowColor', new CM_FormField_Color(['name' => 'shadowColor']));
        $this->registerField('shadowX', new CM_FormField_Integer(['name' => 'shadowX', 'min' => 0, 'max' => 20]));
        $this->registerField('shadowY', new CM_FormField_Integer(['name' => 'shadowY', 'min' => 0, 'max' => 20]));
        $this->registerField('shadowBlur', new CM_FormField_Integer(['name' => 'shadowBlur', 'min' => 0, 'max' => 20]));
    }

    public function _renderStart(CM_Params $params) {
        $this->getField('sizeSlider')->setValue(18);
        $this->getField('shadowColor')->setValue('#333');
        $this->getField('colorBackground')->setValue('#fafafa');
    }
}
