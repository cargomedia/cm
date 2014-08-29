<?php

abstract class CMTest_TestCase extends PHPUnit_Framework_TestCase {

    use \Mocka\MockaTrait;

    public function runBare() {
        if (!isset(CM_Config::get()->CM_Site_Abstract->class)) {
            $siteDefault = $this->getMockSite(null, null, array(
                'url'          => 'http://www.default.dev',
                'urlCdn'       => 'http://cdn.default.dev',
                'name'         => 'Default',
                'emailAddress' => 'default@default.dev',
            ));
            CM_Config::get()->CM_Site_Abstract->class = get_class($siteDefault);
        }
        parent::runBare();
    }

    public static function tearDownAfterClass() {
        CMTest_TH::clearEnv();
    }

    /**
     * @return CM_Form_Abstract
     */
    public function getMockForm() {
        $formMock = $this->getMockForAbstractClass('CM_Form_Abstract');
        $formMock->expects($this->any())->method('getName')->will($this->returnValue('formName'));
        return $formMock;
    }

    /**
     * @param string|null $classname
     * @param int|null    $type
     * @param array|null  $configuration
     * @param array|null  $methods
     * @throws CM_Exception_Invalid
     * @return CM_Site_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockSite($classname = null, $type = null, array $configuration = null, array $methods = null) {
        if (null === $classname) {
            $classname = 'CM_Site_Abstract';
        }
        $classname = (string) $classname;
        $config = CM_Config::get();

        if (null === $type) {
            $type = $config->CM_Class_Abstract->typesMaxValue + 1;
        }
        $type = (int) $type;
        $types = $config->CM_Site_Abstract->types;
        if (isset($types[$type])) {
            throw new CM_Exception_Invalid('Site type ' . $type . ' already used');
        }
        $methods = (array) $methods;
        $defaultConfiguration = array(
            'url'          => null,
            'urlCdn'       => null,
            'name'         => null,
            'emailAddress' => null,
        );
        $configuration = array_merge($defaultConfiguration, (array) $configuration);

        $mockClassname = $classname . '_Mock' . $type . '_' . uniqid();
        $site = $this->getMockForAbstractClass($classname, array(), $mockClassname, true, true, true, $methods);
        $siteClassName = get_class($site);
        $config->CM_Site_Abstract->types[$type] = $siteClassName;
        $config->$siteClassName = new stdClass;
        $config->$siteClassName->type = $type;
        foreach ($configuration as $key => $value) {
            $config->$siteClassName->$key = $value;
        }
        $config->CM_Class_Abstract->typesMaxValue = $type;

        return $site;
    }

    /**
     * @param string                        $url
     * @param array|null                    $query
     * @param CM_Frontend_ViewResponse|null $scopeView
     * @param CM_Frontend_ViewResponse|null $scopeComponent
     * @return CM_Request_Post|\Mocka\AbstractClassTrait
     * @throws Mocka\Exception
     */
    public function createRequest($url, array $query = null, CM_Frontend_ViewResponse $scopeView = null, CM_Frontend_ViewResponse $scopeComponent = null) {
        $url = (string) $url;
        $query = (array) $query;
        $getViewInfo = function (CM_Frontend_ViewResponse $viewResponse) {
            /**
             * Simulate sending view-params to client and back (remove any objects)
             */
            $viewParams = $viewResponse->getView()->getParams()->getParamsDecoded();
            $viewParams = CM_Params::decode(CM_Params::encode($viewParams, true), true);
            return array(
                'id'        => $viewResponse->getAutoId(),
                'className' => get_class($viewResponse->getView()),
                'params'    => $viewParams,
            );
        };
        $viewInfoList = array_map($getViewInfo,
            array_filter([
                'CM_View_Abstract'      => $scopeView,
                'CM_Component_Abstract' => $scopeComponent,
            ])
        );
        if ($viewInfoList) {
            $query['viewInfoList'] = $viewInfoList;
        }

        $mockClass = $this->mockClass('CM_Request_Post');
        $mockClass->mockMethod('getQuery')->set(function () use ($query) {
            return $query;
        });
        $mockClass->mockMethod('getIp')->set(function () {
            return '16909060';
        });
        return $mockClass->newInstance([$url]);
    }

