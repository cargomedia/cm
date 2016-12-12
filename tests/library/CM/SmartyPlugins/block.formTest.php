<?php

require_once CM_Util::getModulePath('CM') . 'library/CM/SmartyPlugins/block.form.php';

class smarty_block_formTest extends CMTest_TestCase {

    public function testAutoSaveValid() {
        $render = new CM_Frontend_Render();
        $output = $render->parseTemplateContent('{form name="CM_Form_Example" autosave="Submit"}{/form}');
        $this->assertContains('data-autosave="true"', $output);
    }

}
