<?php

namespace CM\Url;

use CM_Exception_Invalid;
use CM_Util;
use CM_Model_Language;
use League\Uri\Components\HierarchicalPath;

class PageUrl extends RouteUrl {

    public function getUriRelativeComponents() {
        $segments = [];
        if ($prefix = $this->getPrefix()) {
            $segments[] = $prefix;
        }
        if ($language = $this->getLanguage()) {
            $segments[] = $language->getAbbreviation();
        }
        $path = $this->path->prepend(
            HierarchicalPath::createFromSegments($segments, HierarchicalPath::IS_ABSOLUTE)
        );
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    /**
     * @param string                 $pageClassName
     * @param array|null             $params
     * @param UrlInterface|null      $baseUrl
     * @param CM_Model_Language|null $language
     * @return PageUrl
     */
    public static function create($pageClassName, array $params = null, UrlInterface $baseUrl = null, CM_Model_Language $language = null) {
        /** @var PageUrl $url */
        $url = parent::_create(self::_pageClassNameToPath($pageClassName), $baseUrl, $language);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        return $url;
    }

    /**
     * @param string $pageClassName
     * @return string
     * @throws CM_Exception_Invalid
     */
    protected static function _pageClassNameToPath($pageClassName) {
        if (!class_exists($pageClassName)) {
            throw new CM_Exception_Invalid('Failed to create PageUrl, page class does not exist', null, [
                'pageClassName' => $pageClassName,
            ]);
        }

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
        return $path;
    }
}
