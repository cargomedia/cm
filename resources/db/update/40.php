<?php

echo 'Please run CM script 40 online' . PHP_EOL;
return;
$duplicates = CM_Db_Db::exec("SELECT * FROM (SELECT name, count(*) AS count FROM cm_model_languagekey GROUP BY name) AS t1 WHERE count > 1 ORDER BY count DESC;");
while ($row = $duplicates->fetch()) {
    $name = $row['name'];
    CM_Db_Db::exec("DELETE cm_model_languagekey, cm_languageValue FROM cm_model_languagekey LEFT JOIN cm_languageValue ON (id = languageKeyId)
                    WHERE name = ? AND id > (SELECT minId FROM (SELECT min(id) AS minId FROM cm_model_languagekey WHERE name = ?) as t1)", [$name, $name]);
}
