<?php

class CM_Model_Example_Todo extends CM_Model_Bound {

    /**
     * @return string
     */
    public function getTitle() {
        return $this->_get('title');
    }

    /**
     * @param string $title
     */
    public function setTitle($title) {
        $this->_set('title', $title);
    }

    /**
     * @return string
     */
    public function getDescription() {
        return $this->_get('description');
    }

    /**
     * @param string $description
     */
    public function setDescription($description) {
        $this->_set('description', $description);
    }

    /**
     * @return int
     */
    public function getState() {
        return $this->_get('state');
    }

    /**
     * @param int $state
     */
    public function setState($state) {
        $this->_set('state', $state);
    }

    public function toArray() {
        $array = parent::toArray();
        $array['title'] = $this->getTitle();
        $array['description'] = $this->getDescription();
        $array['state'] = $this->getState();
        return $array;
    }

    protected function _loadData() {
        return CM_Db_Db::exec('SELECT * FROM `cm_model_example_todo` WHERE `id`=?', array($this->getId()))->fetch();
    }

    public static function getCacheClass() {
        return 'CM_Model_StorageAdapter_CacheLocal';
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'title'       => ['type' => 'string'],
            'description' => ['type' => 'string'],
            'state'       => ['type' => 'int'],
        ]);
    }
}
