<?php

class CM_Asset_Css extends CM_Asset_Abstract {

    /** @var CM_Frontend_Render */
    protected $_render;

    /** @var string|null */
    protected $_content;

    /** @var string|null */
    private $_prefix;

    /** @var CM_Asset_Css[] */
    private $_children = array();

    /** @var string|null */
    private $_autoprefixerBrowsers = null;

    /**
     * @param CM_Frontend_Render $render
     * @param string|null        $content
     * @param array|null         $options
     */
    public function __construct(CM_Frontend_Render $render, $content = null, array $options = null) {
        $this->_render = $render;
        if (null !== $content) {
            $this->_content = (string) $content;
        }
        $options = (array) $options;
        if (isset($options['prefix'])) {
            $this->_prefix = (string) $options['prefix'];
        }
        if (isset($options['autoprefixerBrowsers'])) {
            $this->_autoprefixerBrowsers = (string) $options['autoprefixerBrowsers'];
        }
    }

    /**
     * @param string      $content
     * @param string|null $prefix
     */
    public function add($content, $prefix = null) {
        $this->_children[] = new self($this->_render, $content, ['prefix' => $prefix]);
    }

    public function get($compress = null) {
        $content = $this->_getContent();
        return $this->_compile($content, $compress);
    }

    protected function _getContent() {
        $content = '';
        if ($this->_prefix) {
            $content .= $this->_prefix . ' {' . PHP_EOL;
        }
        if ($this->_content) {
            $content .= $this->_content . PHP_EOL;
        }
        foreach ($this->_children as $css) {
            $content .= $css->_getContent();
        }
        if ($this->_prefix) {
            $content .= '}' . PHP_EOL;
        }
        return $content;
    }

    /**
     * @param string       $content
     * @param boolean|null $compress
     * @return string
     */
    private function _compile($content, $compress = null) {
        $content = (string) $content;
        $compress = (bool) $compress;
        $render = $this->_render;

        $cacheKey = CM_CacheConst::App_Resource . '_md5:' . md5($content);
        $cacheKey .= '_compress:' . (int) $compress;
        $cacheKey .= '_siteId:' . $render->getSite()->getId();
        if ($render->getLanguage()) {
            $cacheKey .= '_languageId:' . $render->getLanguage()->getId();
        }
        $cache = new CM_Cache_Storage_File();
        if (false === ($contentTransformed = $cache->get($cacheKey))) {
            $contentTransformed = $content;
            $contentTransformed = $this->_compileLess($contentTransformed, $compress);
            $contentTransformed = $this->_compileAutoprefixer($contentTransformed);
            $contentTransformed = trim($contentTransformed);
            $cache->set($cacheKey, $contentTransformed);
        }
        return $contentTransformed;
    }

    /**
     * @param string $content
     * @param bool   $compress
     * @return string
     */
    private function _compileLess($content, $compress) {
        $render = $this->_render;

        $lessCompiler = new lessc();
        $lessCompiler->registerFunction('image', function ($arg) use ($render) {
            /** @var CM_Frontend_Render $render */
            list($type, $delimiter, $values) = $arg;
            return array('function', 'url', array('string', $delimiter, array($render->getUrlResource('layout', 'img/' . $values[0]))));
        });
        $lessCompiler->registerFunction('urlFont', function ($arg) use ($render) {
            /** @var CM_Frontend_Render $render */
            list($type, $delimiter, $values) = $arg;
            return array($type, $delimiter, array($render->getUrlStatic('/font/' . $values[0])));
        });
        if ($compress) {
            $lessCompiler->setFormatter('compressed');
        }
        return $lessCompiler->compile($content);
    }

    /**
     * @param string $content
     * @return string
     */
    private function _compileAutoprefixer($content) {
        $command = 'autoprefixer';
        $args = [];
        if (null !== $this->_autoprefixerBrowsers) {
            $args[] = '--browsers';
            $args[] = $this->_autoprefixerBrowsers;
        }
        return CM_Util::exec($command, null, $content);
    }
}
