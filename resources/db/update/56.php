<?php

return;

$rows = CM_Db_Db::execRead('SELECT `id`, `thumbnailCount` FROM cm_streamChannel_media WHERE thumbnailCount > 0');

while ($row = $rows->fetch()) {
    CM_Db_Db::exec('UPDATE cm_streamChannel_media SET `data`=? WHERE `id`=? ', [
        CM_Params::encode(['thumbnailCount' => (int) $row['thumbnailCount']], true),
        $row['id'],
    ]);
}

$rows = CM_Db_Db::execRead('SELECT `id`, `thumbnailCount` FROM cm_streamChannelArchive_media WHERE thumbnailCount > 0');

while ($row = $rows->fetch()) {
    CM_Db_Db::exec('UPDATE cm_streamChannelArchive_media SET `data`=? WHERE `id`=? ', [
        CM_Params::encode(['thumbnailCount' => (int) $row['thumbnailCount']], true),
        $row['id'],
    ]);
}
