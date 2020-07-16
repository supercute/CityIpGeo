<?php

use CIPG\Core\IpGeoBase;
use CIPG\Utils\IpGeoBaseUtils;
use CIPG\Utils\RemoteAddress;

require_once 'Core/Utils/RemoteAddress.php';
require_once 'Core/IpGeoBase.php';
require_once 'Core/Utils/IpGeoBaseUtils.php';

$path = __DIR__ . '/DB';

if (isset($_GET['upload_db']) && $_GET['upload_db'] == 'y') {
    /**
     * Загружает данные с ipgeobase.ru и конвертирует в бинарный файл
     * Данные обновляеются ежедневно, имеет смысл поставить задачу на крон
     */
    $util = new IpGeoBaseUtils();
    try {
        $util->loadArchive($path);
        $util->convertInBinary($path);
        echo "База городов успешно загружена";

    } catch (Exception $e){
        echo "Ошибка скачивания";
    }
}

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
            setcookie("CIPG_CITY", $location['city']);
            return true;
        }
    }
    return false;
}

if (file_exists($path)) {
    try {
        $ipGeoBase = new IpGeoBase($path);
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
    } else if (isset($_POST['selected_city'])) {
        setSelectedCity($locations);
    } else {
        echo $_COOKIE['CIPG_CITY'];
    }

} else {
    echo "Не найдена база городов, загрузите /cipg/cipg.php?upload_db=y";
}