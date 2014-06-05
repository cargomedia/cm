<?php

foreach (
    array(
        'cm_locationZip'       => 'cm_model_location_zip',
        'cm_locationCity'      => 'cm_model_location_city',
        'cm_locationState'     => 'cm_model_location_state',
        'cm_locationCountry'   => 'cm_model_location_country',
        'cm_locationCityIp'    => 'cm_model_location_city_ip',
        'cm_locationCountryIp' => 'cm_model_location_country_ip',
    ) as $tableOld => $tableNew) {
    if (CM_Db_Db::existsTable($tableOld)) {
        CM_Db_Db::exec('RENAME TABLE `' . $tableOld . '` TO `' . $tableNew . '`');
    }
}
