<?php

if (!CM_Db_Db::existsColumn('cm_model_location_state', 'lat')) {
    CM_Db_Db::exec('ALTER TABLE `cm_model_location_state` ADD COLUMN lat float DEFAULT NULL AFTER `name`');
}

if (!CM_Db_Db::existsColumn('cm_model_location_state', 'lon')) {
    CM_Db_Db::exec('ALTER TABLE `cm_model_location_state` ADD COLUMN lon float DEFAULT NULL AFTER `lat`');
}

if (!CM_Db_Db::existsColumn('cm_model_location_country', 'lat')) {
    CM_Db_Db::exec('ALTER TABLE `cm_model_location_country` ADD COLUMN lat float DEFAULT NULL AFTER `name`');
}

if (!CM_Db_Db::existsColumn('cm_model_location_country', 'lon')) {
    CM_Db_Db::exec('ALTER TABLE `cm_model_location_country` ADD COLUMN lon float DEFAULT NULL AFTER `lat`');
}

CM_Cache_Shared::getInstance()->flush();

return;

// Initialize

$geoIpFile = new CM_File('GeoLiteCity.zip', CM_Service_Manager::getInstance()->getFilesystems()->getTmp());
if ($geoIpFile->exists()) {
    $geoIpFile->delete();
}
$geoIpFilePath = $geoIpFile->getPathOnLocalFilesystem();
(new \GuzzleHttp\Client())->get('http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/GeoLiteCity-latest.zip', [
    'timeout' => 600,
    'save_to' => $geoIpFilePath,
]);
$zip = zip_open($geoIpFilePath);
while ($entry = zip_read($zip)) {
    if (preg_match('#Location\\.csv\\z#', zip_entry_name($entry))) {
        break;
    }
}
zip_entry_open($zip, $entry, 'r');
$stream = fopen('php://temp', 'r+');
$count = 0;
while ('' !== ($buffer = zip_entry_read($entry))) {
    $buffer = iconv('ISO-8859-1', 'UTF-8', $buffer);
    fwrite($stream, $buffer);
    $count += substr_count($buffer, "\n");
}
zip_close($zip);
rewind($stream);

$item = 0;
while (false !== ($row = fgetcsv($stream))) {
    if ($item >= 2 && count($row) >= 7) { // Skip copyright, column names and empty lines
        list($maxMind, $countryCode, $regionCode, $cityName, $zipCode, $latitude, $longitude) = $row;
        $maxMind = (int) $maxMind;
        $latitude = (float) $latitude;
        $longitude = (float) $longitude;
        if (strlen($zipCode)) { // ZIP code record
        } elseif (strlen($cityName)) { // City record
        } elseif (strlen($regionCode)) { // Region record
            $maxMindRegion = $countryCode . $regionCode;
            if (!CM_Db_Db::update('cm_model_location_state', [
                'lat' => $latitude,
                'lon' => $longitude,
            ], ['_maxmind' => $maxMindRegion])
            ) {
                // For the USA, where the old numeric region codes in _maxmind have been removed from MaxMind's newer region databases
                $countryId = (int) CM_Db_Db::select('cm_model_location_country', 'id', ['abbreviation' => $countryCode])->fetchColumn();
                CM_Db_Db::update('cm_model_location_state', [
                    'lat' => $latitude,
                    'lon' => $longitude,
                ], [
                    'countryId'    => $countryId,
                    'abbreviation' => $regionCode
                ]);
            }
        } elseif (strlen($countryCode)) { // Country record
            CM_Db_Db::update('cm_model_location_country', [
                'lat' => $latitude,
                'lon' => $longitude,
            ], ['abbreviation' => $countryCode]);
        }
    }
    print "\r" . ++$item . '/' . $count;
}
print "\n";

CM_Cache_Shared::getInstance()->flush();
