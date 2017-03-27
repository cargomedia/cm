<?php

use CM\Url\Url;
use CM\Url\RouteUrl;
use CM\Url\ServiceWorkerUrl;
use CM\Url\ResourceUrl;
use CM\Url\StaticUrl;

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
        if (null === $site) {
            $site = $this->getEnvironment()->getSite();
        }
        $cache = $this->_getCache();
        $cacheKey = $this->_getCacheUrlKey($path, null, [get_class($site)]);
        return $cache->get($cacheKey, function () use ($path, $site) {
            return (string) Url::create((string) $path)->withSite($site);
        });
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
        if ($pageClassName instanceof CM_Page_Abstract) {
            $pageClassName = get_class($pageClassName);
        }
        $environment = clone $this->getEnvironment();
        if (null !== $site) {
            $environment->setSite($site);
        }
        if (null !== $language) {
            $environment->setLanguage($language);
        }

        $cache = $this->_getCache();
        $cacheKey = $this->_getCacheUrlKey($pageClassName, $environment, $params);
        return $cache->get($cacheKey, function () use ($pageClassName, $params, $environment) {
            return (string) CM_Page_UrlFactory::getUrl($pageClassName, $params, $environment);
        });
    }

    /**
     * @param string                $type
     * @param string                $path
     * @param CM_Site_Abstract|null $site
     * @return string
     */
    public function getUrlResource($type, $path, CM_Site_Abstract $site = null) {
        $deployVersion = CM_App::getInstance()->getDeployVersion();
        $environment = clone $this->getEnvironment();
        if (null !== $site) {
            $environment->setSite($site);
        }

        $cache = $this->_getCache();
        $cacheKey = $this->_getCacheUrlKey($path, $environment, [$type, $deployVersion]);
        return $cache->get($cacheKey, function () use ($path, $type, $environment, $deployVersion) {
            return (string) ResourceUrl::create($path, $type, $environment, $deployVersion);
        });
    }

    /**
     * @return string
     */
    public function getUrlServiceWorker() {
        $environment = $this->getEnvironment();
        $deployVersion = CM_App::getInstance()->getDeployVersion();

        $cache = $this->_getCache();
        $cacheKey = $this->_getCacheUrlKey('serviceworker', $environment, [$deployVersion]);
        return $cache->get($cacheKey, function () use ($environment, $deployVersion) {
            return (string) ServiceWorkerUrl::create($environment, $deployVersion);
        });
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
        $environment = $this->getEnvironment();
        $params = [
            'user'     => $mail->getRecipient()->getId(),
            'mailType' => $mail->getType(),
        ];

        $cache = $this->_getCache();
        $cacheKey = $this->_getCacheUrlKey('emailtracking', $environment, $params);
        return $cache->get($cacheKey, function () use ($params, $environment) {
            return (string) RouteUrl::create('emailtracking', $params, $environment);
        });
    }

    /**
     * @param string|null           $path
     * @param CM_Site_Abstract|null $site
     * @return string
     */
    public function getUrlStatic($path = null, CM_Site_Abstract $site = null) {
        $deployVersion = null;
        if (null !== $path) {
            $deployVersion = CM_App::getInstance()->getDeployVersion();
        }
        $environment = clone $this->getEnvironment();
        if (null !== $site) {
            $environment->setSite($site);
        }

        $cache = $this->_getCache();
        $cacheKey = $this->_getCacheUrlKey($path, $environment, [$deployVersion]);
        return $cache->get($cacheKey, function () use ($path, $environment, $deployVersion) {
            return (string) StaticUrl::create((string) $path, $environment, $deployVersion);
        });
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

        $cache = $this->_getCache();
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
     * @return CM_Cache_Local
     */
    protected function _getCache() {
        static $cache = null;
        if (null === $cache) {
            $cache = new CM_Cache_Local();
        }
        return $cache;
    }

    /**
     * @param string                       $url
     * @param CM_Frontend_Environment|null $environment
     * @param array|null                   $extra
     * @return string
     */
    protected function _getCacheUrlKey($url, CM_Frontend_Environment $environment = null, array $extra = null) {
        $cache = $this->_getCache();
        $parts = [$url];
        if (null !== $environment) {
            $parts[] = get_class($environment->getSite());
            if ($language = $environment->getLanguage()) {
                $parts[] = $language->getAbbreviation();
            }
        }
        $parts = array_merge($parts, (array) $extra);
        return call_user_func_array([$cache, 'key'], $parts);
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
