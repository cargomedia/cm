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
     * @param string                        $methodName
     * @param array                         $params
     * @param CM_Frontend_ViewResponse      $scopeView
     * @param CM_Frontend_ViewResponse|null $scopeComponent
     * @param CM_Frontend_Environment|null  $environment
     * @return CM_Response_View_Ajax
     */
    public function getResponseAjax($methodName, array $params, CM_Frontend_ViewResponse $scopeView, CM_Frontend_ViewResponse $scopeComponent = null, CM_Frontend_Environment $environment = null) {
        $methodName = (string) $methodName;
        $getViewInfo = function (CM_Frontend_ViewResponse $viewResponse) {
            return array(
                'id'        => $viewResponse->getAutoId(),
                'className' => get_class($viewResponse->getView()),
                'params'    => $viewResponse->getView()->getParams()->getAllOriginal()
            );
        };
        $viewInfoList = array_map($getViewInfo,
            array_filter([
                'CM_View_Abstract'      => $scopeView,
                'CM_Component_Abstract' => $scopeComponent,
            ])
        );
        $body = array(
            'method'       => $methodName,
            'params'       => $params,
            'viewInfoList' => $viewInfoList,
        );
        $request = new CM_Request_Post('/ajax/null', null, null, CM_Params::encode($body, true));
        if ($environment && $environment->hasViewer()) {
            $request->getSession()->setUser($environment->getViewer());
        }

        $response = new CM_Response_View_Ajax($request);
        $response->process();
        return $response;
    }

    /**
     * @param string               $formClassName
     * @param string               $actionName
     * @param array                $data
     * @param string|null          $componentClassName Component that uses that form
     * @param CM_Model_User|null   $viewer
     * @param array|null           $componentParams
     * @param CM_Request_Post|null $request
     * @param int|null             $siteId
     * @param string|null          $languageAbbreviation
     * @return CM_Response_View_Form
     */
    public function getResponseForm($formClassName, $actionName, array $data, $componentClassName = null, CM_Model_User $viewer = null, array $componentParams = null, &$request = null, $siteId = null, $languageAbbreviation = null) {
        if (null === $componentParams) {
            $componentParams = array();
        }
        if (null === $siteId) {
            $siteId = 'null';
        }
        if (null !== $languageAbbreviation) {
            $languageAbbreviation .= '/';
        }
        $session = new CM_Session();
        if ($viewer) {
            $session->setUser($viewer);
        }
        $headers = array('Cookie' => 'sessionId=' . $session->getId());
        $server = array('remote_addr' => '1.2.3.4');
        unset($session); // Make sure session is stored persistently

        $viewArray = array('className' => $formClassName, 'params' => array(), 'id' => 'mockFormId');
        $componentArray = array('className' => $componentClassName, 'params' => $componentParams, 'id' => 'mockFormComponentId');
        $body = CM_Params::encode(array(
            'viewInfoList' => array(
                'CM_Component_Abstract' => $componentArray,
                'CM_View_Abstract'      => $viewArray,
            ),
            'actionName'   => $actionName,
            'data'         => $data,
        ), true);
        $request = new CM_Request_Post('/form/' . $languageAbbreviation . $siteId, $headers, $server, $body);

        $response = new CM_Response_View_Form($request);
        $response->process();
        return $response;
    }

    /**
     * @param string $url
     * @param array  $query
     * @return CM_Request_Post|PHPUnit_Framework_MockObject_MockObject
     */
    public function createRequest($url, array $query = null) {
        $url = (string) $url;
        $ip = '16909060';
        $request = $this->getMockBuilder('CM_Request_Post')->setConstructorArgs(array($url))->setMethods(array('getQuery', 'getIp'))->getMock();
        $request->expects($this->any())->method('getQuery')->will($this->returnValue($query));
        $request->expects($this->any())->method('getIp')->will($this->returnValue($ip));
        return $request;
    }

    /**
     * @param CM_FormAction_Abstract $action
     * @param array|null             $data
     * @return CM_Request_Post|PHPUnit_Framework_MockObject_MockObject
     */
    public function createRequestFormAction(CM_FormAction_Abstract $action, array $data = null) {
        $actionName = $action->getName();
        $form = $action->getForm();
        $query = array(
            'data'         => (array) $data,
            'actionName'   => $actionName,
            'viewInfoList' => array(
                'CM_View_Abstract' => array(
                    'className' => get_class($form),
                    'params'    => $form->getParams()->getAll(),
                    'id'        => 'uniqueId'
                )
            )
        );
        return $this->createRequest('/form/null', $query);
    }

    /**
     * @param string        $uri
     * @param CM_Model_User $viewer
     * @return CM_Response_Abstract
     */
    public function processRequest($uri, CM_Model_User $viewer = null) {
        $request = CM_Request_Abstract::factory('GET', $uri);
        if ($viewer) {
            $request->getSession()->setUser($viewer);
        }
        $response = CM_Response_Abstract::factory($request);
        $response->process();
        return $response;
    }

    /**
     * @param string     $pageClass
     * @param array|null $params
     * @return CM_Page_Abstract
     */
    protected function _createPage($pageClass, array $params = null) {
        return new $pageClass(CM_Params::factory($params));
    }

    /**
     * @param CM_Component_Abstract $component
     * @param CM_Model_User|null    $viewer
     * @param CM_Site_Abstract|null $site
     * @return CMTest_TH_Html
     */
    protected function _renderComponent(CM_Component_Abstract $component, CM_Model_User $viewer = null, CM_Site_Abstract $site = null) {
        $render = new CM_Frontend_Render($site, $viewer);
        $renderAdapter = new CM_RenderAdapter_Component($render, $component);
        $componentHtml = $renderAdapter->fetch();
        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /></head><body>' . $componentHtml . '</body></html>';
        return new CMTest_TH_Html($html);
    }

    /**
     * @param CM_FormField_Abstract   $formField
     * @param CM_Params|array|null    $renderParams
     * @param CM_Frontend_Render|null $render
     * @return CM_Dom_NodeList
     */
    protected function _renderFormField(CM_FormField_Abstract $formField, $renderParams = null, CM_Frontend_Render $render = null) {
        if (null === $render) {
            $render = new CM_Frontend_Render();
        }
        $renderAdapter = new CM_RenderAdapter_FormField($render, $formField);
        $html = $renderAdapter->fetch(CM_Params::factory($renderParams));
        return new CM_Dom_NodeList($html);
    }

    /**
     * @param CM_Page_Abstract      $page
     * @param CM_Model_User|null    $viewer
     * @param CM_Site_Abstract|null $site
     * @return CMTest_TH_Html
     */
    protected function _renderPage(CM_Page_Abstract $page, CM_Model_User $viewer = null, CM_Site_Abstract $site = null) {
        if (null === $site) {
            $site = CM_Site_Abstract::factory();
        }
        $host = parse_url($site->getUrl(), PHP_URL_HOST);
        $request = new CM_Request_Get('?' . http_build_query($page->getParams()->getAllOriginal()), array('host' => $host), null, $viewer);
        $response = new CM_Response_Page($request);
        $render = new CM_Frontend_Render($site, $viewer);
        $page->prepareResponse($render->getEnvironment(), $response);
        $renderAdapter = new CM_RenderAdapter_Page($render, $page);
        $html = $renderAdapter->fetch();
        return new CMTest_TH_Html($html);
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
     * @param CMTest_TH_Html $html
     * @param string         $css
     */
    public static function assertHtmlExists(CMTest_TH_Html $html, $css) {
        self::assertTrue($html->exists($css), 'HTML does not contain `' . $css . '`.');
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
     * @param CMTest_TH_Html $page
     * @param bool           $warnings
     */
    public static function assertTidy(CMTest_TH_Html $page, $warnings = true) {
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
