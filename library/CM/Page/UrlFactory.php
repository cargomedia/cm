<?php

use CM\Url\Url;
use League\Uri\Components\Query;

class CM_Page_UrlFactory {

    /**
     * @param string                $pageClassName
     * @param array|null            $params
     * @param CM_Site_Abstract|null $site
     * @return \CM\Url\UrlInterface
     */
    public static function getUrl($pageClassName, array $params = null, CM_Site_Abstract $site = null) {
        if ($pageClassName instanceof CM_Page_Abstract) {
            $pageClassName = get_class($pageClassName);
        }
        self::_assertPage($pageClassName);
        $url = Url::createFromComponents(self::_getUrlComponents($pageClassName, $params));
        if ($site) {
            self::_assertSupportedSite($pageClassName, $site);
            $url = $url->withSite($site);
        }
        return $url;
    }

    /**
     * @param string     $pageClassName
     * @param array|null $params
     * @return array
     */
    protected static function _getUrlComponents($pageClassName, array $params = null) {
        self::_assertPage($pageClassName);
        $pageComponents = $pageClassName::getUrlComponents($params);
        if (0 === count($pageComponents)) {
            $params = CM_Params::encode((array) $params);
            $pageComponents = [
                'path'  => self::_getPathDefault($pageClassName),
                'query' => count($params) > 0 ? (string) Query::createFromPairs($params) : null,
            ];
        }
        return $pageComponents;
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

    /**
     * @param string $pageClassName
     * @throws CM_Exception_Invalid
     */
    protected static function _assertPage($pageClassName) {
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
    protected static function _assertSupportedSite($pageClassName, CM_Site_Abstract $site) {
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
}