    /**
     * @param CM_FormAction_Abstract        $action
     * @param array|null                    $data
     * @param CM_Frontend_ViewResponse|null $scopeView
     * @param CM_Frontend_ViewResponse|null $scopeComponent
     * @throws CM_Exception_Invalid
     * @return CM_Request_Post|\Mocka\AbstractClassTrait
     */
    public function createRequestFormAction(CM_FormAction_Abstract $action, array $data = null, CM_Frontend_ViewResponse $scopeView = null, CM_Frontend_ViewResponse $scopeComponent = null) {
        $actionName = $action->getName();
        $form = $action->getForm();
        if (null === $scopeView) {
            $scopeView = new CM_Frontend_ViewResponse($form);
        }
        if ($scopeView->getView() !== $form) {
            throw new CM_Exception_Invalid('Action\'s form and ViewResponse\'s view must match');
        }
        if (null === $scopeComponent) {
            $component = $this->mockClass('CM_Component_Abstract')->newInstance();
            $scopeComponent = new CM_Frontend_ViewResponse($component);
        }
        $query = array(
            'data'       => (array) $data,
            'actionName' => $actionName,
        );
        return $this->createRequest('/form/null', $query, $scopeView, $scopeComponent);
    }

    /**
     * @param CM_Component_Abstract         $component
     * @param string                        $methodName
     * @param array|null                    $params
     * @param CM_Frontend_ViewResponse|null $scopeView
     * @param CM_Frontend_ViewResponse|null $scopeComponent
     * @return CM_Request_Post|\Mocka\AbstractClassTrait
     */
    public function createRequestAjax(CM_Component_Abstract $component, $methodName, array $params = null, CM_Frontend_ViewResponse $scopeView = null, CM_Frontend_ViewResponse $scopeComponent = null) {
        $viewResponseComponent = new CM_Frontend_ViewResponse($component);
        if (null === $scopeView) {
            $scopeView = $viewResponseComponent;
        }
        if (null === $scopeComponent) {
            $scopeComponent = $viewResponseComponent;
        }
        $query = array(
            'method' => (string) $methodName,
            'params' => (array) $params,
        );
        return $this->createRequest('/ajax/null', $query, $scopeView, $scopeComponent);
    }

    /**
     * @param CM_Request_Abstract $request
     * @return CM_Response_Abstract|\Mocka\AbstractClassTrait
     */
    public function getResponse(CM_Request_Abstract $request) {
        $className = CM_Response_Abstract::getResponseClassName($request);
        return $this->mockClass($className)->newInstance([$request]);
    }

    /**
     * @param CM_Request_Abstract $request
     * @return CM_Response_Abstract|\Mocka\AbstractClassTrait
     */
    public function processRequest(CM_Request_Abstract $request) {
        $response = $this->getResponse($request);
        $response->process();
        return $response;
    }

    /**
     * @param CM_Component_Abstract        $component
     * @param string                       $methodName
     * @param array|null                   $params
     * @param CM_Frontend_Environment|null $environment
     * @return CM_Response_View_Ajax
     */
    public function getResponseAjax(CM_Component_Abstract $component, $methodName, array $params = null, CM_Frontend_Environment $environment = null) {
        $request = $this->createRequestAjax($component, $methodName, $params);
        if ($environment) {
            $request->mockMethod('getViewer')->set(function () use ($environment) {
                return $environment->getViewer();
            });
        }
        return $this->processRequest($request);
    }

    /**
     * @param CM_FormAction_Abstract   $action
     * @param array                    $data
     * @param CM_Frontend_ViewResponse $scopeComponent
     * @return CM_Response_View_Form
     */
    public function getResponseFormAction(CM_FormAction_Abstract $action, array $data = null, CM_Frontend_ViewResponse $scopeComponent = null) {
        $request = $this->createRequestFormAction($action, $data, $scopeComponent);
        return $this->processRequest($request);
    }

    /**
     * @param object|string $objectOrClass
     * @param string        $methodName
     * @param array|null    $arguments
     * @return mixed
     */
    public function forceInvokeMethod($objectOrClass, $methodName, array $arguments = null) {
        $context = null;
        if (is_object($objectOrClass)) {
            $context = $objectOrClass;
        }
        $arguments = (array) $arguments;
        $reflectionClass = new ReflectionClass($objectOrClass);
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($context, $arguments);
    }

    /**
     * @param string     $pageClass
     * @param array|null $params
     * @return CM_Page_Abstract
     */
    protected function _createPage($pageClass, array $params = null) {
        return new $pageClass(CM_Params::factory($params, false));
    }

