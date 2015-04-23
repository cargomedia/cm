<?php

class CM_Usertext_Markdown extends Michelf\MarkdownExtra {

    /** @var bool $_skipAnchors */
    private $_skipAnchors;

    /** @var bool $_imgLazy */
    private $_imgLazy;

    /**
     * @param bool|null $skipAnchors
     * @param bool|null $imgLazy
     */
    public function __construct($skipAnchors = null, $imgLazy = null) {
        $this->_skipAnchors = (boolean) $skipAnchors;
        $this->_imgLazy = (boolean) $imgLazy;
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
        if ($this->_imgLazy) {
            $this->html_hashes[$key] = $this->_addLazyAttrs($this->html_hashes[$key]);
        } else {
            $this->html_hashes[$key] = $this->_deleteHiddenDimensionAttrs($this->html_hashes[$key]);
        }
        return $key;
    }

    protected function _doImages_inline_callback($matches) {
        $key = parent::_doImages_inline_callback($matches);
        if ($this->_imgLazy) {
            $this->html_hashes[$key] = $this->_addLazyAttrs($this->html_hashes[$key]);
        } else {
            $this->html_hashes[$key] = $this->_deleteHiddenDimensionAttrs($this->html_hashes[$key]);
        }
        return $key;
    }

    /**
     * @param string $tagText
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _addLazyAttrs($tagText) {
        $tagText = preg_replace_callback('/^<img src="([^"]+)" alt="(.+?)" id="img(\d+)-(\d+)" \/>$/i', function ($matches) {
            $src = $matches[1];
            $alt = $matches[2];
            $width = (int) $matches[3];
            $height = (int) $matches[4];
            $ratio = (($height / $width) * 100) % 100;
            $imgHtml = '<div class="embeddedWrapper" width="' . $width . '" style="padding-bottom:' . $ratio . '%;">';
            $imgHtml .= '<img data-src="' . $src . '" alt="' . $alt . '" class="lazy embeddedWrapper-object"/>';
            $imgHtml .= '</div>';
            return $imgHtml;
        }, $tagText, -1, $count);
        if (0 === $count) {
            throw new CM_Exception_Invalid('Cannot replace img-tag `' . $tagText . '`.');
        }
        return $tagText;
    }

    /**
     * @param string $tagText
     * @return string
     * @throws CM_Exception_Invalid
     */
    private function _deleteHiddenDimensionAttrs($tagText) {
        return preg_replace('#id="img\d+-\d+"#im', '', $tagText, -1);
    }
}
