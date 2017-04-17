<?php

class CM_Model_StreamChannel_Media extends CM_Model_StreamChannel_Abstract {

    public function onPublish(CM_Model_Stream_Publish $streamPublish) {
    }

    public function onSubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    public function onUnpublish(CM_Model_Stream_Publish $streamPublish) {
    }

    public function onUnsubscribe(CM_Model_Stream_Subscribe $streamSubscribe) {
    }

    /**
     * @return string
     */
    public function getHash() {
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
     * @return CM_Model_Stream_Publish|null
     */
    public function findStreamPublish() {
        return $this->getStreamPublishs()->getItem(0);
    }

    /**
     * @return CM_Model_Stream_Publish
     * @throws CM_Exception_Invalid
     */
    public function getStreamPublish() {
        $publish = $this->findStreamPublish();
        if (null === $publish) {
            throw new CM_Exception_Invalid('StreamChannel has no StreamPublish.', null, ['streamChannelId' => $this->getId()]);
        }
        return $publish;
    }

    /**
     * @return bool
     */
    public function hasStreamPublish() {
        return null !== $this->findStreamPublish();
    }

    /**
     * @return int
     */
    public function getServerId() {
        return (int) $this->_get('serverId');
    }

    public function jsonSerialize() {
        $array = parent::jsonSerialize();
        if ($publish = $this->findStreamPublish()) {
            $array['user'] = $publish->getUser();
        }
        return $array;
    }

    /**
     * @return CM_StreamChannel_ThumbnailList_Channel
     */
    public function getThumbnails() {
        return new CM_StreamChannel_ThumbnailList_Channel($this->getId());
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
        $adapterType = (int) $data['adapterType'];
        $mediaId = !empty($data['mediaId']) ? (string) $data['mediaId'] : null;

        $affectedRows = CM_Db_Db::exec("INSERT INTO cm_streamChannel(`key`, `createStamp`, `type`, `adapterType`)
	        SELECT ?, ?, ?, ?  FROM DUAL
	          WHERE NOT EXISTS
		        (SELECT 1 FROM cm_streamChannelArchive_media
                  WHERE mediaId = ?)
          ON DUPLICATE KEY UPDATE `id` = LAST_INSERT_ID(id), `createStamp` = \"createStamp\"+1 ",
            [$key, time(), static::getTypeStatic(), $adapterType, $mediaId])->getAffectedRows();

        if (0 === $affectedRows) {
            throw new CM_Exception_Invalid('Channel archive with given mediaId already exists');
        }

        $id = CM_Db_Db::exec("SELECT LAST_INSERT_ID()")->fetchColumn();
        $id = (int) $id;

        CM_Db_Db::insert('cm_streamChannel_media', [
            'id'       => $id,
            'serverId' => $serverId,
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