    /**
     * @param CM_Component_Abstract $component
     * @param CM_Model_User|null    $viewer
     * @param CM_Site_Abstract|null $site
     * @return CM_Dom_NodeList
     */
    protected function _renderComponent(CM_Component_Abstract $component, CM_Model_User $viewer = null, CM_Site_Abstract $site = null) {
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site, $viewer));
        $renderAdapter = new CM_RenderAdapter_Component($render, $component);
        $componentHtml = $renderAdapter->fetch();
        return new CM_Dom_NodeList($componentHtml, true);
    }

    /**
     * @param CM_FormField_Abstract   $formField
     * @param array|null              $renderParams
     * @param CM_Frontend_Render|null $render
     * @return CM_Dom_NodeList
     */
    protected function _renderFormField(CM_FormField_Abstract $formField, array $renderParams = null, CM_Frontend_Render $render = null) {
        if (null === $render) {
            $render = new CM_Frontend_Render();
        }
        $renderAdapter = new CM_RenderAdapter_FormField($render, $formField);
        $html = $renderAdapter->fetch(CM_Params::factory($renderParams, false));
        return new CM_Dom_NodeList($html, true);
    }

    /**
     * @param CM_Page_Abstract      $page
     * @param CM_Model_User|null    $viewer
     * @param CM_Site_Abstract|null $site
     * @return CM_Dom_NodeList
     */
    protected function _renderPage(CM_Page_Abstract $page, CM_Model_User $viewer = null, CM_Site_Abstract $site = null) {
        if (null === $site) {
            $site = CM_Site_Abstract::factory();
        }
        $host = parse_url($site->getUrl(), PHP_URL_HOST);
        $request = new CM_Request_Get('?' . http_build_query($page->getParams()->getParamsEncoded()), array('host' => $host), null, $viewer);
        $response = new CM_Response_Page($request);
        $render = new CM_Frontend_Render(new CM_Frontend_Environment($site, $viewer));
        $page->prepareResponse($render->getEnvironment(), $response);
        $renderAdapter = new CM_RenderAdapter_Page($render, $page);
        $html = $renderAdapter->fetch();
        return new CM_Dom_NodeList($html, true);
    }

    /**
     * @param CM_Response_View_Abstract $response
     * @param array|null                $data
     */
    public static function assertViewResponseSuccess(CM_Response_View_Abstract $response, array $data = null) {
        $responseContent = json_decode($response->getContent(), true);
        self::assertArrayHasKey('success', $responseContent, 'View response not successful');
        if (null !== $data) {
            self::assertSame($data, $responseContent['success']['data']);
        }
    }

    /**
     * @param CM_Response_View_Abstract $response
     * @param string|null               $type
     * @param string|null               $message
     */
    public static function assertViewResponseError(CM_Response_View_Abstract $response, $type = null, $message = null) {
        $responseContent = json_decode($response->getContent(), true);
        self::assertArrayHasKey('error', $responseContent, 'View response successful');
        if (null !== $type) {
            self::assertSame($type, $responseContent['error']['type']);
        }
        if (null !== $message) {
            self::assertSame($message, $responseContent['error']['msg']);
        }
    }

    /**
     *
     * @param array $needles
     * @param array $haystacks
     */
    public static function assertArrayContains(array $needles, array $haystacks) {
        if (count($haystacks) < count($needles)) {
            self::fail('not enough elements to compare each');
        }
        foreach ($needles as $key => $value) {
            self::assertContains($value, $haystacks[$key]);
        }
    }

    /**
     * @param CM_Component_Abstract $cmp
     * @param CM_Model_User|null    $viewer
     */
    public static function assertComponentAccessible(CM_Component_Abstract $cmp, CM_Model_User $viewer = null) {
        $environment = new CM_Frontend_Environment(null, $viewer);
        try {
            $cmp->checkAccessible($environment);
            self::assertTrue(true);
        } catch (CM_Exception_AuthRequired $e) {
            self::fail('should be accessible');
        } catch (CM_Exception_Nonexistent $e) {
            self::fail('should be accessible');
        }
    }

    /**
     * @param CM_Component_Abstract $cmp
     * @param CM_Model_User|null    $viewer
     * @param string|null           $expectedExceptionClass
     */
    public static function assertComponentNotAccessible(CM_Component_Abstract $cmp, CM_Model_User $viewer = null, $expectedExceptionClass = null) {
        $environment = new CM_Frontend_Environment(null, $viewer);
        $expectedExceptionClassList = array(
            'CM_Exception_AuthRequired',
            'CM_Exception_Nonexistent',
            'CM_Exception_NotAllowed',
        );
        if (null !== $expectedExceptionClass) {
            $expectedExceptionClassList = array($expectedExceptionClass);
        }
        try {
            $cmp->checkAccessible($environment);
            self::fail('checkAccessible should throw exception');
        } catch (Exception $e) {
            self::assertTrue(in_array(get_class($e), $expectedExceptionClassList));
        }
    }

    /**
     * @param CM_Component_Abstract $component
     * @param CM_Model_User|null    $viewer
     * @param string|null           $expectedExceptionClass
     */
    public function assertComponentNotRenderable(CM_Component_Abstract $component, CM_Model_User $viewer = null, $expectedExceptionClass = null) {
        if (null === $expectedExceptionClass) {
            $expectedExceptionClass = 'CM_Exception';
        }
        try {
            $this->_renderComponent($component, $viewer);
            $this->fail('Rendering page `' . get_class($component) . '` did not throw an exception');
        } catch (Exception $e) {
            $this->assertInstanceOf($expectedExceptionClass, $e);
        }
    }

    /**
     * @param mixed|CM_Comparable $needle
     * @param Traversable|string  $haystack
     * @param string              $message
     * @param boolean             $ignoreCase
     * @param boolean             $checkForObjectIdentity
     * @param bool                $checkForNonObjectIdentity
     * @throws CM_Exception_Invalid
     */
    public static function assertContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false) {
        if ($needle instanceof CM_Comparable) {
            if (!(is_array($haystack) || $haystack instanceof Traversable)) {
                throw new CM_Exception_Invalid('Haystack is not traversable.');
            }
            $match = false;
            foreach ($haystack as $hay) {
                if ($needle->equals($hay)) {
                    $match = true;
                    break;
                }
            }
            self::assertTrue($match, 'Needle not contained.');
        } else {
            parent::assertContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity, $checkForNonObjectIdentity);
        }
    }

    /**
     * @param mixed|CM_Comparable $needle
     * @param mixed|Traversable   $haystack
     * @param string              $message
     * @param boolean             $ignoreCase
     * @param boolean             $checkForObjectIdentity
     * @param bool                $checkForNonObjectIdentity
     * @throws CM_Exception_Invalid
     */
    public static function assertNotContains($needle, $haystack, $message = '', $ignoreCase = false, $checkForObjectIdentity = true, $checkForNonObjectIdentity = false) {
        if ($needle instanceof CM_Comparable) {
            if (!(is_array($haystack) || $haystack instanceof Traversable)) {
                throw new CM_Exception_Invalid('Haystack is not traversable.');
            }
            $match = false;
            foreach ($haystack as $hay) {
                if ($needle->equals($hay)) {
                    $match = true;
                    break;
                }
            }
            self::assertFalse($match, 'Needle contained.');
        } else {
            parent::assertNotContains($needle, $haystack, $message, $ignoreCase, $checkForObjectIdentity, $checkForNonObjectIdentity);
        }
    }

    /**
     * @param array $needles
     * @param mixed $haystack
     */
    public static function assertContainsAll(array $needles, $haystack) {
        foreach ($needles as $needle) {
            self::assertContains($needle, $haystack);
        }
    }

    /**
     * @param array $needles
     * @param mixed $haystack
     */
    public static function assertNotContainsAll(array $needles, $haystack) {
        foreach ($needles as $needle) {
            self::assertNotContains($needle, $haystack);
        }
    }

    public static function assertEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = true) {
        if ($expected instanceof CM_Paging_Abstract) {
            $expected = $expected->getItems();
        }
        if ($actual instanceof CM_Paging_Abstract) {
            $actual = $actual->getItems();
        }
        if (is_array($expected) && is_array($actual)) {
            self::assertSame(array_keys($expected), array_keys($actual), $message);
            foreach ($expected as $expectedKey => $expectedValue) {
                self::assertEquals($expectedValue, $actual[$expectedKey], $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
            }
            return;
        }
        if ($expected instanceof CM_Comparable) {
            self::assertTrue($expected->equals($actual), 'Comparables differ');
        } else {
            parent::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
        }
    }

    public static function assertNotEquals($expected, $actual, $message = '', $delta = 0, $maxDepth = 10, $canonicalize = false, $ignoreCase = false) {
        try {
            self::assertEquals($expected, $actual, $message, $delta, $maxDepth, $canonicalize, $ignoreCase);
        } catch (PHPUnit_Framework_AssertionFailedError $exception) {
            return;
        }
        self::fail($message);
    }

    /**
     * @param CM_Response_View_Form $response
     * @param string|null           $msg
     */
    public static function assertFormResponseSuccess(CM_Response_View_Form $response, $msg = null) {
        $responseContent = json_decode($response->getContent(), true);
        self::assertFalse($response->hasErrors(), 'Response has errors.');
        if (null !== $msg) {
            $msg = (string) $msg;
            self::assertContains($msg, $responseContent['success']['messages'], 'Response has no message `' . $msg . '`.');
        }
    }

    /**
     * @param CM_Response_View_Form $response
     * @param string|null           $errorMsg
     * @param string|null           $formFieldName
     */
    public static function assertFormResponseError(CM_Response_View_Form $response, $errorMsg = null, $formFieldName = null) {
        $responseContent = json_decode($response->getContent(), true);
        self::assertTrue($response->hasErrors());
        if (null !== $errorMsg) {
            $errorMsg = (string) $errorMsg;
            $error = $errorMsg;
            if (null !== $formFieldName) {
                $formFieldName = (string) $formFieldName;
                $error = array($errorMsg, $formFieldName);
            }
            self::assertContains($error, $responseContent['success']['errors']);
        }
    }

    /**
     * @param CM_Dom_NodeList $html
     * @param string          $css
     */
    public static function assertHtmlExists(CM_Dom_NodeList $html, $css) {
        self::assertTrue($html->has($css), 'HTML does not contain `' . $css . '`.');
    }

    /**
     * @param CM_Page_Abstract $page
     */
    public static function assertPageNotViewable(CM_Page_Abstract $page) {
        self::assertFalse($page->isViewable());
    }

    /**
     * @param CM_Page_Abstract   $page
     * @param string|null        $expectedExceptionClass
     * @param CM_Model_User|null $viewer
     */
    public function assertPageNotRenderable(CM_Page_Abstract $page, $expectedExceptionClass = null, CM_Model_User $viewer = null) {
        if (null === $expectedExceptionClass) {
            $expectedExceptionClass = 'CM_Exception';
        }
        try {
            $this->_renderPage($page, $viewer);
            $this->fail('Rendering page `' . get_class($page) . '` did not throw an exception');
        } catch (Exception $e) {
            $this->assertInstanceOf($expectedExceptionClass, $e);
        }
    }

    /**
     * @param string            $table
     * @param array|string|null $where WHERE conditions: ('attr' => 'value', 'attr2' => 'value')
     * @param int|null          $rowCount
     */
    public static function assertRow($table, $where = null, $rowCount = null) {
        if (null === $rowCount) {
            $rowCount = 1;
        }
        $result = CM_Db_Db::select($table, '*', $where);
        $rowCountActual = count($result->fetchAll());
        self::assertEquals($rowCount, $rowCountActual);
    }

    /**
     * @param string            $table
     * @param array|string|null $where
     */
    public static function assertNotRow($table, $where = null) {
        self::assertRow($table, $where, 0);
    }

    /**
     * @param number $expected
     * @param number $actual
     * @param number|null
     */
    public static function assertSameTime($expected, $actual, $delta = null) {
        if (null === $delta) {
            $delta = 1;
        }
        self::assertEquals($expected, $actual, '', $delta);
    }

    /**
     * @param CM_Dom_NodeList $page
     * @param bool            $warnings
     */
    public static function assertTidy(CM_Dom_NodeList $page, $warnings = true) {
        if (!extension_loaded('tidy')) {
            self::markTestSkipped('The tidy extension is not available.');
        }

        $html = $page->getHtml();
        $tidy = new tidy();

        $tidyConfig = array('show-errors' => 1, 'show-warnings' => $warnings);
        $tidy->parseString($html, $tidyConfig, 'UTF8');

        //$tidy->cleanRepair();
        $tidy->diagnose();
        $lines = array_reverse(explode("\n", $tidy->errorBuffer));
        $content = '';

        foreach ($lines as $line) {
            if (empty($line) || $line == 'No warnings or errors were found.' || strpos($line, 'Info:') === 0 ||
                strpos($line, 'errors were found!') > 0 || strpos($line, 'proprietary attribute') != false
            ) {
                // ignore
            } else {
                $content .= $line . PHP_EOL;
            }
        }

        self::assertEmpty($content, $content);
    }
}
