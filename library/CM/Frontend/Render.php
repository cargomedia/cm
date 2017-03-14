<?php

class CM_Frontend_Render extends CM_Class_Abstract implements CM_Service_ManagerAwareInterface {

    use CM_Service_ManagerAwareTrait;

    /** @var CM_Frontend_GlobalResponse|null */
    private $_js;

    /** @var NumberFormatter */
    private $_formatterCurrency;

    /** @var CM_Menu[] */
    private $_menuList = array();

    /** @var CM_Frontend_Environment */
    private $_environment;

    /** @var Smarty|null */
    private $_smarty;

    /**
     * @param CM_Frontend_Environment|null $environment
     * @param CM_Service_Manager|null      $serviceManager
     */
    public function __construct(CM_Frontend_Environment $environment = null, CM_Service_Manager $serviceManager = null) {
        if (!$environment) {
            $environment = new CM_Frontend_Environment();
        }
        $this->_environment = $environment;
        if (null === $serviceManager) {
            $serviceManager = CM_Service_Manager::getInstance();
        }
        $this->setServiceManager($serviceManager);
    }

    /**
     * @return CM_Site_Abstract
     */
    public function getSite() {
        return $this->getEnvironment()->getSite();
    }

    /**
     * @return CM_Frontend_Environment
     */
    public function getEnvironment() {
        return $this->_environment;
    }

    /**
     * @return CM_Frontend_GlobalResponse
     */
    public function getGlobalResponse() {
        if (null === $this->_js) {
            $this->_js = new CM_Frontend_GlobalResponse();
        }
        return $this->_js;
    }

    /**
     * @param string        $path
     * @param array|null    $variables
     * @param string[]|null $compileId
     * @return string
     */
    public function fetchTemplate($path, array $variables = null, array $compileId = null) {
        $compileId = (array) $compileId;
        $compileId[] = $this->getSite()->getId();
        if ($this->getLanguage()) {
            $compileId[] = $this->getLanguage()->getAbbreviation();
        }
        /** @var Smarty_Internal_TemplateBase $template */
        $template = $this->_getSmarty()->createTemplate($path, null, join('_', $compileId));
        $template->assignGlobal('render', $this);
        $template->assignGlobal('viewer', $this->getViewer());
        if ($variables) {
            $template->assign($variables);
        }
        return $template->fetch();
    }

    /**
     * @param string     $content
     * @param array|null $variables
     * @return string
     */
    public function parseTemplateContent($content, array $variables = null) {
        $content = 'string:' . $content;
        return $this->fetchTemplate($content, $variables);
    }

    /**
     * @param bool|null   $absolute True if full path required
     * @param string|null $theme
     * @param string|null $namespace
     * @return string Theme base path
     */
    public function getThemeDir($absolute = false, $theme = null, $namespace = null) {
        if (!$theme) {
            $theme = $this->getSite()->getTheme();
        }
        if (!$namespace) {
            $namespace = $this->getSite()->getModule();
        }

        $path = CM_Util::getModulePath($namespace, !$absolute);
        return $path . 'layout/' . $theme . '/';
    }

    /**
     * @param string                $template Template file name
     * @param string|null           $module
     * @param string|null           $theme
     * @param bool|null             $absolute
     * @param bool|null             $needed
     * @param CM_Site_Abstract|null $site
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getLayoutPath($template, $module = null, $theme = null, $absolute = null, $needed = null, CM_Site_Abstract $site = null) {
        if (null === $needed) {
            $needed = true;
        }
        if (null === $site) {
            $site = $this->getSite();
        }
        $moduleList = $site->getModules();
        if ($module !== null) {
            $moduleList = array((string) $module);
        }
        $themeList = $site->getThemes();
        if ($theme !== null) {
            $themeList = array((string) $theme);
        }
        foreach ($moduleList as $module) {
            foreach ($themeList as $theme) {
                $file = new CM_File($this->getThemeDir(true, $theme, $module) . $template);
                if ($file->exists()) {
                    if ($absolute) {
                        return $file->getPath();
                    } else {
                        return $this->getThemeDir(false, $theme, $module) . $template;
                    }
                }
            }
        }

        if ($needed) {
            throw new CM_Exception_Invalid('Can\'t find template', null, [
                'template' => $template,
                'modules'  => implode('`, `', $moduleList),
                'themes'   => implode('`, `', $site->getThemes()),
            ]);
        }
        return null;
    }

    /**
     * @param string                $path
     * @param string|null           $namespace
     * @param CM_Site_Abstract|null $site
     * @return CM_File
     * @throws CM_Exception_Invalid
     */
    public function getLayoutFile($path, $namespace = null, CM_Site_Abstract $site = null) {
        return new CM_File($this->getLayoutPath($path, $namespace, null, true, null, $site));
    }

