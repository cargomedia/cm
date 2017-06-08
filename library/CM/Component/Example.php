<?php

class CM_Component_Example extends CM_Component_Abstract {

    public function prepare(CM_Frontend_Environment $environment, CM_Frontend_ViewResponse $viewResponse) {
        $foo = $this->_params->getString('foo', 'value1');
        $colorStyles = $this->_getColorStyles();
        $icons = $this->_getIcons();

        $viewResponse->setData(array(
            'now'         => time(),
            'foo'         => $foo,
            'colorStyles' => $colorStyles,
            'icons'       => $icons,
        ));

        $viewResponse->getJs()->setProperty('uname', 'uname');
    }

    public function checkAccessible(CM_Frontend_Environment $environment) {
        if (!$environment->isDebug()) {
            throw new CM_Exception_NotAllowed();
        }
    }

    public function ajax_test(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Http_Response_View_Ajax $response) {
        $x = $params->getString('x');
        return 'x=' . $x;
    }

    public function ajax_error(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Http_Response_View_Ajax $response) {
        $status = $params->getInt('status', 200);
        $message = $params->has('text') ? $params->getString('text') : null;
        $messagePublic = $params->getBoolean('public', false) ? $message : null;
        if (in_array($status, array(500, 599), true)) {
            $response->addHeaderRaw('HTTP/1.1 ' . $status . ' Internal Server Error');
            $response->sendHeaders();
            exit($message);
        }
        $exception = $params->getString('exception');
        if (!in_array($exception, array('CM_Exception', 'CM_Exception_AuthRequired'), true)) {
            $exception = 'CM_Exception';
        }
        $exceptionOptions = [];
        if (null !== $messagePublic) {
            $exceptionOptions['messagePublic'] = new CM_I18n_Phrase($messagePublic);
        }
        throw new $exception($message, null, null, $exceptionOptions);
    }

    public function ajax_ping(CM_Params $params, CM_Frontend_JavascriptContainer_View $handler, CM_Http_Response_View_Ajax $response) {
        $number = $params->getInt('number');
        self::stream($response->getViewer(true), 'ping', array("number" => $number, "message" => 'pong'));
    }

    public static function rpc_time() {
        return time();
    }

    /**
     * @return string[]
     */
    private function _getIcons() {
        $site = $this->getParams()->getSite('site');

        $filesNames = [];
        foreach (array_reverse($site->getModules()) as $moduleName) {
            $path = CM_Util::getModulePath($moduleName) . 'layout/default/resource/img/icon';

            if (file_exists($path)) {
                $filesNames = array_merge($filesNames, scandir($path));
            }
        }
        $filesNames = preg_grep('/^([^.].*\.svg$)/', $filesNames);
        return str_replace('.svg', '', $filesNames);
    }

    /**
     * @return array
     */
    private function _getColorStyles() {
        $site = $this->getParams()->getSite('site');
        $style = '';
        foreach (array_reverse($site->getModules()) as $moduleName) {
            $file = new CM_File(CM_Util::getModulePath($moduleName) . 'layout/default/variables.less');
            if ($file->exists()) {
                $style .= $file->read() . PHP_EOL;
            }
        }
        preg_match_all('#@(color\w+)#', $style, $matches);
        $colors = array_unique($matches[1]);
        foreach ($colors as $variableName) {
            $style .= '.' . $variableName . ' { background-color: @' . $variableName . '; }' . PHP_EOL;
        }
        $lessCompiler = new lessc();
        $style = $lessCompiler->compile($style);
        preg_match_all('#.(color\w+)\s+\{([^}]+)\}#', $style, $matches);
        return array_combine($matches[1], $matches[2]);
    }
}
