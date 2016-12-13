<?php

class CM_Form_ExampleAutosave extends CM_Form_Abstract {

    protected function _initialize() {
        $this->registerField(new CM_FormField_Text(['name' => 'text']));
        $this->registerField(new CM_FormField_Boolean(['name' => 'booleanSwitch']));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect1', 'values' => [1 => 'Eins', 2 => 'Zwei'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect2', 'values' => [1 => 'Eins', 2 => 'Zwei'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_Set_Select(['name' => 'setSelect3', 'values' => [1 => 'Foo', 2 => 'Bar'], 'labelsInValues' => true]));
        $this->registerField(new CM_FormField_TreeSelect(['name' => 'treeselect', 'tree' => CM_Model_LanguageKey::getTree()]));

        $this->registerAction(new CM_FormAction_Example_Submit($this));
    }

}
