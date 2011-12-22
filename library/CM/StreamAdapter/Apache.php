<?php

class CM_StreamAdapter_Apache extends CM_StreamAdapter_Abstract {

	public function publish($channel, $data) {
		CM_Mysql::insert(TBL_DEV_STREAM, array('channel' => $channel, 'data' => json_encode($data)));
	}

	public function subscribe($channel, $idMin = null) {
		$idMin = (int) $idMin;

		CM_Mysql::delete(TBL_DEV_STREAM, 'createStamp < (NOW()-10)');

		$result = CM_Mysql::exec("SELECT id,data FROM TBL_DEV_STREAM WHERE channel = '?' AND `id` > ? ORDER BY createStamp, id ASC LIMIT 1", $channel, $idMin);
		$data = $result->fetchAssoc();

		return $data;
	}
}
