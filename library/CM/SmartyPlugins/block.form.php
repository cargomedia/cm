<?php

function smarty_block_form($params, $content, Smarty_Internal_Template $template, $open) {
    /** @var CM_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $frontend = $render->getFrontend();
    if ($open) {
        $form = CM_Form_Abstract::factory($params['name']);
        $form->setup();
        $form->renderStart($params);

        $viewResponse = new CM_ViewResponse($form);
        $frontend->treeExpand($viewResponse);
        return '';
    } else {
        $viewResponse = $frontend->getTreeCurrent()->getValue();
        /** @var CM_Form_Abstract $form */
        $form = $viewResponse->getView();

        $class = implode(' ', $form->getClassHierarchy());
        $html = '<form id="' . $form->getAutoId() . '" class="' . $class . ' clearfix" method="post" onsubmit="return false;" novalidate >';
        $html .= $content;


        foreach ($form->getFields() as $fieldName => $field) {
            if ($field instanceof CM_FormField_Hidden) {
                $renderAdapter = new CM_RenderAdapter_FormField($render, $field);
                $html .= $renderAdapter->fetch(CM_Params::factory());
            }
        }
        $frontendHandler = new CM_ViewFrontendHandler();
        foreach ($form->getActions() as $actionName => $action) {
            $frontendHandler->append("this.registerAction('{$actionName}', {$action->js_presentation()})");
        }
        $render->getFrontend()->registerViewResponse($viewResponse, $frontendHandler);
        $html .= '</form>';

        $frontend->treeCollapse();
        return $html;
    }
}
