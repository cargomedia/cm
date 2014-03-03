<?php

if (!CM_Db_Db::describeColumn('cm_streamChannelArchive_video', 'userId')->getAllowNull()) {
  CM_Db_Db::exec('ALTER TABLE  `cm_streamChannelArchive_video` CHANGE  `userId`  `userId` INT( 10 ) UNSIGNED NULL ;');
}
