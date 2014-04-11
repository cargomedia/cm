<?php

class CM_RenderAdapter_Component extends CM_RenderAdapter_Abstract {

    /**
     * @param CM_Component_Abstract $component
     * @return string
     */
    public function fetch(CM_Component_Abstract $component) {
        $renderEnvironment = $this->getRender()->getEnvironment();
        $component->checkAccessible($renderEnvironment);

        $frontendHandler = new CM_ComponentFrontendHandler();
        $viewResponse = $this->_getPreparedViewResponse($component, $renderEnvironment, $frontendHandler);

        $parentViewId = null;
        if (count($this->getRender()->getStack('views'))) {
            /** @var CM_View_Abstract $parentView */
            $parentView = $this->getRender()->getStackLast('views');
            $parentViewId = $parentView->getAutoId();
        }

        $this->getRender()->pushStack($this->_getStackKey(), $component);
        $this->getRender()->pushStack('views', $component);

        $cssClass = implode(' ', $component->getClassHierarchy());
        if (preg_match('#([^/]+)\.tpl$#', $viewResponse->getTemplateName(), $match)) {
            if ($match[1] != 'default') {
                $cssClass .= ' ' . $match[1]; // Include special-tpl name in class (e.g. 'mini')
            }
        }
        $html = '<div id="' . $viewResponse->getAutoId() . '" class="' . $cssClass . '">';

        $viewResponse->addData('viewOjb', $component);
        $html .=  $this->getRender()->renderViewResponse($viewResponse);

        $html .= '</div>';

        $this->getRender()->getJs()->registerComponent($viewResponse, $frontendHandler, $parentViewId);
        $this->getRender()->popStack($this->_getStackKey());
        $this->getRender()->popStack('views');

        return $html;
    }

    /**
     * @param string $tplName
     * @param array  $params
     * @return string
     */
    public function fetchTemplate($tplName, array $params) {
        return $this->_renderTemplate($tplName, $params, true);
    }

    /**
     * @return string
     */
    protected function _getStackKey() {
        return 'components';
    }

    /**
     * @param CM_Component_Abstract       $component
     * @param CM_RenderEnvironment        $environment
     * @param CM_ComponentFrontendHandler $frontendHandler
     * @return CM_ViewResponse
     */
    protected function _getPreparedViewResponse(CM_Component_Abstract $component, CM_RenderEnvironment $environment, CM_ComponentFrontendHandler $frontendHandler) {
        /** @var CM_Component_Abstract $component */
        $viewResponse = new CM_ViewResponse($component);
        $viewResponse->setTemplateName('default');
        $component->prepare($environment, $viewResponse, $frontendHandler);
        return $viewResponse;
    }
}
