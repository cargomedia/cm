<?php

class CM_RenderAdapter_FormField extends CM_RenderAdapter_Abstract {

    public function fetch(CM_Params $renderParams, CM_Frontend_ViewResponse &$viewResponse = null) {
        $field = $this->_getFormField();
        $frontend = $this->getRender()->getGlobalResponse();

        $viewResponse = new CM_Frontend_ViewResponse($field);
        $viewResponse->setTemplateName('default');
        $field->prepare($renderParams, $this->getRender()->getEnvironment(), $viewResponse);
        $viewResponse->set('field', $field);
        $viewResponse->set('inputId', $viewResponse->getAutoIdTagged('input'));
        $viewResponse->set('name', $field->getName());
        $viewResponse->set('value', $field->getValue());
        $viewResponse->set('options', $field->getOptions());
        $viewResponse->getJs()->setProperty('fieldOptions', $field->getOptions());

        $frontend->treeExpand($viewResponse);

        $content = trim($this->getRender()->fetchViewResponse($viewResponse));
        if (!$field instanceof CM_FormField_Hidden) {
            $content .= '<div class="messages"></div>';
            $content .= '<div class="formFieldFeedback formFieldFeedback-success"><span class="icon icon-verified"></span></div>';
            $content .= '<div class="formFieldFeedback formFieldFeedback-error"><span class="icon icon-error"></span></div>';
        }
        $tagAttributes = [
            'id'    => $viewResponse->getAutoId(),
            'class' => join(' ', $viewResponse->getCssClasses()),
        ];
        $tagRenderer = new CM_Frontend_HtmlTagRenderer();
        $html = $tagRenderer->renderTag('div', $content, $tagAttributes, $viewResponse->getDataAttributes());

        $formViewResponse = $frontend->getClosestViewResponse('CM_Form_Abstract');
        if ($formViewResponse) {
            $formViewResponse->getJs()->append("this.registerField(cm.views['{$viewResponse->getAutoId()}']);");
        }

        $frontend->treeCollapse();
        return $html;
    }

    /**
     * @return CM_FormField_Abstract
     */
    private function _getFormField() {
        return $this->_getView();
    }
}
