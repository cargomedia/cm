<?php

$isInnoDb = function ($tableName) {
	$result = CM_Db_Db::exec("SHOW TABLE STATUS WHERE name = '" . $tableName . "'")->fetch();
	return $result['Engine'] === 'InnoDB';
};

if (!$isInnoDb('cm_streamChannel')) {
	CM_Db_Db::exec("ALTER TABLE  `cm_streamChannel` ENGINE = INNODB");
}

if (!$isInnoDb('cm_stream_publish')) {
	CM_Db_Db::exec("TRUNCATE TABLE  `cm_stream_publish`");
	CM_Db_Db::exec("ALTER TABLE  `cm_stream_publish` ENGINE = INNODB");
	CM_Db_Db::exec("ALTER TABLE  `cm_stream_publish` ADD FOREIGN KEY (  `channelId` ) REFERENCES  `skadate`.`cm_streamChannel` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;");
}
if (!$isInnoDb('cm_stream_subscribe')) {
	CM_Db_Db::exec("TRUNCATE TABLE  `cm_stream_subscribe`");
	CM_Db_Db::exec("ALTER TABLE  `cm_stream_subscribe` ENGINE = INNODB");
	CM_Db_Db::exec("ALTER TABLE  `cm_stream_subscribe` ADD FOREIGN KEY (  `channelId` ) REFERENCES  `skadate`.`cm_streamChannel` (`id`) ON DELETE RESTRICT ON UPDATE RESTRICT ;");
}

