<?php
require('phpQuery/phpQuery.php');

//Домент откуда будем получать товары
const DOMEN = "https://tachka.ru";
const DOMEN_CAT = "https://tachka.ru/farkop?";




//бренды которые будем парсить, были указанны в задачи
$parsLink = array("auto-hak", "aragon", "avtos", "bizon-dzk", "brink", "westfalia", "baltex", "treyler");
$dataPath = dirname(__FILE__) . '/data/';
if (!file_exists($dataPath)) {
    mkdir($dataPath, 0777, true);
}

 //Получаем файл
//Если не номера страницы, то думаем что это первая страница и не передаем в CURL запрос номер пагинации, т.к срабывает массовый редирект
//Если мы впервый раз на страницы, то создаем файл в ктором будем хранить ссылки на товары
//Если мы не первый раз на страницы, то открываем для чтения уже существующий файл




//Отправляем CURL запрос и начинаем парсить все страницы.
// ШАГ 1. Получаем все ссылки детальных страниц.
foreach ($parsLink as $brand) {
    $params = array(
        'brand'  => $brand
    );
    $fileName = $dataPath. $brand . '.json';
    getData($params, $fileName, $brand);
}




function getData($link, $filename, $brand, $count = 0)
{
    $ch = curl_init(DOMEN_CAT . http_build_query($link));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $doc = phpQuery::newDocument($response);
    //Выбираем только товары
    $products = $doc->find('.catalog-list .catalog-item');
    //Перебераем товары
    foreach ($products as $product) {
        $pq = pq($product);

        $data[$pq->attr('id')]['URL'] = $pq->find('.catalog-item__head a')->attr('href');
        $data[$pq->attr('id')]['ARTICUL'] = $pq->find('.catalog-item__attributes.attributes .attributes__row:eq(0) .attributes__data')->text();
    }


    file_put_contents($filename, json_encode($data), FILE_APPEND);

    $toPage = $doc->find('.paging .paging__page.paging__page_active')->text();
    $countPage = explode(" ", $doc->find('.top_sort__pages')->text())[2];

    while ($count != $countPage) {
        $params = [
            'brand'  => $brand,
            'page' => $toPage + 1
        ];
        getData($params, $filename, $brand, $toPage + 1);
        return;
    }
}
