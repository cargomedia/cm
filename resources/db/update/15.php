<?php

if (CM_Db_Db::existsColumn('cm_stream_publish', 'userId')) {
	CM_Db_Db::exec('ALTER TABLE  `cm_stream_publish` MODIFY  `userId` int(10) unsigned DEFAULT NULL');
}
