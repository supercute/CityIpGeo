<?php
use CIPG\Utils\IpGeoBaseUtils;
require_once 'Core/Utils/IpGeoBaseUtils.php';

define("PATH", __DIR__ . '/DB');

if (isset($_GET['Y'])) {
    /**
     * Загружает данные с ipgeobase.ru и конвертирует в бинарный файл
     * Данные обновляеются ежедневно, имеет смысл поставить задачу на крон
     */
    $util = new IpGeoBaseUtils();
    try {
        $util->loadArchive(PATH);
        $util->convertInBinary(PATH);
        echo "База городов успешно загружена";

    } catch (Exception $e){
        echo "Ошибка скачивания";
    }
}