<?php

namespace CM\Url;

use Functional;
use CM_Util;
use CM_Params;
use Psr\Http\Message\UriInterface;
use GuzzleHttp\Psr7\UriResolver;
use CM\Url\Vendor\Uri;

class Url extends Uri {

    /** @var array|null */
    protected $_params = null;

    /** @var string|null */
    protected $_prefix = null;

    /**
     * @return bool
     */
    public function isRelative() {
        return '' === $this->getScheme() && '' === $this->getHost();
    }

    /**
     * @return string|null
     */
    public function getPrefix() {
        if (null === $this->_prefix) {
            return null;
        }
        return (string) $this->_prefix;
    }

    /**
     * @return array|null
     */
    public function getParams() {
        return $this->_params;
    }

    /**
     * @param string $path
     * @return bool
     */
    public function matchPath($path) {
        $path = (string) $path;
        return false !== strpos($this->getPath(), $path);
    }

    /**
     * @return bool
     */
    public function hasTrailingSlash() {
        return '/' === substr($this->getPath(), -1);
    }

    /**
     * @return static
     */
    public function withTrailingSlash() {
        $new = clone $this;
        if (!$new->hasTrailingSlash()) {
            $new->path .= '/';
        }
        return $new;
    }

    /**
     * @return static
     */
    public function withoutTrailingSlash() {
        $new = clone $this;
        if ($new->hasTrailingSlash()) {
            $new->path = rtrim($new->path, '/');
        }
        return $new;
    }

