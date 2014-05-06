<?php

function smarty_function_formField(array $params, Smarty_Internal_Template $template) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');

    $class = null;
    if (isset($params['class'])) {
        $class = (string) $params['class'];
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
    /** @var CM_ViewResponse|null $viewResponse */
    $viewResponse = null;
    if (isset($params['name'])) {
        $fieldName = (string) $params['name'];
        /** @var CM_Form_Abstract $form */
        $form = $render->getFrontend()->getClosestViewResponse('CM_Form_Abstract')->getView();
        $formField = $form->getField($fieldName);
        $renderAdapter = new CM_RenderAdapter_FormField($render, $formField);
        $input .= $renderAdapter->fetch(CM_Params::factory($params), $viewResponse);
    }
    if (isset($params['append'])) {
        $input .= (string) $params['append'];
    }

    $html = '<div class="formField clearfix ' . $fieldName . ' ' . $class . '">';
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
