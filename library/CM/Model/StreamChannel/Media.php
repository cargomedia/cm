<?php

class CM_Model_StreamChannel_Media extends CM_Model_StreamChannel_Abstract {

    public function onPublish(CM_Model_Stream_Publish $streamPublish) {
    }

    public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
        if (!CM_Model_StreamChannelArchive_Media::findById($this->getId())) {
            CM_Model_StreamChannelArchive_Media::createStatic(array('streamChannel' => $this));
        }
    }

    public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    /**
     * @param int $thumbnailCount
     */
    public function setThumbnailCount($thumbnailCount) {
        $thumbnailCount = (int) $thumbnailCount;
        $params = $this->_getDataColumn();
        $params->set('thumbnailCount', $thumbnailCount);
        CM_Db_Db::update('cm_streamChannel_media', ['data' => CM_Params::jsonEncode($params->getParamsEncoded())], ['id' => $this->getId()]);
        $this->_change();
    }

    /**
     * @return string
     */
    public function getHash() {
        if ($this->hasStreamPublish()) {
            return md5($this->getStreamPublish()->getKey());
        }
        return md5($this->getKey());
    }

    /**
     * @return string|null
     */
    public function getMediaId() {
        $mediaId = $this->_get('mediaId');
        $mediaId = (null !== $mediaId) ? (string) $mediaId : null;
        return $mediaId;
    }

    /**
     * @return CM_Model_Stream_Publish
     * @throws CM_Exception_Invalid
     */
    public function getStreamPublish() {
        if (!$this->hasStreamPublish()) {
            throw new CM_Exception_Invalid('StreamChannel `' . $this->getId() . '` has no StreamPublish.');
        }
        return $this->getStreamPublishs()->getItem(0);
    }

    /**
     * @return boolean
     */
    public function hasStreamPublish() {
        return (boolean) $this->getStreamPublishs()->getCount();
    }

    /**
     * @return int
     */
    public function getServerId() {
        return (int) $this->_get('serverId');
    }

    public function toArray() {
        $array = parent::toArray();
        if ($this->hasStreamPublish()) {
            $array['user'] = $this->getStreamPublish()->getUser();
        }
        return $array;
    }

    /**
     * @return int
     */
    public function getThumbnailCount() {
        $params = $this->_getDataColumn();
        return $params->getInt('thumbnailCount', 0);
    }

    /**
     * @param int $index
     * @return CM_File_UserContent
     */
    public function getThumbnail($index) {
        $index = (int) $index;
        $filename = $this->getId() . '-' . $this->getHash() . '-thumbs' . DIRECTORY_SEPARATOR . $index . '.png';
        return new CM_File_UserContent('streamChannels', $filename, $this->getId());
    }

    /**
     * @return CM_Paging_FileUserContent_StreamChannelMediaThumbnails
     */
    public function getThumbnails() {
        return new CM_Paging_FileUserContent_StreamChannelMediaThumbnails($this);
    }

    /**
     * @return CM_Params
     */
    protected function _getDataColumn() {
        if (!$this->_has('data')) {
            return CM_Params::factory();
        } else {
            return CM_Params::factory(CM_Params::jsonDecode($this->_get('data')));
        }
    }

    protected function _onDeleteBefore() {
        parent::_onDeleteBefore();
        if (!CM_Model_StreamChannelArchive_Media::findById($this->getId())) {
            CM_Model_StreamChannelArchive_Media::createStatic(array('streamChannel' => $this));
        }
    }

    protected function _onDelete() {
        CM_Db_Db::delete('cm_streamChannel_media', array('id' => $this->getId()));
        parent::_onDelete();
    }

    protected function _loadData() {
        return CM_Db_Db::exec("SELECT * FROM `cm_streamChannel` JOIN `cm_streamChannel_media` USING (`id`)
								WHERE `id` = ?", array($this->getId()))->fetch();
    }

    protected static function _createStatic(array $data) {
        $key = (string) $data['key'];
        $serverId = $data['serverId'];
        $thumbnailCount = (int) $data['thumbnailCount'];
        $adapterType = (int) $data['adapterType'];
        $mediaId = !empty($data['mediaId']) ? (string) $data['mediaId'] : null;
        $id = CM_Db_Db::insert('cm_streamChannel', [
            'key'         => $key,
            'createStamp' => time(),
            'type'        => static::getTypeStatic(),
            'adapterType' => $adapterType,
        ], null, ['id' => ['literal' => 'LAST_INSERT_ID(id)']]);

        CM_Db_Db::insert('cm_streamChannel_media', [
            'id'       => $id,
            'serverId' => $serverId,
            'data'     => CM_Params::encode(['thumbnailCount' => $thumbnailCount], true),
            'mediaId'  => $mediaId,
        ], null, ['id' => ['literal' => 'LAST_INSERT_ID(id)']]);

        $cacheKey = CM_CacheConst::StreamChannel_Id . '_key' . $key . '_adapterType:' . $adapterType;
        CM_Cache_Shared::getInstance()->delete($cacheKey);
        return new static($id);
    }

    /**
     * @param string $mediaId
     * @return CM_Model_StreamChannel_Media|null
     */
    public static function findByMediaId($mediaId) {
        $row = CM_Db_Db::exec("SELECT t1.id, t2.type FROM cm_streamChannel_media t1 JOIN cm_streamChannel t2 USING(id) WHERE t1.mediaId = ?", [(string) $mediaId])->fetch();
        if (!$row) {
            return null;
        }
        $streamChannelId = $row['id'];
        $streamChannelType = $row['type'];
        return CM_Model_StreamChannel_Media::factory($streamChannelId, $streamChannelType);
    }
}
