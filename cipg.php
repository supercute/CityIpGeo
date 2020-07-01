<?php

use CIPG\IpGeoBase\IpGeoBase\IpGeoBase;
use CIPG\IpGeoBase\Utils\IpGeoBaseUtil\IpGeoBaseUtil;
use CIPG\IpGeoBase\Utils\RemoteAddress\RemoteAddress;

require_once 'IpGeoBase/Utils/RemoteAddress.php';
require_once 'IpGeoBase/IpGeoBase.php';
require_once 'IpGeoBase/Utils/IpGeoBaseUtil.php';

$path = __DIR__ . '/IpGeoBase/DB';

if (isset($_GET['upload_db']) && $_GET['upload_db'] == 'y') {
    /**
     * Загружаем данные с ipgeobase.ru и конвертируем в бинарный файл
     * Данные обновляеются ежедневно, имеет смысл поставить задачу на крон
     */
    $util = new IpGeoBaseUtil();
    try {
        $util->loadArchive($path);
    } catch (Exception $e){
        echo "Ошибка скачивания";
    }

    try {
        $util->convertInBinary($path);
    } catch (Exception $e) {
        echo "Ошибка конвертации";
    }
    echo "База городов успешно загружена";
}

$ip = new RemoteAddress();

try {
    $ipGeoBase = new IpGeoBase($path);
} catch (Exception $e) {
    echo "Ошибка создания обьекта класса ipGeoBase";
}
$info =  $ipGeoBase->search('94.181.214.151');
$locations = $ipGeoBase->listLocations();
//var_dump($locations);

if (isset($_POST['query'])) {
    $query = $_POST['query'];
    foreach ($locations as $location) {
        if (mb_stripos($location['city'], $query,0,'UTF-8') !== false) {
            $searchLocations[] = $location;
        }
    }
    header('Content-type: application/json');
    if (!empty($searchLocations)) {
        echo json_encode($searchLocations);
    } else {
        echo 'Не найдено результатов';
    }
} else if (isset($_POST['selected_city'])) {
    setcookie("CIPG_CITY", $_POST['selected_city']);
    return true;
} else {
    if (!isset($_COOKIE['CIPG_CITY'])) {
        setcookie("CIPG_CITY", $info['city']);
    }
    echo $_COOKIE['CIPG_CITY'];
}


//Выводит список всех городов
//$cities = $ipGeoBase->listCity();
//
//print_r($cities);