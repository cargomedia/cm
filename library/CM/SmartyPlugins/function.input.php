<?php

function smarty_function_input(array $params, Smarty_Internal_Template $template) {
    $params = CM_Params::factory($params);
    if (!$params->has('name')) {
        throw new CM_Exception_Invalid('Param `name` missing');
    }
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    /** @var CM_Form_Abstract $form */
    $form = $render->getFrontend()->getClosestViewResponse('CM_Form_Abstract')->getView();
    if (null === $form) {
        throw new CM_Exception_Invalid('Cannot find parent `CM_Form_Abstract` view response. {input} can be only rendered within form view.');
    }
    $fieldName = $params->getString('name');
    $field = $form->getField($fieldName);
    $renderAdapter = new CM_RenderAdapter_FormField($render, $field);
    return $renderAdapter->fetch($params);
}
