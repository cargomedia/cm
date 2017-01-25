<?php

namespace CM\Url;

use CM_Model_Language;
use League\Uri\Components\Query;

class RouteUrl extends AbstractUrl {

    /** @var array|null */
    protected $_params = null;

    /**
     * @return array|null
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * @param array $params
     * @return static
     */
    public function withParams(array $params) {
        $query = Query::createFromPairs($params);
        return $this->withQuery((string) $query);
    }

    /**
     * @param $queryString
     * @return RouteUrl
     */
    public function withQuery($queryString) {
        $query = new Query($queryString);
        $this->_params = $query->getPairs();
        /** @var RouteUrl $url */
        $url = parent::withQuery((string) $query);
        return $url;
    }

    protected function _getUriRelativeComponents() {
        $path = $this->path;
        if ($prefix = $this->getPrefix()) {
            $path = $path->prepend($prefix);
        }
        if ($language = $this->getLanguage()) {
            $path = $path->append($language->getAbbreviation());
        }
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    /**
     * @param string                 $route
     * @param array|null             $params
     * @param UrlInterface|null      $baseUrl
     * @param CM_Model_Language|null $language
     * @return RouteUrl
     */
    public static function create($route, array $params = null, UrlInterface $baseUrl = null, CM_Model_Language $language = null) {
        /** @var RouteUrl $url */
        $url = parent::_create($route, $baseUrl, $language);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        return $url;
    }
}
