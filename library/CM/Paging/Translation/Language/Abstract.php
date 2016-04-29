<?php

abstract class CM_Paging_Translation_Language_Abstract extends CM_Paging_Abstract {

    /** @var CM_Model_Language */
    protected $_language;

    /**
     * @return string[]
     */
    public function getAssociativeArray() {
        $translations = array();
        foreach ($this as $translation) {
            $key = $translation['key'];
            unset($translation['key']);
            $translations[$key] = $translation;
        }
        return $translations;
    }

    /**
     * @param string $phrase
     * @throws CM_Exception_Invalid
     * @return string
     */
    public function get($phrase) {
        $translations = $this->getAssociativeArray();
        if (!array_key_exists($phrase, $translations)) {
            throw new CM_Exception_Invalid('Translation `' . $phrase . '` does not exist');
        }
        return $translations[$phrase]['value'];
    }

    protected function _processItem($item) {
        $item['variables'] = ($item['variables']) ? json_decode($item['variables']) : array();
        sort($item['variables']);
        return $item;
    }
}