    public function withPath($path) {
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }
        $new = clone $this;
        $new->_setPath($path);
        return $new;
    }

    /**
     * @param $path
     * @return static
     */
    public function appendPath($path) {
        $path = $this->filterPath($this->getPath() . '/' . $path);
        $new = clone $this;
        $new->_setPath($path);
        return $new;
    }

    /**
     * @param $path
     * @return static
     */
    public function prependPath($path) {
        $path = $this->filterPath($path . '/' . $this->getPath());
        $new = clone $this;
        $new->_setPath($path);
        return $new;
    }

    /**
     * @param string $prefix
     * @return static
     */
    public function withPrefix($prefix) {
        if (null !== $prefix) {
            $prefix = trim($this->filterPath(UriResolver::removeDotSegments($prefix)), '/');
        }
        $prefix = '' !== (string) $prefix ? $prefix : null;
        $url = clone $this;
        $url->_prefix = $prefix;
        return $url;
    }

    /**
     * @return static
     */
    public function withoutPrefix() {
        $url = clone $this;
        $url->_prefix = null;
        return $url;
    }

    /**
     * @param array $params
     * @return static
     */
    public function withParams(array $params) {
        $this->_setParams($params);
        $params = CM_Params::encode($this->getParams());
        $query = http_build_query($params);
        /** @var Url $url */
        $url = parent::withQuery($query);
        return $url;
    }

    public function withQuery($query) {
        $this->_setParams($query);
        return parent::withQuery($query);
    }

    /**
     * @param $baseUrl
     * @return static
     */
    public function withBaseUrl($baseUrl) {
        if (!$baseUrl instanceof BaseUrl) {
            $baseUrl = BaseUrl::create((string) $baseUrl);
        }
        /** @var Url $url */
        $url = $this
            ->withHost($baseUrl->getHost())
            ->withScheme($baseUrl->getScheme());

        if ($prefix = $baseUrl->getPrefix()) {
            $url = $url->withPrefix($prefix);
        }
        return $url;
    }

    /**
     * @param string|UriInterface $uri
     * @return static
     */
    public function withRelativeComponentsFrom($uri) {
        if (!$uri instanceof UriInterface) {
            $uri = new Uri((string) $uri);
        }
        /** @var Url $url */
        $url = $this
            ->withPath($uri->getPath())
            ->withQuery($uri->getQuery())
            ->withFragment($uri->getFragment());
        return $url;
    }

    /**
     * @return static
     */
    public function withoutRelativeComponents() {
        $url = $this;
        $url->path = null;
        $url->query = null;
        $url->fragment = null;
        return $url;
    }

    /**
     * @return string
     */
    public function getUriBaseComponents() {
        $baseUrl = sprintf('%s://%s', $this->getScheme(), $this->getAuthority());
        if ($prefix = $this->getPrefix()) {
            $baseUrl = sprintf('%s/%s', $baseUrl, $prefix);
        }
        return $baseUrl;
    }

    /**
     * @return string
     */
    public function getUriRelativeComponents() {
        return $this->_getPathFromSegments() . $this->_getQueryComponent() . $this->_getFragmentComponent();
    }

    public function __toString() {
        return $this->getSchemeSpecificPart();
    }

    protected function filterPath($path) {
        $path = parent::filterPath($path);
        $segments = $this->_filterPathSegments(explode('/', $path));
        return implode('/', $segments) . ('/' === substr($path, -1) ? '/' : '');
    }

    /**
     * @return array
     */
    public function getPathSegments() {
        return $this->_filterPathSegments(explode('/', $this->path));
    }

    /**
     * @return array
     */
    public function getSegments() {
        $segments = [];
        if ($prefix = $this->getPrefix()) {
            $segments[] = $prefix;
        }
        return array_merge(
            $segments,
            $this->getPathSegments()
        );
    }

    protected function applyParts(array $parts) {
        parent::applyParts($parts);
        $this->_setPath($this->path);
        $this->_setParams($this->getQuery());
    }

    /**
     * @param string|array|null $query
     */
    protected function _setParams($query) {
        $params = $query;
        if (!is_array($params)) {
            $query = (string) $query;
            $params = [];
            parse_str($query, $params);
        }

        $paramsSanitized = null;
        if (0 !== count($params)) {
            $paramsSanitized = [];
            foreach ($params as $key => $value) {
                $key = CM_Util::sanitizeUtf($key);

                if (is_array($value)) {
                    array_walk_recursive($value, function (&$innerValue) {
                        if (is_string($innerValue)) {
                            $innerValue = CM_Util::sanitizeUtf($innerValue);
                        }
                    });
                } elseif (is_string($value)) {
                    $value = CM_Util::sanitizeUtf($value);
                }

                $paramsSanitized[$key] = $value;
            }
        }
        $this->_params = $paramsSanitized;
    }

    /**
     * @param string $path
     */
    protected function _setPath($path) {
        $this->path = UriResolver::removeDotSegments((string) $path);
        $this->_ensureAbsolutePath();
    }

    /**
     * @return string
     */
    protected function _getQueryComponent() {
        $query = (string) $this->query;
        return !empty($query) ? '?' . $query : $query;
    }

    /**
     * @return string
     */
    protected function _getFragmentComponent() {
        $fragment = (string) $this->fragment;
        return !empty($fragment) ? '#' . $fragment : $fragment;
    }

    /**
     * @return string
     */
    protected function _getPathFromSegments() {
        $segments = $this->getSegments();
        if (0 === count($segments) && '' === $this->path) {
            return '';
        }
        $segments = $this->_filterPathSegments($segments);
        $path = '/' . implode('/', $segments);
        if ($this->hasTrailingSlash() && '/' !== $path) {
            $path .= '/';
        }
        return $path;
    }

    /**
     * @param string $segment
     */
    protected function _dropPathSegment($segment) {
        $filteredSegments = Functional\reject($this->getPathSegments(), function ($value) use ($segment) {
            return $segment === $value;
        });
        $this->_setPath(implode('/', $filteredSegments));
    }

    /**
     * @param array|null $segments
     * @return array
     */
    protected function _filterPathSegments(array $segments = null) {
        return array_values(Functional\reject((array) $segments, function ($value) {
            return null === $value || '' === $value;
        }));
    }

    protected function _ensureAbsolutePath() {
        $path = $this->getPath();
        if ('' === $path || '/' !== mb_substr($path, 0, 1, 'UTF-8')) {
            $this->path = '/' . $path;
        }
    }

    /**
     * @return string
     */
    protected function getSchemeSpecificPart() {
        $scheme = $this->scheme;
        $authority = $this->getAuthority();
        if (!empty($authority)) {
            $authority = (!empty($scheme) ? $scheme . ':' : '') . '//' . $authority;
        }
        return $authority . $this->getUriRelativeComponents();
    }

    /**
     * @param string      $uri
     * @param array|null  $params
     * @param string|null $fragment
     * @return static
     */
    public static function createWithParams($uri, array $params = null, $fragment = null) {
        /** @var Url $url */
        $url = new static($uri);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        if (null !== $fragment) {
            $url = $url->withFragment($fragment);
        }
        return $url;
    }

    /**
     * @param array $parts
     * @return static
     */
    public static function createFromParts(array $parts) {
        $url = new static();
        $url->applyParts($parts);
        return $url;
    }

    /**
     * @param string $url
     * @return Url
     */
    public static function createFromString($url) {
        /** @var Url $url */
        $url = new static($url);
        return $url;
    }
}
