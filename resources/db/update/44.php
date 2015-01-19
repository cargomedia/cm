<?php

if (CM_Db_Db::existsTable('cm_emoticon')) {
    CM_Db_Db::exec("DROP TABLE cm_emoticon");
}
