<?php

function smarty_function_formField(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $cssClasses = array();
    if (isset($params['class'])) {
        $cssClasses[] = (string) $params['class'];
        unset($params['class']);
    }

    $label = null;
    if (isset($params['label'])) {
        $label = (string) $params['label'];
    }

    $input = null;
    $fieldName = null;
    if (isset($params['prepend'])) {
        $input .= (string) $params['prepend'];
    }
    /** @var CM_Frontend_ViewResponse|null $viewResponse */
    $viewResponse = null;
    if (isset($params['name'])) {
        $fieldName = (string) $params['name'];
        $cssClasses[] = $fieldName;
        /** @var CM_Form_Abstract $form */
        $form = $render->getGlobalResponse()->getClosestViewResponse('CM_Form_Abstract')->getView();
        if (null === $form) {
            throw new CM_Exception_Invalid('Cannot find parent `CM_Form_Abstract` view response. Named {formField} can be only rendered within form view.');
        }
        $formField = $form->getField($fieldName);
        $renderAdapter = new CM_RenderAdapter_FormField($render, $formField);
        $input .= $renderAdapter->fetch(CM_Params::factory($params, false), $viewResponse);

        if ($form->isRequiredField($fieldName)) {
            $cssClasses[] = 'required';
        }
        if (null !== $formField->getValue()) {
            $cssClasses[] = 'prefilled';
        }
    }
    if (isset($params['append'])) {
        $input .= (string) $params['append'];
    }
    $html = '<div class="formField clearfix ' . join(' ', $cssClasses) . '">';
    if ($label) {
        $html .= '<label';
        if ($viewResponse) {
            $html .= ' for="' . $viewResponse->getAutoIdTagged('input') . '"';
        }
        $html .= '>' . $label . '</label>';
    }
    if ($input) {
        $html .= '<div class="input">';
        $html .= $input;
        $html .= '</div>';
    }
    $html .= '</div>';

    return $html;
}
