<?php

use CM\Url\PageUrl;

class CM_Page_UrlFactory {

    /**
     * @param string                       $pageClassName
     * @param array|null                   $params
     * @param CM_Frontend_Environment|null $environment
     * @return PageUrl
     */
    public static function getUrl($pageClassName, array $params = null, CM_Frontend_Environment $environment = null) {
        self::assertPage($pageClassName);
        $url = PageUrl::createFromParts(self::_getParts($pageClassName, $params));
        if ($environment) {
            self::assertSupportedSite($pageClassName, $environment->getSite());
            $url = $url->withEnvironment($environment);
        }
        return $url;
    }

    /**
     * @param string     $pageClassName
     * @param array|null $params
     * @return array
     */
    protected static function _getParts($pageClassName, array $params = null) {
        /** @var CM_Page_Abstract $pageClassName */
        $pageParts = $pageClassName::getUrlParts($params);
        if (null === $pageParts) {
            $pageParts = [
                'path' => self::_getPathDefault($pageClassName),
            ];
            if (null !== $params && count($params) > 0) {
                $url = PageUrl::createWithParams('', $params);
                $pageParts['query'] = $url->getQuery();
            }
        }
        return $pageParts;
    }

    /**
     * @param string $pageClassName
     * @throws CM_Exception_Invalid
     */
    public static function assertPage($pageClassName) {
        if (!class_exists($pageClassName) || !is_subclass_of($pageClassName, 'CM_Page_Abstract')) {
            throw new CM_Exception_Invalid('Cannot find valid class definition for page class name', null, [
                'pageClassName' => $pageClassName,
            ]);
        }
    }

    /**
     * @param string           $pageClassName
     * @param CM_Site_Abstract $site
     * @throws CM_Exception_Invalid
     */
    public static function assertSupportedSite($pageClassName, CM_Site_Abstract $site) {
        if (!preg_match('/^([A-Za-z]+)_/', $pageClassName, $matches)) {
            throw new CM_Exception_Invalid('Cannot find namespace of page class name', null, [
                'pageClassName' => $pageClassName,
            ]);
        }
        $namespace = $matches[1];
        if (!in_array($namespace, $site->getModules())) {
            throw new CM_Exception_Invalid('Site does not contain namespace', null, [
                'site'      => get_class($site),
                'namespace' => $namespace,
            ]);
        }
    }

    /**
     * @param string $pageClassName
     * @return string
     */
    protected static function _getPathDefault($pageClassName) {
        $list = explode('_', $pageClassName);
        foreach ($list as $index => $entry) {
            unset($list[$index]);
            if ($entry == 'Page') {
                break;
            }
        }
        foreach ($list as $index => $entry) {
            $list[$index] = CM_Util::uncamelize($entry);
        }
        $path = '/' . implode('/', $list);
        if ($path == '/index') {
            $path = '/';
        }
        return $path;
    }
}
