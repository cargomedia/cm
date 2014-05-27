<?php

function smarty_block_form($params, $content, Smarty_Internal_Template $template, $open) {
    /** @var CM_Frontend_Render $render */
    $render = $template->smarty->getTemplateVars('render');
    $frontend = $render->getFrontend();
    if ($open) {
        $form = CM_Form_Abstract::factory($params['name']);
        $form->setup();
        $form->renderStart($params);

        $viewResponse = new CM_Frontend_ViewResponse($form);
        $frontend->treeExpand($viewResponse);
        return '';
    } else {
        $viewResponse = $frontend->getClosestViewResponse('CM_Form_Abstract');
        if (null === $viewResponse) {
            throw new CM_Exception_Invalid('Cannot find `CM_Form_Abstract` within frontend tree.');
        }
        /** @var CM_Form_Abstract $form */
        $form = $viewResponse->getView();

        $classes = $form->getClassHierarchy();
        $classes[] = $form->getName();
        $html = '<form id="' . $viewResponse->getAutoId() . '" class="' .
            implode(' ', $classes) . ' clearfix" method="post" onsubmit="return false;" novalidate >';
        $html .= $content;

        foreach ($form->getFields() as $field) {
            if ($field instanceof CM_FormField_Hidden) {
                $renderAdapter = new CM_RenderAdapter_FormField($render, $field);
                $html .= $renderAdapter->fetch(CM_Params::factory());
            }
        }
        foreach ($form->getActions() as $actionName => $action) {
            $viewResponse->getJs()->append("this.registerAction('{$actionName}', {$action->js_presentation()});");
        }
        $html .= '</form>';

        $frontend->treeCollapse();
        return $html;
    }
}
