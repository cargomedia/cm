<?php

abstract class CM_Page_Abstract extends CM_Component_Abstract {

    public function checkAccessible(CM_Frontend_Environment $environment) {
    }

    /**
     * @return bool
     */
    public function getCanTrackPageView() {
        return true;
    }

    /**
     * @return string|null
     */
    public function getPathVirtualPageView() {
        return null;
    }

    /**
     * Checks if the page is viewable by the current user
     *
     * @return bool True if page is visible
     */
    public function isViewable() {
        return true;
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param CM_Http_Response_Page   $response
     */
    public function prepareResponse(CM_Frontend_Environment $environment, CM_Http_Response_Page $response) {
    }

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
    }

    /**
     * @param CM_Frontend_Render $render
     * @param string             $path
     * @throws CM_Exception_Invalid
     * @return string
     */
    public static final function getClassnameByPath(CM_Frontend_Render $render, $path) {
        $path = (string) $path;

        $pathTokens = explode('/', $path);
        array_shift($pathTokens);

        // Rewrites code-of-honor to CodeOfHonor
        foreach ($pathTokens as &$pathToken) {
            $pathToken = CM_Util::camelize($pathToken);
        }

        return $render->getClassnameByPartialClassname('Page_' . implode('_', $pathTokens));
    }

    /**
     * @param array|null $params
     * @return string
     */
    public static function getPath(array $params = null) {
        $pageClassName = get_called_class();
        $list = explode('_', $pageClassName);

        // Remove first parts
        foreach ($list as $index => $entry) {
            unset($list[$index]);
            if ($entry == 'Page') {
                break;
            }
        }

        // Converts upper case letters to dashes: CodeOfHonor => code-of-honor
        foreach ($list as $index => $entry) {
            $list[$index] = CM_Util::uncamelize($entry);
        }

        $path = '/' . implode('/', $list);
        if ($path == '/index') {
            $path = '/';
        }
        return CM_Util::link($path, $params);
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getLayout(CM_Frontend_Environment $environment) {
        return $this->_getLayoutClassByName($environment, 'Default');
    }

    /**
     * @param CM_Frontend_Environment $environment
     * @param string                  $layoutName
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected function _getLayoutClassByName(CM_Frontend_Environment $environment, $layoutName) {
        $layoutName = (string) $layoutName;

        foreach ($environment->getSite()->getModules() as $moduleNamespace) {
            $classname = $moduleNamespace . '_Layout_' . $layoutName;
            if (class_exists($classname)) {
                return $classname;
            }
        }

        throw new CM_Exception_Invalid('Layout is not defined in any namespace', null, ['layoutName' => $layoutName]);
    }

}
