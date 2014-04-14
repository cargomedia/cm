<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

    /**
     * @return string
     */
    public function fetch() {
        $renderEnvironment = $this->getRender()->getEnvironment();
        $this->_getComponent()->checkAccessible($renderEnvironment);

        $frontendHandler = new CM_ComponentFrontendHandler();
        $viewResponse = $this->_getPreparedViewResponse($renderEnvironment, $frontendHandler);

        $parentViewId = null;
        if (count($this->getRender()->getStack('views'))) {
            /** @var CM_View_Abstract $parentView */
            $parentView = $this->getRender()->getStackLast('views');
            $parentViewId = $parentView->getAutoId();
        }

        $this->getRender()->pushStack($this->_getStackKey(), $this->_getComponent());
        $this->getRender()->pushStack('views', $this->_getComponent());

        $cssClass = implode(' ', $this->_getComponent()->getClassHierarchy());
        if (preg_match('#([^/]+)\.tpl$#', $viewResponse->getTemplateName(), $match)) {
            if ($match[1] != 'default') {
                $cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
            }
        }
        $html = '<div id="' . $viewResponse->getAutoId() . '" class="' . $cssClass . '">';

        $viewResponse->addData('viewObj', $this->_getComponent());
        $html .=  $this->getRender()->fetchViewResponse($viewResponse);

        $html .= '</div>';

        $this->getRender()->getJs()->registerComponent($viewResponse, $frontendHandler, $parentViewId);
        $this->getRender()->popStack($this->_getStackKey());
        $this->getRender()->popStack('views');

        return $html;
    }

    /**
     * @return string
     */
    protected function _getStackKey() {
        return 'components';
    }

    /**
     * @param CM_RenderEnvironment        $environment
     * @param CM_ComponentFrontendHandler $frontendHandler
     * @return CM_ViewResponse
     */
    protected function _getPreparedViewResponse(CM_RenderEnvironment $environment, CM_ComponentFrontendHandler $frontendHandler) {
        /** @var CM_Component_Abstract $component */
        $viewResponse = new CM_ViewResponse($this->_getComponent());
        $viewResponse->setTemplateName('default');
        $component->prepare($environment, $viewResponse, $frontendHandler);
        return $viewResponse;
    }

    /**
     * @return CM_Component_Abstract
     */
    private function _getComponent() {
        return $this->_getView();
    }
}
