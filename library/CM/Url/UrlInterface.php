<?php

namespace CM\Url;

use CM_Site_Abstract;
use CM_Model_Language;
use CM_Frontend_Environment;
use Psr\Http\Message\UriInterface;

interface UrlInterface extends UriInterface {

    /**
     * @return bool
     */
    public function isAbsolute();

    /**
     * @return CM_Model_Language|null
     */
    public function getLanguage();

    /**
     * @return CM_Site_Abstract|null
     */
    public function getSite();

    /**
     * @return string|null
     */
    public function getPrefix();

    /**
     * @return array|null
     */
    public function getParams();

    /**
     * @return string
     */
    public function getUriBaseComponents();

    /**
     * @return string
     */
    public function getUriRelativeComponents();

    /**
     * @param CM_Model_Language $language
     * @return UrlInterface
     */
    public function withLanguage(CM_Model_Language $language);

    /**
     * @param CM_Site_Abstract $site
     * @return UrlInterface
     */
    public function withSite(CM_Site_Abstract $site);

    /**
     * @param string|null $prefix
     * @return UrlInterface
     */
    public function withPrefix($prefix);

    /**
     * @return UrlInterface
     */
    public function withoutPrefix();

    /**
     * @param array $params
     * @return UrlInterface
     */
    public function withParams(array $params);

    /**
     * @param UrlInterface|string $baseUrl
     * @return UrlInterface
     */
    public function withBaseUrl($baseUrl);

    /**
     * @param UrlInterface|string $url
     * @return UrlInterface
     */
    public function withRelativeComponentsFrom($url);

    /**
     * @return UrlInterface
     */
    public function withoutRelativeComponents();

    /**
     * @param CM_Frontend_Environment $environment
     * @return UrlInterface
     */
    public function withEnvironment(CM_Frontend_Environment $environment);

    /**
     * @param string $path
     * @return UrlInterface
     */
    public function withPath($path);

    /**
     * @param string $query
     * @return UrlInterface
     */
    public function withQuery($query);

    /**
     * @param string $fragment
     * @return UrlInterface
     */
    public function withFragment($fragment);
}
