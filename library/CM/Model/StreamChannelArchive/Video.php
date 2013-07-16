<?php

class CM_Model_StreamChannelArchive_Video extends CM_Model_StreamChannelArchive_Abstract {

	const TYPE = 25;

	/**
	 * @return int
	 */
	public function getCreated() {
		return (int) $this->_get('createStamp');
	}

	/**
	 * @return int
	 */
	public function getDuration() {
		return (int) $this->_get('duration');
	}

	/**
	 * @return CM_File_UserContent
	 */
	public function getVideo() {
		$filename = $this->getId() . '-' . $this->getHash() . '-original.mp4';
		return new CM_File_UserContent('streamChannels', $filename, $this->getId());
	}

	/**
	 * @return string
	 */
	public function getHash() {
		return (string) $this->_get('hash');
	}

	/**
	 * @return int
	 */
	public function getHeight() {
		return (int) $this->_get('height');
	}

	/**
	 * @return int
	 */
	public function getStreamChannelType() {
		return (int) $this->_get('streamChannelType');
	}

	/**
	 * @return int
	 */
	public function getThumbnailCount() {
		return (int) $this->_get('thumbnailCount');
	}

	/**
	 * @return CM_Paging_FileUserContent_StreamChannelArchiveVideoThumbnails
	 */
	public function getThumbnails() {
		return new CM_Paging_FileUserContent_StreamChannelArchiveVideoThumbnails($this);
	}

	/**
	 * @return CM_Model_User|null
	 */
	public function getUser() {
		try {
			return CM_Model_User::factory($this->getUserId());
		} catch (CM_Exception_Nonexistent $ex) {
			return null;
		}
	}

	/**
	 * @return int
	 */
	public function getUserId() {
		return (int) $this->_get('userId');
	}

	/**
	 * @return int
	 */
	public function getWidth() {
		return (int) $this->_get('width');
	}

	/**
	 * @return array
	 */
	protected function _loadData() {
		return CM_Db_Db::select('cm_streamChannelArchive_video', '*', array('id' => $this->getId()))->fetch();
	}

	protected function _onDelete() {
		$this->getVideo()->delete();
		$thumbDir = new CM_File_UserContent('streamChannels', $this->getId() . '-' . $this->getHash() . '-thumbs/', $this->getId());
		$thumbDir->delete();
		CM_Db_Db::delete('cm_streamChannelArchive_video', array('id' => $this->getId()));
	}

	protected static function _create(array $data) {
		/** @var CM_Model_StreamChannel_Video $streamChannel */
		$streamChannel = $data['streamChannel'];
		$streamPublish = $streamChannel->getStreamPublish();
		$createStamp = $streamPublish->getStart();
		$thumbnailCount = $streamChannel->getThumbnailCount();
		$end = time();
		$duration = $end - $createStamp;
		CM_Db_Db::insert('cm_streamChannelArchive_video', array('id' => $streamChannel->getId(), 'userId' => $streamPublish->getUser()->getId(), 'width' => $streamChannel->getWidth(), 'height' => $streamChannel->getHeight(),
			'duration' => $duration, 'thumbnailCount' => $thumbnailCount, 'hash' => $streamChannel->getHash(), 'streamChannelType' => $streamChannel->getType(), 'createStamp' => $createStamp));
		return new self($streamChannel->getId());
	}

	/**
	 * @param int $age
	 * @param int $streamChannelType
	 */
	public static function deleteOlder($age, $streamChannelType) {
		$age = (int) $age;
		$streamChannelType = (int) $streamChannelType;
		$ageMax = time() - $age - 1;
		$streamChannelArchives = new CM_Paging_StreamChannelArchiveVideo_Type($streamChannelType, $ageMax);
		/** @var CM_Model_StreamChannelArchive_Video $streamChannelArchive */
		foreach ($streamChannelArchives as $streamChannelArchive) {
			$streamChannelArchive->delete();
		}
	}
}
