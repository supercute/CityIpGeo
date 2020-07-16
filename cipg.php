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
     * Загружаем данные с ipgeobase.ru и конвертируем в бинарный файл
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
if (file_exists($path)) {
    $ip = new RemoteAddress();
    try {
        $ipGeoBase = new IpGeoBase($path);
    } catch (Exception $e) {
        echo "Ошибка создания обьекта класса ipGeoBase";
    }

    $info =  $ipGeoBase->search('94.181.214.151');
    $locations = $ipGeoBase->listLocations();
    var_dump($locations);

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
} else {
    echo "Не найдена база городов, загрузите /cipg/cipg.php?upload_db=y";
}