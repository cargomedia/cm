<?php

class CM_Paging_Emoticon_All extends CM_Paging_Abstract {

    public function __construct() {
        $source = new CM_PagingSource_Sql('id, code, codeAdditional, file', 'cm_emoticon', null, '`id`');
        $source->enableCacheLocal();
        parent::__construct($source);
    }

    protected function _processItem($itemRaw) {
        $item = array();
        $item['id'] = (int) $itemRaw['id'];
        $item['code'] = (string) $itemRaw['code'];
        $item['codes'] = array_merge(
            array($itemRaw['code']),
            $this->_decodeCodeAdditional($itemRaw['codeAdditional'])
        );
        $item['file'] = $itemRaw['file'];
        return $item;
    }

    /**
     * @param string     $code
     * @param string     $file
     * @param array|null $aliases
     */
    public function add($code, $file, array $aliases = null) {
        $aliases = (array) $aliases;
        CM_Db_Db::insertIgnore('cm_emoticon', array('code' => $code, 'file' => $file, 'codeAdditional' => $this->_encodeCodeAdditional($aliases)));
        $this->_change();
    }

    /**
     * @param string   $code
     * @param string[] $aliases
     * @throws CM_Exception_Invalid
     */
    public function setAliases($code, array $aliases) {
        CM_Db_Db::update('cm_emoticon', array('codeAdditional' => $this->_encodeCodeAdditional($aliases)), array('code' => $code));
        $this->_change();
    }

    /**
     * @param string $code
     * @param string $alias
     */
    public function addAlias($code, $alias) {
        $alias = (string) $alias;
        $aliases = array_merge($this->getAliases($code), array($alias));
        $this->setAliases($code, $aliases);
    }

    /**
     * @param string $code
     * @return array
     */
    public function getAliases($code) {
        foreach ($this->getItemsRaw() as $itemRaw) {
            if ($itemRaw['code'] === $code) {
                return $this->_decodeCodeAdditional($itemRaw['codeAdditional']);
            }
        }
        return array();
    }

    /**
     * @param string|null $codeAdditional
     * @return array
     */
    protected function _decodeCodeAdditional($codeAdditional) {
        if (null === $codeAdditional) {
            return array();
        }
        return CM_Params::decode($codeAdditional, true);
    }

    /**
     * @param array $aliases
     * @return null|string
     */
    protected function _encodeCodeAdditional(array $aliases) {
        if (!count($aliases)) {
            return null;
        }
        return CM_Params::encode($aliases, true);
    }
}