    /**
     * @return string
     */
    public function getSiteName() {
        return $this->getSite()->getName();
    }

    /**
     * @param string|null           $path
     * @param CM_Site_Abstract|null $site
     * @return string
     */
    public function getUrl($path = null, CM_Site_Abstract $site = null) {
        if (null === $path) {
            $path = '';
        }
        if (null === $site) {
            $site = $this->getSite();
        }
        $path = (string) $path;
        return $site->getUrl() . $path;
    }

    /**
     * @param CM_Page_Abstract|string $pageClassName
     * @param array|null              $params
     * @param CM_Site_Abstract|null   $site
     * @param CM_Model_Language|null  $language
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function getUrlPage($pageClassName, array $params = null, CM_Site_Abstract $site = null, CM_Model_Language $language = null) {
        if (null === $site) {
            $site = $this->getSite();
        }
        if ($pageClassName instanceof CM_Page_Abstract) {
            $pageClassName = get_class($pageClassName);
        }
        $pageClassName = (string) $pageClassName;

        if (!class_exists($pageClassName) || !is_subclass_of($pageClassName, 'CM_Page_Abstract')) {
            throw new CM_Exception_Invalid('Cannot find valid class definition for page class name', null, ['pageClassName' => $pageClassName]);
        }
        if (!preg_match('/^([A-Za-z]+)_/', $pageClassName, $matches)) {
            throw new CM_Exception_Invalid('Cannot find namespace of page class name', null, ['pageClassName' => $pageClassName]);
        }
        $namespace = $matches[1];
        if (!in_array($namespace, $site->getModules())) {
            throw new CM_Exception_Invalid('Site does not contain namespace', null, [
                'site'      => get_class($site),
                'namespace' => $namespace,
            ]);
        }
        /** @var CM_Page_Abstract $pageClassName */
        $path = $pageClassName::getPath($params);

