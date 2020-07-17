<?php

use CIPG\Core\IpGeoBase;
use CIPG\Utils\RemoteAddress;

require_once 'Core/Utils/RemoteAddress.php';
require_once 'Core/IpGeoBase.php';
require_once 'Core/Utils/IpGeoBaseUtils.php';
require_once 'uploadDB.php';

/**
 * Осуществляет поиск города в массиве городов без учета регистра
 * @param array $locations
 */
function querySearch(array $locations)
{
    $query = $_POST['query'];
    foreach ($locations as $location) {
        if (mb_stripos($location['city'], $query, 0, 'UTF-8') !== false) {
            $searchLocations[] = $location;
        }
    }
    header('Content-type: application/json');
    if (!empty($searchLocations)) {
        echo json_encode($searchLocations);
    } else {
        echo 'Не найдено результатов';
    }
}

/**
 * Устанавливает выбранный город
 * @param array $locations
 * @return bool
 */
function setSelectedCity(array $locations)
{
    foreach ($locations as $location) {
        if (intval($_POST['selected_city']) === intval($location['cityId'])) {
            setcookie("CIPG_CITY", $location['city'], time()+3600);
            return true;
        }
    }
    return false;
}

if (file_exists(PATH)) {
    try {
        $ipGeoBase = new IpGeoBase(PATH);
    } catch (Exception $e) {
        echo "Ошибка создания обьекта класса ipGeoBase";
    }

    $ip = new RemoteAddress();
    $ip->getIpAddress();
    $info =  $ipGeoBase->search('77.88.55.80'); //Яндекс ip (Москва)

    if (!isset($_COOKIE['CIPG_CITY'])) {
        $_COOKIE['CIPG_CITY'] = $info['city'];
    }

    $locations = $ipGeoBase->listLocations();

    if (isset($_POST['query'])) {
        querySearch($locations);
    } elseif (isset($_POST['selected_city'])) {
        setSelectedCity($locations);
    } else {
        echo $_COOKIE['CIPG_CITY'];
    }
} else {
    echo "Не найдена база городов";
}
