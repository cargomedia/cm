<?php

class CM_StreamChannel_Thumbnail extends CM_Model_Abstract {

    /**
     * @return int
     */
    public function getChannelId() {
        return $this->_get('channelId');
    }

    /**
     * @return int
     */
    public function getCreateStamp() {
        return $this->_get('createStamp');
    }

    /**
     * @return CM_File_UserContent
     */
    public function getFile() {
        return new CM_File_UserContent('streamChannels', $this->getChannelId() . '-thumbs' . DIRECTORY_SEPARATOR . $this->getCreateStamp() . '-' .
            $this->getHash() . '-' . $this->getId() . '.png', $this->getChannelId());
    }

    /**
     * @return string
     */
    public function getHash() {
        return md5($this->getChannelId() . '-' . $this->getId());
    }

    protected function _getSchema() {
        return new CM_Model_Schema_Definition([
            'channelId'   => 'int',
            'createStamp' => 'int',
        ]);
    }

    protected function _getContainingCacheables() {
        return [new CM_StreamChannel_ThumbnailList_Channel($this->getChannelId())];
    }

    /**
     * @param int $channelId
     * @param int $createStamp
     * @return self
     */
    public static function create($channelId, $createStamp) {
        $thumbnail = new self();
        $thumbnail->_set([
            'channelId'   => $channelId,
            'createStamp' => $createStamp,
        ]);
        $thumbnail->commit();
        return $thumbnail;
    }

    public static function getPersistenceClass() {
        return 'CM_Model_StorageAdapter_Database';
    }
}
