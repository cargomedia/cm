<?php

class CM_Form_ExampleIcon extends CM_Form_Abstract {

    protected function _initialize() {
        $this->registerField(new CM_FormField_Integer(['name' => 'sizeSlider', 'min' => 6, 'max' => 120]));
        $this->registerField(new CM_FormField_Color(['name' => 'colorBackground']));
        $this->registerField(new CM_FormField_Color(['name' => 'color']));
        $this->registerField(new CM_FormField_Color(['name' => 'shadowColor']));
        $this->registerField(new CM_FormField_Integer(['name' => 'shadowX', 'min' => 0, 'max' => 20]));
        $this->registerField(new CM_FormField_Integer(['name' => 'shadowY', 'min' => 0, 'max' => 20]));
        $this->registerField(new CM_FormField_Integer(['name' => 'shadowBlur', 'min' => 0, 'max' => 20]));
    }

    public function prepare(CM_Params $renderParams) {
        $this->getField('sizeSlider')->setValue(18);
        $this->getField('shadowColor')->setValue('#333');
        $this->getField('colorBackground')->setValue('#fafafa');
    }
}
