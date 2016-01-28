<?php

if (CM_Db_Db::existsColumn('cm_streamChannelArchive_media', 'userId')) {
    CM_Db_Db::exec("ALTER TABLE cm_streamChannelArchive_media DROP userId");
}
