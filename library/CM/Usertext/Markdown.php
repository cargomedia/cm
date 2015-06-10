<?php

class CM_Usertext_Markdown extends Michelf\MarkdownExtra {

    protected $id_class_attr_catch_re = '\{((?:[ ]*[#.][-_:a-zA-Z0-9]+){0,2}(?:[ ]*\$[\d]+x[\d]+))[ ]*\}';
    protected $id_class_attr_nocatch_re = '\{(?:[ ]*[#.][-_:a-zA-Z0-9]+){0,2}(?:[ ]*\$[\d]+x[\d]+)[ ]*\}';

    /** @var bool $_skipAnchors */
    private $_skipAnchors;

    /**
     * @param bool|null $skipAnchors
     */
    public function __construct($skipAnchors = null) {
        $this->_skipAnchors = (boolean) $skipAnchors;
        parent::__construct();
    }

    protected function formParagraphs($text) {
        $text = preg_replace('/\A\n+|\n+\z/', '', $text);
        $grafs = preg_split('/\n{1,}/', $text, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($grafs as $key => $value) {
            if (!preg_match('/^B\x1A[0-9]+B$/', $value)) {
                # Is a paragraph.
                $value = $this->runSpanGamut($value);
                $value = preg_replace('/^([ ]*)/', "<p>", $value);
                $value .= "</p>";
                $grafs[$key] = $this->unhash($value);
            } else {
                # Is a block.
                # Modify elements of @grafs in-place...
                $graf = $value;
                $block = $this->html_hashes[$graf];
                $graf = $block;
                $grafs[$key] = $graf;
            }
        }
        return implode("\n", $grafs);
    }

    protected function _doAnchors_inline_callback($matches) {
        if (!$this->_skipAnchors) {
            return parent::_doAnchors_inline_callback($matches);
        }
        $link_text = $this->runSpanGamut($matches[2]);
        return $this->hashPart($link_text);
    }

    protected function _doAnchors_reference_callback($matches) {
        if (!$this->_skipAnchors) {
            return parent::_doAnchors_inline_callback($matches);
        }
        $link_text = $matches[2];
        return $link_text;
    }

    protected function _doImages_reference_callback($matches) {
        $key = parent::_doImages_reference_callback($matches);
        $this->html_hashes[$key] = $this->_transformToLazyImages($this->html_hashes[$key]);
        return $key;
    }

    protected function doExtraAttributes($tag_name, $attr) {
        $extraAttrs = parent::doExtraAttributes($tag_name, $attr);
        if ('img' == $tag_name) {
            preg_match_all('/\$(\d+)x(\d+)/', $attr, $matches);
            if ($matches[1] && $matches[2]) {
                $extraAttrs .= ' width="' . $matches[1][0] . '" height="' . $matches[2][0] . '"';
            }
        }
        return $extraAttrs;
    }

    protected function _doImages_inline_callback($matches) {
        $key = parent::_doImages_inline_callback($matches);
        $this->html_hashes[$key] = $this->_transformToLazyImages($this->html_hashes[$key]);
        return $key;
    }

    /**
     * @param string $tagText
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _transformToLazyImages($tagText) {
        $tagText = preg_replace_callback('/^<img src="([^"]+)" alt="([^"]+)" title="(?:[^"]+)?"(?: class="([^"]+)")? (?:width="(\d+)") (?:height="(\d+)") \/>$/i', function ($matches) {
            $width = (int) $matches[4];
            $height = (int) $matches[5];
            if ($width > 0 && $height > 0) {
                $src = $matches[1];
                $alt = $matches[2];
                $class = $matches[3] ? 'lazy ' . $matches[3] : 'lazy';
                $imgHtml = '<img data-src="' . $src . '" alt="' . $alt . '" class="' . $class . '"/>';
                return CM_Frontend_TemplateHelper_ContentPlaceholder::create($imgHtml, $width, $height) . '<br/>';
            }
            return $matches[0];
        }, $tagText);
        return $tagText;
    }
}
