<?php

namespace CM\Url;

use CM_Frontend_Environment;
use CM_Page_Abstract;
use CM_Site_Abstract;
use CM\Url\Components\PagePath;
use League\Uri\Components\HierarchicalPath as Path;
use League\Uri\Components\Fragment;
use League\Uri\Components\Host;
use League\Uri\Components\Pass;
use League\Uri\Components\Port;
use League\Uri\Components\Query;
use League\Uri\Components\Scheme;
use League\Uri\Components\User;
use League\Uri\Components\UserInfo;
use League\Uri\Interfaces\Fragment as FragmentInterface;
use League\Uri\Interfaces\Host as HostInterface;
use League\Uri\Interfaces\Port as PortInterface;
use League\Uri\Interfaces\Query as QueryInterface;
use League\Uri\Interfaces\Scheme as SchemeInterface;
use League\Uri\Interfaces\UserInfo as UserInfoInterface;

class PageUrl extends RouteUrl {

    /** @var PagePath */
    protected $path;

    /**
     * @param SchemeInterface   $scheme
     * @param UserInfoInterface $userInfo
     * @param HostInterface     $host
     * @param PortInterface     $port
     * @param PagePath          $path
     * @param QueryInterface    $query
     * @param FragmentInterface $fragment
     */
    public function __construct(
        SchemeInterface $scheme,
        UserInfoInterface $userInfo,
        HostInterface $host,
        PortInterface $port,
        PagePath $path,
        QueryInterface $query,
        FragmentInterface $fragment
    ) {
        parent::__construct($scheme, $userInfo, $host, $port, $path, $query, $fragment);
    }

    /**
     * @return string
     */
    public function getPageClassName() {
        return $this->path->getPageClassName();
    }

    public function withSite(CM_Site_Abstract $site, $sameOrigin = null) {
        $this->path->assertSupportedSite($site);
        return parent::withSite($site, $sameOrigin);
    }

    public function getUriRelativeComponents() {
        $segments = [];
        if ($prefix = $this->getPrefix()) {
            $segments[] = $prefix;
        }
        if ($language = $this->getLanguage()) {
            $segments[] = $language->getAbbreviation();
        }
        $path = new Path((string) $this->path);
        $path = $path->prepend(
            Path::createFromSegments($segments, Path::IS_ABSOLUTE)
        );
        return ''
            . $path->getUriComponent()
            . $this->query->getUriComponent()
            . $this->fragment->getUriComponent();
    }

    /**
     * @param CM_Page_Abstract|string      $pageClassName
     * @param array|null                   $params
     * @param CM_Frontend_Environment|null $environment
     * @return PageUrl
     */
    public static function create($pageClassName, array $params = null, CM_Frontend_Environment $environment = null) {
        if ($pageClassName instanceof CM_Page_Abstract) {
            $pageClassName = get_class($pageClassName);
        }
        $pageClassName = (string) $pageClassName;

        /** @var PageUrl $url */
        $url = parent::_create($pageClassName, $environment);
        if (null !== $params) {
            $url = $url->withParams($params);
        }
        return $url;
    }

    public static function createFromComponents(array $components) {
        $components = self::normalizeUriHash($components);
        return new static(
            new Scheme($components['scheme']),
            new UserInfo(new User($components['user']), new Pass($components['pass'])),
            new Host($components['host']),
            new Port($components['port']),
            new PagePath($components['path']),
            new Query($components['query']),
            new Fragment($components['fragment'])
        );
    }
}
