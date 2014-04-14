<?php

function smarty_function_input(array $params, Smarty_Internal_Template $template) {
    if (!isset($params['name'])) {
        trigger_error('Param `name` missing.');
    }
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    /** @var CM_Form_Abstract $form */
    $form = $render->getStackLast('forms');
    $field = $form->getField($params['name']);
    $renderAdapter = new CM_RenderAdapter_FormField($render, $field);
    return $renderAdapter->fetch($params, $field, $form, $params['name']);
}
