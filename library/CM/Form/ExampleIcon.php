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

    protected function _getRequiredFields() {
        return [];
    }

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        parent::prepare($environment, $viewResponse);

        $this->getField('sizeSlider')->setValue(18);
        $this->getField('shadowColor')->setValue(CM_Color_RGB::fromHexString('333333'));
        $this->getField('colorBackground')->setValue(CM_Color_RGB::fromHexString('fafafa'));
    }
}
