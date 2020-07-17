# cityipgeo
Определение города, форма выбора и поиска города.

## Установка
Скачайте последний [релиз](https://github.com/supercute/cityipgeo/releases/)  или актуальную ветку [master](https://github.com/supercute/cityipgeo/archive/master.zip)

Установите файлы cipg.css и cipg.js из папки assets
```html
<head>
    <link rel="stylesheet" href="/cityipgeo/assets/css/cipg.css" />
</head>

<body>
    <script src="/cityipgeo/assets/js/cipg.js"></script>
</body>
```
Добавьте в любое нужное место ссылку:

```html
<a href="#" id="cipg-city"></a>
```

Загрузите базу городов выполнив файл uploadDB.php с параметром 'Y' 

**сайт/cityipgeo/uploadDB.php?Y**

## Настройка

В файле **default_cities.json** хранятся избранные города

```json
{
  "2097" : "Москва",
  "2287" : "Санкт-Петербург",
  "1283" : "Казань",
  "2732" : "Екатеринбург",
  "1427" : "Краснодар"
}
```

ID городов можно взять из файла **DB/cities.txt**
