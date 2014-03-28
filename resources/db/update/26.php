<?php

$emoticonList  = new CM_Paging_Emoticon_All();
foreach (CM_Db_Db::select('cm_emoticon', array('code', 'codeAdditional'), 'codeAdditional IS NOT NULL')->fetchAll() as $emoticon) {
    $aliases = explode(',', $emoticon['codeAdditional']);
    $emoticonList->setAliases($emoticon['code'], $aliases);
}
