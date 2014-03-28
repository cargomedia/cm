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
        $item['codes'] = array($itemRaw['code']);
        if ($itemRaw['codeAdditional']) {
            $item['codes'] = array_merge($item['codes'], explode(',', $itemRaw['codeAdditional']));
        }
        $item['file'] = $itemRaw['file'];
        return $item;
    }

    /**
     * @param string $code
     * @param string $file
     */
    public function add($code, $file) {
        CM_Db_Db::insertIgnore('cm_emoticon', array('code' => $code, 'file' => $file));
        $this->_change();
    }

    /**
     * @param string   $code
     * @param string[] $aliases
     * @throws CM_Exception_Invalid
     */
    public function setAliases($code, array $aliases) {
        CM_Db_Db::update('cm_emoticon', array('codeAdditional' => join(',', $aliases)), array('code' => $code));
        $this->_change();
    }

    /**
     * @param string $code
     * @param string $alias
     */
    public function addAlias($code, $alias) {
        $alias = (string) $alias;
        $aliases = array_merge($this->_getAliases($code), array($alias));
        $this->setAliases($code, $aliases);
    }

    /**
     * @param string $code
     * @return array
     */
    protected function _getAliases($code) {
        foreach ($this->getItemsRaw() as $itemRaw) {
            if ($itemRaw['code'] === $code) {
                return explode(',', $itemRaw['codeAdditional']);
            }
        }
    }
}