        if (!$language) {
            $language = $this->getLanguage();
        }
        if ($language) {
            $path = '/' . $language->getAbbreviation() . $path;
        }
        return $this->getUrl($path, $site);
    }

    /**
     * @param string|null           $type
     * @param string|null           $path
     * @param array|null            $options
     * @param CM_Site_Abstract|null $site
     * @return string
     */
    public function getUrlResource($type = null, $path = null, array $options = null, CM_Site_Abstract $site = null) {
        $options = array_merge([
            'sameOrigin' => false,
        ], (array) $options);
        if (null === $site) {
            $site = $this->getSite();
        }

        if (!$options['sameOrigin'] && $this->getSite()->getUrlCdn()) {
            $url = $site->getUrlCdn();
        } else {
            $url = $site->getUrlBase();
        }

        if (!is_null($type) && !is_null($path)) {
            $pathParts = [];
            $pathParts[] = (string) $type;
            if ($this->getLanguage()) {
                $pathParts[] = $this->getLanguage()->getAbbreviation();
            }
            $pathParts[] = $site->getType();
            $pathParts[] = CM_App::getInstance()->getDeployVersion();
            $pathParts = array_merge($pathParts, explode('/', $path));

            $url .= '/' . implode('/', $pathParts);
        }

        return $url;
    }

    /**
     * @return string
     */
    public function getUrlServiceWorker() {
        $pathParts = [];
        $pathParts[] = 'serviceworker';
        if ($this->getLanguage()) {
            $pathParts[] = $this->getLanguage()->getAbbreviation();
        }
        $pathParts[] = CM_App::getInstance()->getDeployVersion();

        $path = '/' . implode('-', $pathParts) . '.js';

        return $this->getUrl($path);
    }

    /**
     * @param CM_Mail_Mailable $mail
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getUrlEmailTracking(CM_Mail_Mailable $mail) {
        if (!$mail->getRecipient()) {
            throw new CM_Exception_Invalid('Needs user');
        }
        $params = array('user' => $mail->getRecipient()->getId(), 'mailType' => $mail->getType());
        return CM_Util::link($this->getSite()->getUrl() . '/emailtracking', $params);
    }

    /**
     * @param string|null           $path
     * @param CM_Site_Abstract|null $site
     * @return string
     */
    public function getUrlStatic($path = null, CM_Site_Abstract $site = null) {
        if (null === $site) {
            $site = $this->getSite();
        }
        if ($this->getSite()->getUrlCdn()) {
            $url = $site->getUrlCdn();
        } else {
            $url = $site->getUrlBase();
        }

        $url .= '/static';
        if (null !== $path) {
            $url .= $path . '?' . CM_App::getInstance()->getDeployVersion();
        }

        return $url;
    }

    /**
     * @param CM_File_UserContent $file
     * @return string
     */
    public function getUrlUserContent(CM_File_UserContent $file) {
        return $file->getUrl();
    }

    /**
     * @return CM_Model_User|null
     */
    public function getViewer() {
        return $this->getEnvironment()->getViewer();
    }

    /**
     * @return CM_Model_Language|null
     */
    public function getLanguage() {
        return $this->getEnvironment()->getLanguage();
    }

    /**
     * @param string     $key
     * @param array|null $params
     * @return string
     */
    public function getTranslation($key, array $params = null) {
        $params = (array) $params;
        $translation = $key;
        if ($language = $this->getLanguage()) {
            $translation = $language->getTranslation($key, array_keys($params));
        }
        $translation = $this->_parseVariables($translation, $params);
        return $translation;
    }

    public function clearTemplates() {
        $this->_getSmarty()->clearCompiledTemplate();
    }

    /**
     * @param int               $dateType
     * @param int               $timeType
     * @param string|null       $pattern
     * @param DateTimeZone|null $timeZone
     * @return IntlDateFormatter
     * @throws CM_Exception
     */
    public function getFormatterDate($dateType, $timeType, $pattern = null, DateTimeZone $timeZone = null) {
        if (null === $timeZone) {
            $timeZone = $this->getEnvironment()->getTimeZone();
        }
        $timeZoneName = $timeZone->getName();
        if (in_array(substr($timeZoneName, 0, 1), ['+', '-'])) {
            $timeZoneName = 'GMT' . $timeZoneName;
        }

        $formatter = new IntlDateFormatter($this->getLocale(), $dateType, $timeType, $timeZoneName, null, $pattern);
        if (null === $formatter) {
            throw new CM_Exception('Cannot create date formatter', null, [
                'locale'       => $this->getLocale(),
                'dateType'     => $dateType,
                'timeType'     => $timeType,
                'timeZoneName' => $timeZoneName,
                'pattern'      => $pattern,
            ]);
        }
        return $formatter;
    }

    /**
     * @return NumberFormatter
     */
    public function getFormatterCurrency() {
        if (!$this->_formatterCurrency) {
            $this->_formatterCurrency = new NumberFormatter($this->getLocale(), NumberFormatter::CURRENCY);
        }
        return $this->_formatterCurrency;
    }

    /**
     * @return string
     */
    public function getLocale() {
        return $this->getEnvironment()->getLocale();
    }

    /**
     * @param CM_Menu $menu
     */
    public function addMenu(CM_Menu $menu) {
        $this->_menuList[] = $menu;
    }

    /**
     * @return CM_Menu[]
     */
    public function getMenuList() {
        return $this->_menuList;
    }

    /**
     * @param CM_View_Abstract $view
     * @param string           $templateName
     * @throws CM_Exception
     * @return string|null
     */
    public function getTemplatePath(CM_View_Abstract $view, $templateName) {
        $templateName = (string) $templateName;
        foreach ($view->getClassHierarchy() as $className) {
            if (!preg_match('/^([a-zA-Z]+)_([a-zA-Z]+)_(.+)$/', $className, $matches)) {
                throw new CM_Exception('Cannot detect namespace/view-class/view-name for className and templateName.', null, [
                    'className'    => $className,
                    'templateName' => $templateName,
                ]);
            }
            $templatePathRelative = $matches[2] . DIRECTORY_SEPARATOR . $matches[3] . DIRECTORY_SEPARATOR . $templateName . '.tpl';
            $namespace = $matches[1];
            if ($templatePath = $this->getLayoutPath($templatePathRelative, $namespace, null, false, false)) {
                return $templatePath;
            }
        }
        return null;
    }

    /**
     * @param CM_View_Abstract $view
     * @param string           $templateName
     * @param array|null       $data
     * @throws CM_Exception
     * @return string
     */
    public function fetchViewTemplate(CM_View_Abstract $view, $templateName, array $data = null) {
        $templatePath = $this->getTemplatePath($view, $templateName);
        if (null === $templatePath) {
            throw new CM_Exception('Cannot find template for the view', null, [
                'template'      => $templateName,
                'viewClassName' => get_class($view),
            ]);
        }
        $viewClassName = get_class($view);
        return $this->fetchTemplate($templatePath, $data, [$viewClassName]);
    }

    /**
     * @param CM_Frontend_ViewResponse $viewResponse
     * @return string
     */
    public function fetchViewResponse(CM_Frontend_ViewResponse $viewResponse) {
        return $this->fetchViewTemplate($viewResponse->getView(), $viewResponse->getTemplateName(), $viewResponse->getData());
    }

    /**
     * @param string $classname
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getClassnameByPartialClassname($classname) {
        $classname = (string) $classname;
        foreach ($this->getSite()->getModules() as $availableNamespace) {
            $classnameWithNamespace = $availableNamespace . '_' . $classname;
            if (class_exists($classnameWithNamespace)) {
                return $classnameWithNamespace;
            }
        }
        throw new CM_Exception_Invalid('The class was not found in any namespace.', null, ['name' => $classname]);
    }

    /**
     * @param $variableName
     * @return string
     * @throws CM_Exception_Invalid
     */
    public function getLessVariable($variableName) {
        $variableName = (string) $variableName;

        $cache = new CM_Cache_Local();
        return $cache->get($cache->key(__METHOD__, $this->getSite()->getTheme(), $variableName), function () use ($variableName) {
            $assetCss = new CM_Asset_Css($this);
            $assetCss->addVariables();
            $assetCss->add('foo:@' . $variableName);

            $css = $assetCss->get();
            if (!preg_match('/^foo:(.+);$/', $css, $matches)) {
                throw new CM_Exception_Invalid('Cannot detect variable from CSS.', null, [
                    'variableName' => $variableName,
                    'css'          => $css,
                ]);
            }
            return (string) $matches[1];
        });
    }

    /**
     * @return Smarty
     */
    private function _getSmarty() {
        if (null === $this->_smarty) {
            $this->_smarty = new Smarty();
            $this->_smarty->setTemplateDir(DIR_ROOT);
            $this->_smarty->setCompileDir(CM_Bootloader::getInstance()->getDirTmp() . 'smarty/');
            $this->_smarty->_file_perms = 0666;
            $this->_smarty->_dir_perms = 0777;
            $this->_smarty->compile_check = CM_Bootloader::getInstance()->isDebug();
            $this->_smarty->caching = false;
            $this->_smarty->error_reporting = error_reporting();
        }

        $pluginDirs = array(SMARTY_PLUGINS_DIR);
        foreach ($this->getSite()->getModules() as $moduleName) {
            $pluginDirs[] = CM_Util::getModulePath($moduleName) . 'library/' . $moduleName . '/SmartyPlugins';
        }
        $this->_smarty->setPluginsDir($pluginDirs);
        $this->_smarty->loadFilter('pre', 'translate');

        return $this->_smarty;
    }

    /**
     * @param string $phrase
     * @param array  $variables
     * @return string
     */
    private function _parseVariables($phrase, array $variables) {
        return preg_replace_callback('~\{\$(\w+)\}~', function ($matches) use ($variables) {
            return isset($variables[$matches[1]]) ? $variables[$matches[1]] : '';
        }, $phrase);
    }
}
