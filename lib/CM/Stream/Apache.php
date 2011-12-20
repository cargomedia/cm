<?php

class CM_Stream_Apache extends CM_Stream_Abstract {

	public function publish($channel, $data) {
		CM_Mysql::insert(TBL_DEV_STREAM, array('channel' => $channel, 'data' => json_encode($data)));
	}

	/**
	 * processes an imitated longpolling request and returns the oldest not yet received and still valid data in the table for the channel with the id $channel
	 *
	 * @param string   $channel
	 * @param int|null $idMin
	 * @return array|null Data array (id, data)
	 */
	public function subscribe($channel, $idMin = null) {
		if (!Config::get()->stream->enabled) {
			return null;
		}
		CM_Mysql::delete(TBL_DEV_STREAM, 'createStamp < (NOW()-10)');

		$idMin = (int) $idMin;

		$result = CM_Mysql::exec("SELECT * FROM TBL_DEV_STREAM WHERE channel = '?' AND `id` > ? ORDER BY createStamp, id ASC LIMIT 1", $channel, $idMin);

		$dataArray = $result->fetchAssoc();

		if (!$dataArray) {
			return null;
		}

		return array('id' => $dataArray['id'], 'data' => $dataArray['data'],);
	}
}
