<?php

// 1) Duplicate entry for the city "Saint John's"

// Setting the region code to "Newfoundland and Labrador" for one of them
// to achieve consistency with the updated GeoIP data

CM_Db_Db::update('cm_locationCity', array('stateId' => 2524), array('id' => 22256));

// 2) Duplicate entries for the cities "Apo"/"Fpo" (U.S. Army post offices)

// Adding region entries for the U.S. Armed Forces Americas & Pacific

$idUS = CM_Db_Db::select('cm_locationCountry', 'id', array('abbreviation' => 'US'))->fetchColumn();

if (false === $idUS) {
	throw new CM_Exception_Invalid('No country with abbreviation `US` found');
}

$idUS = (int) $idUS;

$armedForcesRegionList = array(
	'AA' => 'Armed Forces Americas',
	'AE' => 'Armed Forces Europe, Middle East, & Canada',
	'AP' => 'Armed Forces Pacific',
);

$idArmedForcesList = array();

foreach ($armedForcesRegionList as $regionCode => $regionName) {
	$idArmedForces = CM_Db_Db::select('cm_locationState', 'id',
		array('countryId' => $idUS, 'abbreviation' => $regionCode))->fetchColumn();

	if (false === $idArmedForces) {
		$idArmedForces = CM_Db_Db::insert('cm_locationState',
			array('countryId' => $idUS, 'abbreviation' => $regionCode, 'name' => $regionName)
		);
	} else {
		CM_Db_Db::update('cm_locationState',
			array('name' => $regionName),
			array('countryId' => $idUS, 'abbreviation' => $regionCode)
		);
	}
	$idArmedForcesList[$regionCode] = (int) $idArmedForces;
}

// Moving the duplicate cities to the correct regions

$armedForcesCityList = array(
	'AA' => array(173158, 173159),
	'AE' => array(173160, 173161),
	'AP' => array(173944, 173945),
);

foreach ($armedForcesCityList as $regionCode => $cityIdList) {
	$regionId = $idArmedForcesList[$regionCode];
	foreach ($cityIdList as $cityId) {
		CM_Db_Db::update('cm_locationCity', array('stateId' => $regionId), array('id' => $cityId));
	}
}
