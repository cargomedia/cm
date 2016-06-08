<?php

class CM_Form_Example_Todo extends CM_Form_Abstract {

    protected function _initialize() {
        $this->registerField(new CM_FormField_Hidden(['name' => 'todoId']));
        $this->registerField(new CM_FormField_Text(['name' => 'title']));
        $this->registerField(new CM_FormField_Text(['name' => 'description']));
        $this->registerField(new CM_FormField_Set_Select([
            'name'           => 'state',
            'labelsInValues' => true,
            'values'         => [0 => 'pending', 1 => 'in progress', 2 => 'cancelled', 3 => 'done'],
        ]));
        $this->registerAction(new CM_FormAction_Example_Todo_Save($this));
    }
}
