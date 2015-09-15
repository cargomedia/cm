<?php

class CM_Usertext_Usertext extends CM_Class_Abstract {

    /** @var CM_Frontend_Render */
    private $_render;

    /**
     * @param CM_Frontend_Render $render
     */
    function __construct(CM_Frontend_Render $render = null) {
        $this->_render = $render;
    }

    /** @var CM_Usertext_Filter_Interface[] */
    private $_filterList = array();

    /**
     * @param CM_Usertext_Filter_Interface $filter
     */
    public function addFilter(CM_Usertext_Filter_Interface $filter) {
        $this->_filterList[] = $filter;
    }

    /**
     * @param string                       $filterName
     * @param CM_Usertext_Filter_Interface $filter
     */
    public function addFilterAfter($filterName, CM_Usertext_Filter_Interface $filter) {
        $filterName = (string) $filterName;
        $filterPosition = null;
        foreach ($this->_getFilters() as $index => $filterExisting) {
            if (get_class($filterExisting) === $filterName) {
                $filterPosition = $index + 1;
                break;
            }
        }
        if (null !== $filterPosition) {
            array_splice($this->_filterList, $filterPosition, 0, [$filter]);
        }
    }

    /**
     * @return CM_Frontend_Render
     * @throws CM_Exception_Invalid
     */
    public function getRender() {
        if (!$this->_render) {
            throw new CM_Exception_Invalid('Render not set');
        }
        return $this->_render;
    }

    /**
     * @param string $mode
     * @param array  $options
     * @throws CM_Exception_Invalid
     */
    public function setMode($mode, $options = null) {
        $options = $options ? $options : [];
        $acceptedModes = array('escape', 'oneline', 'simple', 'markdown', 'markdownPlain');
        if (!in_array($mode, $acceptedModes)) {
            throw new CM_Exception_Invalid('Invalid mode `' . $mode . '`');
        }
        $mode = (string) $mode;
        $optionsDefault = [
            'maxLength'           => null,
            'skipAnchors'         => null,
            'emoticonFixedHeight' => null,
            'allowBadwords'       => false
        ];

        $options = $options + $optionsDefault;
        $this->_clearFilters();
        $emoticonFixedHeight = null;
        $this->_setMode($mode, $options);
    }

    /**
     * @param string $text
     * @return string
     */
    public function transform($text) {
        $cacheKey = $this->_getCacheKey($text);
        $cache = CM_Cache_Local::getInstance();
        $render = $this->getRender();
        if (($result = $cache->get($cacheKey)) === false) {
            $result = $text;
            foreach ($this->_getFilters() as $filter) {
                $result = $filter->transform($result, $render);
            }
            $cache->set($cacheKey, $result);
        }
        return $result;
    }

    /**
     * @param string $text
     * @return string
     */
    protected function _getCacheKey($text) {
        $cacheKey = CM_CacheConst::Usertext . '_text:' . md5($text);
        $filterList = $this->_getFilters();
        if (0 !== count($filterList)) {
            $cacheKeyListFilter = array_map(function (CM_Usertext_Filter_Interface $filter) {
                return $filter->getCacheKey();
            }, $filterList);
            $cache = CM_Cache_Local::getInstance();
            $cacheKey .= '_filter:' . $cache->key($cacheKeyListFilter);
        }
        return $cacheKey;
    }

    /**
     * @param string $mode
     * @param array  $options
     * @throws CM_Exception_Invalid
     */
    protected function _setMode($mode, array $options = []) {
        $maxLength = $options['maxLength'];
        $skipAnchors = $options['skipAnchors'];
        $emoticonFixedHeight = $options['emoticonFixedHeight'];
        $allowBadwords = $options['allowBadwords'];
        if (!$allowBadwords) {
            $this->addFilter(new CM_Usertext_Filter_Badwords());
        }
        if ('escape' != $mode) {
            $this->addFilter(new CM_Usertext_Filter_Emoticon_ReplaceAdditional());
        }
        $this->addFilter(new CM_Usertext_Filter_Escape());
        switch ($mode) {
            case 'escape':
                break;
            case 'oneline':
                $this->addFilter(new CM_Usertext_Filter_MaxLength($maxLength));
                $this->addFilter(new CM_Usertext_Filter_Emoticon($emoticonFixedHeight));
                break;
            case 'simple':
                $this->addFilter(new CM_Usertext_Filter_MaxLength($maxLength));
                $this->addFilter(new CM_Usertext_Filter_NewlineToLinebreak(3));
                $this->addFilter(new CM_Usertext_Filter_Emoticon($emoticonFixedHeight));
                break;
            case 'markdown':
                if (null !== $maxLength) {
                    throw new CM_Exception_Invalid('MaxLength is not allowed in mode markdown.');
                }
                $this->addFilter(new CM_Usertext_Filter_Emoticon_EscapeMarkdown());
                $this->addFilter(new CM_Usertext_Filter_Markdown_UnescapeBlockquote());
                $this->addFilter(new CM_Usertext_Filter_Markdown($skipAnchors));
                $this->addFilter(new CM_Usertext_Filter_Emoticon_UnescapeMarkdown());
                $this->addFilter(new CM_Usertext_Filter_Emoticon($emoticonFixedHeight));
                break;
            case 'markdownPlain':
                $this->addFilter(new CM_Usertext_Filter_Emoticon_EscapeMarkdown());
                $this->addFilter(new CM_Usertext_Filter_Markdown($skipAnchors));
                $this->addFilter(new CM_Usertext_Filter_Emoticon_UnescapeMarkdown());
                $this->addFilter(new CM_Usertext_Filter_Striptags());
                $this->addFilter(new CM_Usertext_Filter_MaxLength($maxLength));
                $this->addFilter(new CM_Usertext_Filter_Emoticon($emoticonFixedHeight));
                break;
        }

        if ('markdownPlain' != $mode) {
            $this->addFilter(new CM_Usertext_Filter_CutWhitespace());
        }
    }

    private function _clearFilters() {
        $this->_filterList = array();
    }

    /**
     * @return CM_Usertext_Filter_Interface[]
     */
    private function _getFilters() {
        return $this->_filterList;
    }

    /**
     * @param CM_Frontend_Render $render
     * @return CM_Usertext_Usertext
     */
    public static function factory(CM_Frontend_Render $render) {
        $className = self::_getClassName();
        return new $className($render);
    }
}
